<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IssueFollowUp extends Model
{
    use HasFactory;

    protected $table = 'issue_follow_ups';

    public const CATEGORIES = [
        'Audit GIMS',
        'Audit EBTKE',
        'ADB Mission',
        'Audit PMC',
        'GRM',
        'Hasil Observasi',
    ];

    public const UNITS = [
        'PMU',
        'Dieng Unit 1',
        'Dieng Unit 2',
        'Utility',
        'JOTRE',
        'BISMO',
    ];

    public const PRIORITIES = [
        'Rendah',
        'Sedang',
        'Tinggi',
        'Kritis',
    ];

    // Status utama temuan yang konsisten: Open & Close
    public const STATUSES = [
        'Open',
        'Close',
    ];

    // 4 Fungsi Utama HSSE (menggantikan istilah Divisi)
    public const HSSE_FUNCTIONS = [
        'Health & Safety' => 'Health & Safety',
        'Security' => 'Security',
        'Environment' => 'Environment',
        'Social' => 'Social',
    ];

    // Alias untuk kompatibilitas kode lama
    public const HSSE_DIVISIONS = [
        'Health & Safety' => 'Health & Safety',
        'Security' => 'Security',
        'Environment' => 'Environment',
        'Social' => 'Social',
        'HSSE' => 'Health & Safety',
        'H' => 'Health & Safety',
        'S_SOCIAL' => 'Social',
        'S_SECURITY' => 'Security',
        'E' => 'Environment',
    ];

    public const ACTIVE_STATUSES = [
        'Open',
    ];

    public const CLOSED_STATUSES = [
        'Close',
        'Closed',
    ];

    protected $fillable = [
        'issue_code',
        'hsse_division',
        'title',
        'category',
        'unit',
        'reported_date',
        'due_date',
        'location',
        'description',
        'action_plan',
        'pic',
        'priority',
        'status',
        'closed_date',
        'closed_by',
        'notes',
        'attachment_path',
    ];

    protected $appends = [
        'is_overdue',
        'status_badge_class',
        'priority_badge_class',
        'function_label',
        'function_badge_class',
        'division_label',
        'division_badge_class',
        'source_label',
    ];

    protected function casts(): array
    {
        return [
            'reported_date' => 'date:Y-m-d',
            'due_date' => 'date:Y-m-d',
            'closed_date' => 'date:Y-m-d',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (IssueFollowUp $issue) {
            if (empty($issue->issue_code)) {
                $datePart = $issue->reported_date
                    ? Carbon::parse($issue->reported_date)->format('Ymd')
                    : date('Ymd');
                $randomPart = strtoupper(substr(uniqid(), -4));
                $candidate = "IFU-{$datePart}-{$randomPart}";
                while (static::where('issue_code', $candidate)->exists()) {
                    $candidate = "IFU-{$datePart}-" . random_int(1000, 9999);
                }
                $issue->issue_code = $candidate;
            }

            // Normalisasi Fungsi HSSE
            if (!empty($issue->hsse_division)) {
                $raw = strtoupper(trim((string) $issue->hsse_division));
                $issue->hsse_division = match ($raw) {
                    'H', 'HSSE', 'HEALTH', 'HEALTH & SAFETY', 'HEALTH & SAVETY' => 'HSSE',
                    'S', 'S_SOCIAL', 'SOSIAL', 'SOCIAL' => 'Social',
                    'S_SECURITY', 'SCURITY', 'SECURITY' => 'Security',
                    'E', 'ENVIRONMENT', 'EMVIRONMENT' => 'Environment',
                    default => $issue->hsse_division,
                };
            }

            if (empty($issue->category)) {
                $issue->category = 'Audit GIMS';
            }

            // Normalisasi nilai status agar konsisten ('Open' vs 'Close')
            if (strcasecmp((string) $issue->status, 'Close') === 0 || strcasecmp((string) $issue->status, 'Closed') === 0) {
                $issue->status = 'Close';
                if (empty($issue->closed_date)) {
                    $issue->closed_date = Carbon::today()->format('Y-m-d');
                }
            } else {
                $issue->status = 'Open';
                // Aturan: Jika status temuan Open, tanggal tenggat tidak dapat diedit atau diisi (dikosongkan)
                $issue->due_date = null;
                $issue->closed_date = null;
                $issue->closed_by = null;
            }
        });
    }

    /**
     * Relasi dokumentasi temuan (Add Documentation).
     */
    public function documentations(): HasMany
    {
        return $this->hasMany(IssueDocumentation::class, 'issue_follow_up_id')
            ->orderBy('id', 'desc');
    }

    /**
     * Relasi riwayat tindak lanjut (kronologis terbaru di atas).
     */
    public function logs(): HasMany
    {
        return $this->hasMany(IssueFollowUpLog::class, 'issue_follow_up_id')
            ->orderBy('followed_up_at', 'desc')
            ->orderBy('id', 'desc');
    }

    /**
     * Relasi riwayat tindak lanjut secara kronologis dari awal ke akhir.
     */
    public function chronologicalLogs(): HasMany
    {
        return $this->hasMany(IssueFollowUpLog::class, 'issue_follow_up_id')
            ->orderBy('followed_up_at', 'asc')
            ->orderBy('id', 'asc');
    }

    /**
     * Apakah temuan melewati tenggat dan belum berstatus Close.
     */
    public function isOverdue(): bool
    {
        if (in_array($this->status, self::CLOSED_STATUSES, true) || empty($this->due_date)) {
            return false;
        }

        return Carbon::parse($this->due_date)->startOfDay()->lt(Carbon::today());
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->isOverdue();
    }

    public function getSourceLabelAttribute(): string
    {
        return $this->category ?: '-';
    }

    public function getDateFoundAttribute(): mixed
    {
        return $this->reported_date;
    }

    public function getResponsiblePersonAttribute(): ?string
    {
        return $this->pic;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        if (in_array($this->status, self::CLOSED_STATUSES, true)) {
            return 'status-closed';
        }
        return 'status-open';
    }

    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority) {
            'Kritis' => 'priority-critical',
            'Tinggi' => 'priority-high',
            'Rendah' => 'priority-low',
            default => 'priority-medium',
        };
    }

    public function getFunctionLabelAttribute(): string
    {
        $code = strtoupper(trim((string) $this->hsse_division));
        if ($code === 'H' || $code === 'HSSE' || str_contains($code, 'HEALTH') || str_contains($code, 'SAFETY')) {
            return 'Health & Safety';
        }
        if ($code === 'S_SECURITY' || $code === 'SCURITY' || $code === 'SECURITY') {
            return 'Security';
        }
        if ($code === 'E' || $code === 'ENVIRONMENT' || $code === 'EMVIRONMENT') {
            return 'Environment';
        }
        if ($code === 'S' || $code === 'S_SOCIAL' || $code === 'SOSIAL' || $code === 'SOCIAL') {
            return 'Social';
        }
        return !empty($this->hsse_division) ? $this->hsse_division : 'Belum Diklasifikasikan';
    }

    public function getFunctionBadgeClassAttribute(): string
    {
        $label = $this->getFunctionLabelAttribute();
        return match ($label) {
            'Health & Safety' => 'division-h',
            'Security' => 'division-s-security',
            'Environment' => 'division-e',
            'Social' => 'division-s-social',
            default => 'division-unclassified',
        };
    }

    // Alias backward compatibility untuk division_label
    public function getDivisionLabelAttribute(): string
    {
        return $this->getFunctionLabelAttribute();
    }

    // Alias backward compatibility untuk division_badge_class
    public function getDivisionBadgeClassAttribute(): string
    {
        return $this->getFunctionBadgeClassAttribute();
    }

    /**
     * Scope filter berdasarkan Fungsi / Divisi HSSE (Health & Safety, Security, Environment, Social, atau unclassified).
     */
    public function scopeByDivision(Builder $query, ?string $division): Builder
    {
        if (empty($division) || strtoupper(trim($division)) === 'ALL' || strtoupper(trim($division)) === 'SEMUA') {
            return $query;
        }

        $normalized = strtoupper(trim($division));
        if ($normalized === 'UNCLASSIFIED' || $normalized === 'BELUM DIKLASIFIKASIKAN') {
            return $query->where(function (Builder $q) {
                $q->whereNull('hsse_division')->orWhere('hsse_division', '');
            });
        }

        if ($normalized === 'H' || $normalized === 'HSSE' || str_contains($normalized, 'HEALTH') || str_contains($normalized, 'SAFETY')) {
            return $query->where(function (Builder $q) {
                $q->where('hsse_division', 'Health & Safety')
                  ->orWhere('hsse_division', 'HSSE')
                  ->orWhere('hsse_division', 'H')
                  ->orWhere('hsse_division', 'like', '%Health%')
                  ->orWhere('hsse_division', 'like', '%Safety%');
            });
        }

        if ($normalized === 'S_SECURITY' || $normalized === 'SCURITY' || $normalized === 'SECURITY') {
            return $query->where(function (Builder $q) {
                $q->where('hsse_division', 'Security')
                  ->orWhere('hsse_division', 'S_SECURITY')
                  ->orWhere('hsse_division', 'SCURITY')
                  ->orWhere('hsse_division', 'Scurity');
            });
        }

        if ($normalized === 'E' || $normalized === 'ENVIRONMENT' || $normalized === 'EMVIRONMENT') {
            return $query->where(function (Builder $q) {
                $q->where('hsse_division', 'Environment')
                  ->orWhere('hsse_division', 'E')
                  ->orWhere('hsse_division', 'like', '%Environment%');
            });
        }

        if ($normalized === 'S_SOCIAL' || $normalized === 'S' || $normalized === 'SOSIAL' || $normalized === 'SOCIAL') {
            return $query->where(function (Builder $q) {
                $q->where('hsse_division', 'Social')
                  ->orWhere('hsse_division', 'S_SOCIAL')
                  ->orWhere('hsse_division', 'S')
                  ->orWhere('hsse_division', 'Sosial');
            });
        }

        return $query->where('hsse_division', $division);
    }

    /**
     * Scope untuk temuan Overdue (due_date < hari ini dan status != Close).
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNotIn('status', self::CLOSED_STATUSES)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', Carbon::today()->toDateString());
    }

    /**
     * Scope query backend untuk temuan aktif (Open).
     */
    public function scopeActiveForHighlight(Builder $query): Builder
    {
        return $query->whereIn('status', self::ACTIVE_STATUSES)
            ->orderBy('reported_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');
    }
}
