<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\FindingSource;
use App\Models\IssueDocumentation;
use App\Models\IssueFollowUp;
use App\Models\IssueFollowUpLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class IssueFollowUpService
{
    /**
     * Terapkan filter pencarian dan parameter ke query IssueFollowUp.
     */
    public function buildFilteredQuery(Request $request): Builder
    {
        $query = IssueFollowUp::query()->with(['logs', 'documentations']);

        // Filter: Rentang Tanggal (reported_date)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('reported_date', [
                $request->input('start_date'),
                $request->input('end_date'),
            ]);
        } elseif ($request->filled('start_date')) {
            $query->whereDate('reported_date', '>=', $request->input('start_date'));
        } elseif ($request->filled('end_date')) {
            $query->whereDate('reported_date', '<=', $request->input('end_date'));
        }

        // Filter: Fungsi Safeguard (Health & Safety, Security, Environment, Social) - mendukung multiple array
        $functionParam = $request->input('hsse_functions', $request->input('hsse_function', $request->input('function', $request->input('hsse_division', $request->input('division')))));
        if (!empty($functionParam)) {
            if (is_array($functionParam)) {
                $funcs = array_filter($functionParam, fn($f) => !empty($f) && strtoupper(trim($f)) !== 'ALL' && strtoupper(trim($f)) !== 'SEMUA');
                if (!empty($funcs)) {
                    $query->where(function (Builder $q) use ($funcs) {
                        foreach ($funcs as $f) {
                            $q->orWhere(function (Builder $sub) use ($f) {
                                $sub->byDivision($f);
                            });
                        }
                    });
                }
            } elseif (strtoupper((string) $functionParam) !== 'ALL' && strtoupper((string) $functionParam) !== 'SEMUA') {
                $query->byDivision((string) $functionParam);
            }
        }

        // Filter: Status (All, Open, Close / Closed, atau Overdue)
        if ($request->filled('status')) {
            $statusFilter = trim((string) $request->input('status'));
            if (strcasecmp($statusFilter, 'All') !== 0 && strcasecmp($statusFilter, 'Semua') !== 0 && $statusFilter !== '') {
                if (strcasecmp($statusFilter, 'Overdue') === 0) {
                    $query->overdue();
                } elseif (strcasecmp($statusFilter, 'Close') === 0 || strcasecmp($statusFilter, 'Closed') === 0) {
                    $query->whereIn('status', ['Close', 'Closed']);
                } elseif (strcasecmp($statusFilter, 'Open') === 0) {
                    $query->where('status', 'Open');
                } else {
                    $query->where('status', $statusFilter);
                }
            }
        }

        // Filter: Sumber Temuan (category / source)
        $sourceParam = $request->input('source', $request->input('category'));
        if (!empty($sourceParam) && strtoupper((string) $sourceParam) !== 'ALL' && strtoupper((string) $sourceParam) !== 'SEMUA') {
            $query->where('category', $sourceParam);
        }

        // Filter: Unit (opsional / kompatibilitas data lama)
        if ($request->filled('unit') && strtoupper((string) $request->input('unit')) !== 'ALL') {
            $query->where('unit', $request->input('unit'));
        }

        // Filter: PIC (opsional / kompatibilitas data lama)
        if ($request->filled('pic')) {
            $pic = trim((string) $request->input('pic'));
            $query->where('pic', 'like', "%{$pic}%");
        }

        // Filter: Prioritas (opsional / kompatibilitas data lama)
        if ($request->filled('priority') && strtoupper((string) $request->input('priority')) !== 'ALL') {
            $query->where('priority', $request->input('priority'));
        }

        // Pencarian berdasarkan judul, deskripsi, kode, sumber temuan, lokasi, catatan
        if ($request->filled('search')) {
            $term = trim((string) $request->input('search'));
            $query->where(function (Builder $q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
                  ->orWhere('issue_code', 'like', "%{$term}%")
                  ->orWhere('category', 'like', "%{$term}%")
                  ->orWhere('hsse_division', 'like', "%{$term}%")
                  ->orWhere('location', 'like', "%{$term}%")
                  ->orWhere('notes', 'like', "%{$term}%");
            });
        }

        // Pengurutan (default: terbaru berdasarkan reported_date DESC, id DESC)
        $sort = $request->input('sort', 'date-desc');
        if ($sort === 'date-asc') {
            $query->orderBy('reported_date', 'asc')->orderBy('id', 'asc');
        } elseif ($sort === 'due-asc') {
            $query->orderByRaw('due_date IS NULL, due_date ASC')->orderBy('reported_date', 'desc');
        } elseif ($sort === 'status') {
            $query->orderBy('status', 'asc')->orderBy('reported_date', 'desc')->orderBy('id', 'desc');
        } else {
            $query->orderBy('reported_date', 'desc')->orderBy('id', 'desc');
        }

        return $query;
    }

    /**
     * Hitung kartu ringkasan aktual dari database: Total, Open, Close, Overdue.
     */
    public function calculateStats(Collection $issues): array
    {
        $total = $issues->count();
        $open = $issues->where('status', 'Open')->count();
        $closed = $issues->filter(fn (IssueFollowUp $i) => in_array($i->status, ['Close', 'Closed'], true))->count();
        $overdue = $issues->filter(fn (IssueFollowUp $i) => $i->isOverdue())->count();

        return [
            'total_issues' => $total,
            'open_issues' => $open,
            'closed_issues' => $closed,
            'in_progress_issues' => 0, // Disederhanakan ke Open & Close
            'overdue_issues' => $overdue,
        ];
    }

    /**
     * Hitung ringkasan per 4 Fungsi HSSE: HSSE, Social, Security, Environment.
     */
    public function calculateDivisionSummary(?Collection $allIssues = null): array
    {
        $collection = $allIssues ?? IssueFollowUp::all();

        $buildFunctionStats = function (Collection $subset, string $code, string $label) {
            $open = $subset->where('status', 'Open')->count();
            $closed = $subset->filter(fn (IssueFollowUp $i) => in_array($i->status, ['Close', 'Closed'], true))->count();
            $total = $subset->count();
            $overdue = $subset->filter(fn (IssueFollowUp $i) => $i->isOverdue())->count();

            return [
                'code' => $code,
                'label' => $label,
                'total' => $total,
                'open' => $open,
                'closed' => $closed,
                'in_progress' => 0,
                'active' => $open,
                'overdue' => $overdue,
            ];
        };

        // 1. Health & Safety (H / HSSE)
        $hsseItems = $collection->filter(function (IssueFollowUp $i) {
            $raw = strtoupper(trim((string) $i->hsse_division));
            return in_array($raw, ['HEALTH & SAFETY', 'HSSE', 'H', 'HEALTH', 'HEALTH & SAVETY'], true) || str_contains($raw, 'HEALTH') || str_contains($raw, 'SAFETY');
        });

        // 2. Security (Security / Scurity)
        $securityItems = $collection->filter(function (IssueFollowUp $i) {
            $raw = strtoupper(trim((string) $i->hsse_division));
            return in_array($raw, ['SECURITY', 'SCURITY', 'S_SECURITY'], true);
        });

        // 3. Environment (E / Lingkungan)
        $envItems = $collection->filter(function (IssueFollowUp $i) {
            $raw = strtoupper(trim((string) $i->hsse_division));
            return in_array($raw, ['ENVIRONMENT', 'EMVIRONMENT', 'E'], true) || str_contains($raw, 'ENVIRONMENT');
        });

        // 4. Social (Social / Sosial)
        $socialItems = $collection->filter(function (IssueFollowUp $i) {
            $raw = strtoupper(trim((string) $i->hsse_division));
            return in_array($raw, ['SOCIAL', 'SOSIAL', 'S_SOCIAL', 'S'], true) || str_contains($raw, 'SOCIAL') || str_contains($raw, 'SOSIAL');
        });

        $unclassifiedItems = $collection->filter(function (IssueFollowUp $i) use ($hsseItems, $securityItems, $envItems, $socialItems) {
            return !$hsseItems->contains('id', $i->id) &&
                   !$securityItems->contains('id', $i->id) &&
                   !$envItems->contains('id', $i->id) &&
                   !$socialItems->contains('id', $i->id);
        });

        $hsseStats = $buildFunctionStats($hsseItems, 'Health & Safety', 'Health & Safety');
        $securityStats = $buildFunctionStats($securityItems, 'Security', 'Security');
        $envStats = $buildFunctionStats($envItems, 'Environment', 'Environment');
        $socialStats = $buildFunctionStats($socialItems, 'Social', 'Social');

        return [
            'all' => $buildFunctionStats($collection, 'ALL', 'Semua Fungsi'),
            'Health & Safety' => $hsseStats,
            'HSSE' => $hsseStats, // Kompatibilitas
            'H' => $hsseStats, // Kompatibilitas
            'Security' => $securityStats,
            'S_SECURITY' => $securityStats, // Kompatibilitas
            'Environment' => $envStats,
            'E' => $envStats, // Kompatibilitas
            'Social' => $socialStats,
            'S_SOCIAL' => $socialStats, // Kompatibilitas
            'S' => $socialStats, // Kompatibilitas
            'unclassified' => $buildFunctionStats($unclassifiedItems, 'UNCLASSIFIED', 'Belum Diklasifikasikan'),
        ];
    }

    /**
     * Bangun data grafik per fungsi (Health & Safety, Security, Environment, Social).
     * Setiap fungsi memiliki data: Open, Close, Total.
     */
    public function buildFunctionCharts(?Collection $allIssues = null): array
    {
        $summary = $this->calculateDivisionSummary($allIssues);

        return [
            'Health & Safety' => [
                'name' => 'Health & Safety',
                'short_code' => 'health_safety',
                'icon' => 'bi-heart-pulse-fill',
                'pill_class' => 'hsse',
                'badge_color' => 'bg-primary',
                'open' => $summary['Health & Safety']['open'],
                'close' => $summary['Health & Safety']['closed'],
                'total' => $summary['Health & Safety']['total'],
            ],
            'Security' => [
                'name' => 'Security',
                'short_code' => 'security',
                'icon' => 'bi-shield-lock-fill',
                'pill_class' => 'security',
                'badge_color' => 'bg-warning-subtle text-warning-emphasis border border-warning',
                'open' => $summary['Security']['open'],
                'close' => $summary['Security']['closed'],
                'total' => $summary['Security']['total'],
            ],
            'Environment' => [
                'name' => 'Environment',
                'short_code' => 'environment',
                'icon' => 'bi-tree-fill',
                'pill_class' => 'environment',
                'badge_color' => 'bg-success',
                'open' => $summary['Environment']['open'],
                'close' => $summary['Environment']['closed'],
                'total' => $summary['Environment']['total'],
            ],
            'Social' => [
                'name' => 'Social',
                'short_code' => 'social',
                'icon' => 'bi-people-fill',
                'pill_class' => 'social',
                'badge_color' => 'bg-danger',
                'open' => $summary['Social']['open'],
                'close' => $summary['Social']['closed'],
                'total' => $summary['Social']['total'],
            ],
        ];
    }

    /**
     * Dapatkan daftar sumber temuan aktif dari master database.
     */
    public function getActiveSources(): array
    {
        $dbSources = FindingSource::where('is_active', true)->orderBy('name')->pluck('name')->all();
        if (empty($dbSources)) {
            return IssueFollowUp::CATEGORIES;
        }

        return array_values(array_unique(array_merge(IssueFollowUp::CATEGORIES, $dbSources)));
    }

    /**
     * Tambahkan sumber temuan baru melalui antarmuka website.
     */
    public function addFindingSource(string $name, ?string $description = null): FindingSource
    {
        $trimmed = trim($name);
        return FindingSource::firstOrCreate(
            ['name' => $trimmed],
            ['description' => $description, 'is_active' => true]
        );
    }

    /**
     * Buat temuan baru beserta log awal dalam transaksi database.
     */
    public function createIssue(array $data, ?UploadedFile $attachment = null, ?Request $request = null): IssueFollowUp
    {
        $userName = Auth::user()?->name ?? 'Administrator';
        $userId = Auth::id();

        return DB::transaction(function () use ($data, $attachment, $request, $userName, $userId) {
            $status = (strcasecmp($data['status'] ?? 'Open', 'Close') === 0 || strcasecmp($data['status'] ?? 'Open', 'Closed') === 0)
                ? 'Close'
                : 'Open';

            // Aturan Tenggat: Jika status Open, tenggat tidak boleh diisi (null)
            $dueDate = ($status === 'Close') ? ($data['due_date'] ?? null) : null;

            // Sumber Temuan (category)
            $source = $data['category'] ?? ($data['source'] ?? 'Audit GIMS');

            // Pastikan sumber temuan ada di master jika baru
            if (!empty($source)) {
                $this->addFindingSource($source);
            }

            // Normalisasi Fungsi HSSE
            $func = $data['hsse_function'] ?? ($data['hsse_division'] ?? 'HSSE');
            $funcNormalized = match (strtoupper(trim((string) $func))) {
                'H', 'HSSE', 'HEALTH', 'HEALTH & SAFETY', 'HEALTH & SAVETY' => 'HSSE',
                'S', 'S_SOCIAL', 'SOSIAL', 'SOCIAL' => 'Social',
                'S_SECURITY', 'SCURITY', 'SECURITY' => 'Security',
                'E', 'ENVIRONMENT', 'EMVIRONMENT' => 'Environment',
                default => $func,
            };

            $issue = IssueFollowUp::create([
                'hsse_division' => $funcNormalized,
                'reported_date' => $data['reported_date'],
                'title' => $data['title'],
                'category' => $source,
                'unit' => $data['unit'] ?? null,
                'location' => $data['location'] ?? null,
                'description' => $data['description'],
                'action_plan' => $data['action_plan'] ?? null,
                'pic' => $data['pic'] ?? null,
                'due_date' => $dueDate,
                'priority' => $data['priority'] ?? null,
                'status' => $status,
                'closed_date' => ($status === 'Close') ? Carbon::today()->toDateString() : null,
                'closed_by' => ($status === 'Close') ? $userName : null,
                'notes' => $data['notes'] ?? null,
                'attachment_path' => null,
            ]);

            // Jika ada lampiran awal (multiple documentation_files, multiple attachments, atau single attachment)
            $uploadedList = [];
            if ($request && $request->hasFile('documentation_files')) {
                $uploadedList = array_merge($uploadedList, (array) $request->file('documentation_files'));
            }
            if ($request && $request->hasFile('attachments')) {
                $uploadedList = array_merge($uploadedList, (array) $request->file('attachments'));
            }

            if (!empty($uploadedList)) {
                foreach ($uploadedList as $file) {
                    if ($file instanceof UploadedFile && $file->isValid()) {
                        $this->addDocumentation($issue, $file, 'Dokumentasi Temuan', $request);
                    }
                }
            } elseif ($attachment && $attachment->isValid()) {
                $this->addDocumentation($issue, $attachment, 'Dokumentasi Awal Temuan', $request);
            }

            // Catat log
            IssueFollowUpLog::create([
                'issue_follow_up_id' => $issue->id,
                'user_id' => $userId,
                'user_name' => $userName,
                'action_type' => 'CREATED',
                'old_status' => null,
                'new_status' => $issue->status,
                'note' => "Temuan baru dicatat dengan status {$issue->status}.",
                'followed_up_at' => now(),
            ]);

            $this->recordActivityLog(
                action: 'TAMBAH',
                issue: $issue,
                description: "Menambahkan temuan HSSE baru [{$issue->issue_code}] Fungsi {$issue->function_label}: {$issue->title} (Status: {$issue->status})",
                changes: null,
                request: $request
            );

            return $issue->load(['logs', 'documentations']);
        });
    }

    /**
     * Perbarui data temuan di MySQL.
     */
    public function updateIssue(IssueFollowUp $issue, array $data, ?UploadedFile $attachment = null, ?Request $request = null): IssueFollowUp
    {
        $userName = Auth::user()?->name ?? 'Administrator';
        $userId = Auth::id();

        return DB::transaction(function () use ($issue, $data, $attachment, $request, $userName, $userId) {
            $oldStatus = $issue->status;
            $oldSnapshot = $issue->only([
                'hsse_division', 'reported_date', 'title', 'category', 'location',
                'description', 'due_date', 'status', 'notes'
            ]);

            $newStatus = (strcasecmp($data['status'] ?? $issue->status, 'Close') === 0 || strcasecmp($data['status'] ?? $issue->status, 'Closed') === 0)
                ? 'Close'
                : 'Open';

            // Aturan Tenggat: Jika status Open, tenggat tidak boleh diisi (null). Jika Close, dapat diisi/diedit.
            $dueDate = ($newStatus === 'Close') ? ($data['due_date'] ?? $issue->due_date) : null;

            $closedDate = $issue->closed_date;
            $closedBy = $issue->closed_by;
            if ($newStatus === 'Close' && $oldStatus !== 'Close') {
                $closedDate = Carbon::today()->toDateString();
                $closedBy = $userName;
            } elseif ($newStatus === 'Open') {
                $closedDate = null;
                $closedBy = null;
            }

            // Normalisasi Fungsi HSSE
            $func = $data['hsse_function'] ?? ($data['hsse_division'] ?? $issue->hsse_division);
            $funcNormalized = match (strtoupper(trim((string) $func))) {
                'H', 'HSSE', 'HEALTH', 'HEALTH & SAFETY', 'HEALTH & SAVETY' => 'HSSE',
                'S', 'S_SOCIAL', 'SOSIAL', 'SOCIAL' => 'Social',
                'S_SECURITY', 'SCURITY', 'SECURITY' => 'Security',
                'E', 'ENVIRONMENT', 'EMVIRONMENT' => 'Environment',
                default => $func,
            };

            $source = $data['category'] ?? ($data['source'] ?? $issue->category);
            if (!empty($source)) {
                $this->addFindingSource($source);
            }

            $issue->update([
                'hsse_division' => $funcNormalized,
                'reported_date' => $data['reported_date'] ?? $issue->reported_date,
                'title' => $data['title'] ?? $issue->title,
                'category' => $source,
                'location' => $data['location'] ?? $issue->location,
                'description' => $data['description'] ?? $issue->description,
                'due_date' => $dueDate,
                'status' => $newStatus,
                'closed_date' => $closedDate,
                'closed_by' => $closedBy,
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $issue->notes,
            ]);

            // Jika ada unggahan dokumentasi saat edit (documentation_files, attachments, atau single attachment)
            $uploadedList = [];
            if ($request && $request->hasFile('documentation_files')) {
                $uploadedList = array_merge($uploadedList, (array) $request->file('documentation_files'));
            }
            if ($request && $request->hasFile('attachments')) {
                $uploadedList = array_merge($uploadedList, (array) $request->file('attachments'));
            }

            if (!empty($uploadedList)) {
                foreach ($uploadedList as $file) {
                    if ($file instanceof UploadedFile && $file->isValid()) {
                        $this->addDocumentation($issue, $file, 'Dokumentasi Temuan', $request);
                    }
                }
            } elseif ($attachment && $attachment->isValid()) {
                $this->addDocumentation($issue, $attachment, 'Dokumentasi Temuan', $request);
            }

            $changedFields = $issue->getChanges();
            unset($changedFields['updated_at']);

            if ($oldStatus !== $issue->status) {
                IssueFollowUpLog::create([
                    'issue_follow_up_id' => $issue->id,
                    'user_id' => $userId,
                    'user_name' => $userName,
                    'action_type' => 'STATUS_CHANGED',
                    'old_status' => $oldStatus,
                    'new_status' => $issue->status,
                    'note' => "Status temuan diubah dari {$oldStatus} menjadi {$issue->status}.",
                    'followed_up_at' => now(),
                ]);
            }

            $this->recordActivityLog(
                action: 'UBAH',
                issue: $issue,
                description: "Memperbarui temuan [{$issue->issue_code}]: {$issue->title} (Status: {$issue->status})",
                changes: [
                    'before' => array_intersect_key($oldSnapshot, $changedFields),
                    'after' => $changedFields,
                ],
                request: $request
            );

            return $issue->fresh(['logs', 'documentations']);
        });
    }

    /**
     * Tambahkan catatan tindak lanjut (Follow Up).
     */
    public function addFollowUp(IssueFollowUp $issue, array $data, ?UploadedFile $attachment = null, ?Request $request = null): IssueFollowUpLog
    {
        $userName = Auth::user()?->name ?? 'Administrator';
        $userId = Auth::id();

        return DB::transaction(function () use ($issue, $data, $attachment, $request, $userName, $userId) {
            $oldStatus = $issue->status;
            $requestedStatus = $data['status'] ?? null;

            $newStatus = $oldStatus;
            if (!empty($requestedStatus)) {
                $newStatus = (strcasecmp($requestedStatus, 'Close') === 0 || strcasecmp($requestedStatus, 'Closed') === 0) ? 'Close' : 'Open';
            }

            $log = IssueFollowUpLog::create([
                'issue_follow_up_id' => $issue->id,
                'user_id' => $userId,
                'user_name' => $userName,
                'action_type' => ($newStatus === 'Close') ? 'CLOSED' : 'FOLLOW_UP',
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'note' => $data['note'],
                'followed_up_at' => now(),
            ]);

            // Jika ada attachment pada follow up, simpan juga sebagai dokumentasi temuan
            if ($attachment) {
                $this->addDocumentation($issue, $attachment, 'Dokumentasi Tindak Lanjut: ' . mb_substr($data['note'], 0, 50), $request);
            }

            $updateData = [
                'status' => $newStatus,
                'notes' => $data['note'],
            ];

            if ($newStatus === 'Close' && $oldStatus !== 'Close') {
                $updateData['closed_date'] = Carbon::today()->toDateString();
                $updateData['closed_by'] = $userName;
            } elseif ($newStatus === 'Open') {
                $updateData['due_date'] = null; // Open tidak memiliki tenggat
                $updateData['closed_date'] = null;
                $updateData['closed_by'] = null;
            }

            $issue->update($updateData);

            $this->recordActivityLog(
                action: 'UBAH',
                issue: $issue,
                description: "Menambahkan tindak lanjut temuan [{$issue->issue_code}]: {$data['note']} (Status: {$newStatus})",
                changes: [
                    'before' => ['status' => $oldStatus],
                    'after' => ['status' => $newStatus, 'note' => $data['note']],
                ],
                request: $request
            );

            return $log;
        });
    }

    /**
     * Ubah status temuan (Close atau Reopen).
     */
    public function changeStatus(IssueFollowUp $issue, string $targetStatus, ?string $note = null, ?Request $request = null, ?string $closedDate = null): IssueFollowUp
    {
        $userName = Auth::user()?->name ?? 'Administrator';
        $userId = Auth::id();

        return DB::transaction(function () use ($issue, $targetStatus, $note, $request, $userName, $userId, $closedDate) {
            $oldStatus = $issue->status;
            $normalizedStatus = (strcasecmp($targetStatus, 'Close') === 0 || strcasecmp($targetStatus, 'Closed') === 0)
                ? 'Close'
                : 'Open';

            $defaultNote = ($normalizedStatus === 'Close')
                ? 'Temuan telah diselesaikan dan ditutup (Close).'
                : "Temuan dibuka kembali dengan status Open.";

            $finalNote = !empty($note) ? $note : $defaultNote;

            IssueFollowUpLog::create([
                'issue_follow_up_id' => $issue->id,
                'user_id' => $userId,
                'user_name' => $userName,
                'action_type' => ($normalizedStatus === 'Close') ? 'CLOSED' : 'REOPENED',
                'old_status' => $oldStatus,
                'new_status' => $normalizedStatus,
                'note' => $finalNote,
                'followed_up_at' => now(),
            ]);

            $updateData = [
                'status' => $normalizedStatus,
                'notes' => $finalNote,
            ];

            if ($normalizedStatus === 'Close') {
                $updateData['closed_date'] = !empty($closedDate) ? Carbon::parse($closedDate)->toDateString() : Carbon::today()->toDateString();
                $updateData['closed_by'] = $userName;
            } else {
                $updateData['due_date'] = null; // Open tidak memiliki tenggat
                $updateData['closed_date'] = null;
                $updateData['closed_by'] = null;
            }

            $issue->update($updateData);

            $this->recordActivityLog(
                action: 'UBAH',
                issue: $issue,
                description: "Mengubah status temuan [{$issue->issue_code}] ({$oldStatus} → {$normalizedStatus}): {$finalNote}",
                changes: [
                    'before' => ['status' => $oldStatus],
                    'after' => ['status' => $normalizedStatus],
                ],
                request: $request
            );

            return $issue->fresh(['logs', 'documentations']);
        });
    }

    /**
     * Tambahkan file dokumentasi pada temuan (Add Documentation).
     */
    public function addDocumentation(IssueFollowUp $issue, UploadedFile $file, ?string $caption = null, ?Request $request = null): IssueDocumentation
    {
        $userName = Auth::user()?->name ?? 'Administrator';
        $originalName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $mimeType = $file->getClientMimeType() ?: $file->getMimeType();
        $extension = $file->getClientOriginalExtension() ?: 'bin';

        // Simpan file ke disk public: uploads/issue-documentation
        $cleanBase = preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
        $filename = 'doc_' . $issue->id . '_' . date('Ymd_His') . '_' . substr(uniqid(), -5) . '.' . $extension;
        $folder = 'uploads/issue-documentation';

        $storedPath = $file->storeAs($folder, $filename, 'public');

        $doc = IssueDocumentation::create([
            'issue_follow_up_id' => $issue->id,
            'file_name' => $filename,
            'original_name' => $originalName,
            'file_path' => $storedPath,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'uploaded_by' => $userName,
            'caption' => $caption ?: $originalName,
        ]);

        $this->recordActivityLog(
            action: 'TAMBAH',
            issue: $issue,
            description: "Menambahkan dokumentasi \"{$originalName}\" pada temuan [{$issue->issue_code}]",
            changes: null,
            request: $request
        );

        return $doc;
    }

    /**
     * Hapus file dokumentasi temuan.
     */
    public function deleteDocumentation(IssueDocumentation $doc, ?Request $request = null): bool
    {
        $issue = $doc->issueFollowUp;
        $name = $doc->original_name;

        // Hapus fisik file jika ada
        if (!empty($doc->file_path) && Storage::disk('public')->exists($doc->file_path)) {
            Storage::disk('public')->delete($doc->file_path);
        }

        $deleted = $doc->delete();

        if ($issue && $deleted) {
            $this->recordActivityLog(
                action: 'HAPUS',
                issue: $issue,
                description: "Menghapus dokumentasi \"{$name}\" dari temuan [{$issue->issue_code}]",
                changes: null,
                request: $request
            );
        }

        return (bool) $deleted;
    }

    /**
     * Hapus temuan beserta relasi dokumentasinya.
     */
    public function deleteIssue(IssueFollowUp $issue, ?Request $request = null): void
    {
        DB::transaction(function () use ($issue, $request) {
            // Hapus file dokumentasi fisik
            foreach ($issue->documentations as $doc) {
                if (!empty($doc->file_path) && Storage::disk('public')->exists($doc->file_path)) {
                    Storage::disk('public')->delete($doc->file_path);
                }
            }

            $this->recordActivityLog(
                action: 'HAPUS',
                issue: $issue,
                description: "Menghapus temuan [{$issue->issue_code}]: {$issue->title} (Status: {$issue->status})",
                changes: null,
                request: $request
            );

            $issue->delete();
        });
    }

    /**
     * Catat ke tabel activity_logs terpusat secara aman.
     */
    protected function recordActivityLog(
        string $action,
        IssueFollowUp $issue,
        string $description,
        ?array $changes = null,
        ?Request $request = null
    ): void {
        try {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => Auth::user()?->name ?? 'Administrator',
                'module' => 'Issue & Follow Up',
                'action' => $action,
                'record_type' => IssueFollowUp::class,
                'record_id' => $issue->id,
                'description' => $description,
                'changes' => $changes,
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
