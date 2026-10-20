<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\StoreEventRequest;
use App\Http\Requests\Events\UpdateEventRequest;
use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\Event;
use App\Models\EventCompany;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventController extends Controller
{
    /**
     * Display a listing of events with 4 metric cards (Total, Selesai, Tahun Ini, Bulan Ini) and 6-month trend chart.
     */
    public function index(Request $request): View|JsonResponse
    {
        // 1. Base query for filtering
        $query = Event::query()->withCount('documents')->with(['documents']);

        // Filter: Range Tanggal (Mulai & Selesai)
        $startDate = null;
        $endDate = null;

        if ($request->filled('start_date') || $request->filled('end_date')) {
            if ($request->filled('start_date')) {
                try {
                    $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
                } catch (\Exception) {}
            }
            if ($request->filled('end_date')) {
                try {
                    $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
                } catch (\Exception) {}
            }

            if ($startDate && $endDate) {
                $query->whereBetween('event_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            } elseif ($startDate) {
                $query->where('event_date', '>=', $startDate->format('Y-m-d'));
            } elseif ($endDate) {
                $query->where('event_date', '<=', $endDate->format('Y-m-d'));
            }
        } elseif ($request->filled('date_range')) {
            // Kompatibilitas jika date_range picker dikirim
            $dates = explode(' - ', $request->input('date_range'));
            if (count($dates) === 2) {
                try {
                    $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay();
                    $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay();
                    $query->whereBetween('event_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                } catch (\Exception) {}
            }
        }

        // Filter: Pencarian Global (Judul Kegiatan dan Deskripsi)
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        // Filter: Status (All / Selesai / Berlangsung / Direncanakan / Dibatalkan)
        if ($request->filled('status') && !in_array(strtolower($request->input('status')), ['all', 'semua', ''])) {
            $query->where('status', $request->input('status'));
        }

        // Filter: Perusahaan / Instansi (Opsional jika difilter spesifik)
        if ($request->filled('company') && !in_array(strtolower($request->input('company')), ['all', 'semua', ''])) {
            $query->where('company', $request->input('company'));
        }

        // 2. Kalkulasi 4 Widget / Stat Card Baru
        // Menggunakan filter tanggal/pencarian yang aktif atau global sesuai filter
        $statsQuery = clone $query;
        $now = Carbon::now();

        // 1) Total Pekerjaan / Kegiatan
        $totalKegiatan = (clone $statsQuery)->count();

        // 2) Selesai
        $selesai = (clone $statsQuery)->where('status', Event::STATUS_SELESAI)->count();

        // 3) Tahun Ini
        $currentYear = $now->year;
        $tahunIni = (clone $statsQuery)->whereYear('event_date', $currentYear)->count();

        // 4) Bulan Ini
        $currentMonth = $now->month;
        $bulanIni = (clone $statsQuery)
            ->whereYear('event_date', $currentYear)
            ->whereMonth('event_date', $currentMonth)
            ->count();

        // 3. Mini Chart / Tren 6 Bulan Terakhir untuk Widget Total Kegiatan
        $trendMonths = [];
        $trendCounts = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i);
            $trendMonths[] = $m->translatedFormat('M');
            $c = Event::query()
                ->whereYear('event_date', $m->year)
                ->whereMonth('event_date', $m->month)
                ->count();
            $trendCounts[] = $c;
        }

        // 4. Pagination Tabel Kegiatan (Urutan terbaru ke terlama)
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [5, 10, 25, 50, 100])) {
            $perPage = 10;
        }

        $events = $query->orderBy('event_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        // 5. Master Data Perusahaan / Instansi
        $companies = EventCompany::where('is_active', true)->orderBy('name')->pluck('name')->toArray();
        if (empty($companies)) {
            $companies = ['EBTKE', 'PT Geo Dipa Energi (Persero)', 'PLN', 'Kementerian ESDM'];
        }

        // Distinct companies dari data yang ada di database
        $existingCompanies = Event::query()->whereNotNull('company')->where('company', '!=', '')->distinct()->pluck('company')->toArray();
        $allCompanies = array_values(array_unique(array_merge($companies, $existingCompanies)));
        sort($allCompanies);

        // Jika AJAX request
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'stats' => [
                    'total' => $totalKegiatan,
                    'selesai' => $selesai,
                    'tahun_ini' => $tahunIni,
                    'bulan_ini' => $bulanIni,
                ],
                'trend' => [
                    'labels' => $trendMonths,
                    'data' => $trendCounts,
                ],
                'companies' => $allCompanies,
                'events' => $events,
            ]);
        }

        return view('events.index', compact(
            'events',
            'totalKegiatan',
            'selesai',
            'tahunIni',
            'bulanIni',
            'trendMonths',
            'trendCounts',
            'allCompanies',
            'perPage'
        ));
    }

    /**
     * Display the specified event with documentation and activity logs.
     */
    public function show($id): JsonResponse|View
    {
        $event = Event::with(['documents', 'activityLogs'])->findOrFail($id);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'event' => $event,
                'documents' => $event->documents->map(function ($doc) {
                    return [
                        'id' => $doc->id,
                        'title' => $doc->title,
                        'file_name' => $doc->file_name,
                        'file_path' => $doc->file_path,
                        'file_url' => $doc->file_url,
                        'mime_type' => $doc->mime_type,
                        'file_size' => $doc->file_size,
                        'created_at' => $doc->created_at ? $doc->created_at->format('d/m/Y H:i') : null,
                    ];
                }),
                'logs' => $event->activityLogs->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'user_name' => $log->user_name,
                        'action' => $log->action,
                        'description' => $log->description,
                        'created_at' => $log->created_at ? $log->created_at->format('d/m/Y H:i') : null,
                    ];
                }),
            ]);
        }

        return view('events.show', compact('event'));
    }

    /**
     * Check for potential duplicate event records.
     */
    public function checkDuplicate(Request $request): JsonResponse
    {
        $title = trim((string) $request->input('title'));
        $eventDate = $request->input('event_date');
        $location = trim((string) $request->input('location'));
        $ignoreId = $request->input('ignore_id');

        if (empty($title)) {
            return response()->json(['duplicate' => false]);
        }

        $query = Event::query();

        if ($eventDate) {
            $query->whereDate('event_date', $eventDate);
        }

        $query->where(function ($q) use ($title, $location) {
            $q->where('title', $title)
              ->orWhere('title', 'like', "%{$title}%");
        });

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $duplicates = $query->latest('id')->take(3)->get();

        if ($duplicates->isNotEmpty()) {
            $first = $duplicates->first();
            $creatorLog = ActivityLog::where('module', 'events')
                ->where('action', 'TAMBAH')
                ->where('record_id', $first->id)
                ->latest('id')
                ->first();

            return response()->json([
                'duplicate' => true,
                'message' => "Ditemukan kegiatan serupa '{$first->title}' pada tanggal yang sama (" . ($first->event_date ? $first->event_date->format('d/m/Y') : '-') . ").",
                'data' => $first,
                'created_by' => $creatorLog ? $creatorLog->user_name : ($first->created_by ?? 'Tim HSSE'),
                'created_at' => $first->created_at ? $first->created_at->format('d/m/Y H:i') : '-',
            ]);
        }

        return response()->json(['duplicate' => false]);
    }

    /**
     * Store a newly created event in storage.
     */
    public function store(StoreEventRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $userName = Auth::user()->name ?? 'Budi Santoso';

        // Pastikan nama instansi/perusahaan tersimpan di master jika baru
        if (!empty($validated['company'])) {
            EventCompany::firstOrCreate(
                ['name' => trim($validated['company'])],
                ['is_active' => true]
            );
        }

        DB::beginTransaction();
        try {
            $event = Event::create([
                'title' => $validated['title'],
                'company' => $validated['company'],
                'category' => $validated['category'] ?? 'Kegiatan',
                'unit' => $validated['unit'] ?? 'PMU',
                'event_date' => $validated['event_date'],
                'start_time' => $validated['start_time'] ?? null,
                'end_time' => $validated['end_time'] ?? null,
                'location' => $validated['location'] ?? null,
                'participants' => isset($validated['participants']) ? (int)$validated['participants'] : null,
                'pic' => $validated['pic'] ?? $userName,
                'status' => $validated['status'],
                'description' => $validated['description'] ?? null,
                'created_by' => $userName,
                'updated_by' => $userName,
            ]);

            // Handle file uploads
            if ($request->hasFile('documentation_files')) {
                foreach ($request->file('documentation_files') as $file) {
                    if ($file->isValid()) {
                        $originalName = $file->getClientOriginalName();
                        $storedPath = $file->store('events/documentation', 'public');
                        $mime = $file->getMimeType();
                        $size = $file->getSize();

                        $document = Document::create([
                            'title' => pathinfo($originalName, PATHINFO_FILENAME),
                            'document_type' => str_starts_with($mime, 'image/') ? 'Foto Kegiatan' : 'Dokumen',
                            'module' => 'events',
                            'record_id' => $event->id,
                            'file_name' => $originalName,
                            'file_path' => $storedPath,
                            'mime_type' => $mime,
                            'file_size' => $size,
                            'uploaded_by' => $userName,
                            'description' => 'Dokumentasi kegiatan ' . $event->title,
                        ]);

                        if (empty($event->documentation)) {
                            $event->update(['documentation' => $storedPath]);
                        }
                    }
                }
            }

            // Create Activity Log
            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'Events',
                'action' => 'TAMBAH',
                'record_type' => Event::class,
                'record_id' => $event->id,
                'description' => "Menambahkan event baru: {$event->title} ({$event->category})",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kegiatan berhasil ditambahkan!',
                    'data' => $event->load('documents'),
                ]);
            }

            return redirect()->route('events.index')->with('success', 'Kegiatan berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan kegiatan: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Gagal menambahkan kegiatan: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified event in storage.
     */
    public function update(UpdateEventRequest $request, $id): RedirectResponse|JsonResponse
    {
        $event = Event::findOrFail($id);
        $validated = $request->validated();
        $userName = Auth::user()->name ?? 'Budi Santoso';

        // Pastikan nama instansi/perusahaan tersimpan di master jika baru
        if (!empty($validated['company'])) {
            EventCompany::firstOrCreate(
                ['name' => trim($validated['company'])],
                ['is_active' => true]
            );
        }

        DB::beginTransaction();
        try {
            $event->update([
                'title' => $validated['title'],
                'company' => $validated['company'],
                'category' => $validated['category'] ?? $event->category ?? 'Kegiatan',
                'unit' => $validated['unit'] ?? $event->unit ?? 'PMU',
                'event_date' => $validated['event_date'],
                'start_time' => $validated['start_time'] ?? $event->start_time,
                'end_time' => $validated['end_time'] ?? $event->end_time,
                'location' => $validated['location'] ?? $event->location,
                'participants' => isset($validated['participants']) ? (int)$validated['participants'] : $event->participants,
                'pic' => $validated['pic'] ?? $event->pic,
                'status' => $validated['status'],
                'description' => $validated['description'] ?? $event->description,
                'updated_by' => $userName,
            ]);

            // Handle file uploads
            if ($request->hasFile('documentation_files')) {
                foreach ($request->file('documentation_files') as $file) {
                    if ($file->isValid()) {
                        $originalName = $file->getClientOriginalName();
                        $storedPath = $file->store('events/documentation', 'public');
                        $mime = $file->getMimeType();
                        $size = $file->getSize();

                        Document::create([
                            'title' => pathinfo($originalName, PATHINFO_FILENAME),
                            'document_type' => str_starts_with($mime, 'image/') ? 'Foto Kegiatan' : 'Dokumen',
                            'module' => 'events',
                            'record_id' => $event->id,
                            'file_name' => $originalName,
                            'file_path' => $storedPath,
                            'mime_type' => $mime,
                            'file_size' => $size,
                            'uploaded_by' => $userName,
                            'description' => 'Dokumentasi kegiatan ' . $event->title,
                        ]);

                        if (empty($event->documentation)) {
                            $event->update(['documentation' => $storedPath]);
                        }
                    }
                }
            }

            // Create Activity Log
            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'Events',
                'action' => 'UBAH',
                'record_type' => Event::class,
                'record_id' => $event->id,
                'description' => "Memperbarui event: {$event->title}",
                'changes' => [
                    'after' => $event->getChanges(),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kegiatan berhasil diperbarui!',
                    'data' => $event->load('documents'),
                ]);
            }

            return redirect()->route('events.index')->with('success', 'Kegiatan berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui kegiatan: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Gagal memperbarui kegiatan: ' . $e->getMessage());
        }
    }

    /**
     * Upload additional documentation directly to an event.
     */
    public function uploadDocumentation(Request $request, $id): JsonResponse
    {
        $event = Event::findOrFail($id);
        $request->validate([
            'files.*' => ['required', 'file', 'max:15360', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx'],
        ], [
            'files.*.max' => 'Ukuran file dokumentasi maksimal 15 MB per file.',
            'files.*.mimes' => 'Format file yang didukung: JPG, JPEG, PNG, WEBP, PDF, DOC, DOCX, XLS, XLSX.',
        ]);

        $userName = Auth::user()->name ?? 'Budi Santoso';
        $uploaded = [];

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                if ($file->isValid()) {
                    $originalName = $file->getClientOriginalName();
                    $mime = $file->getMimeType();
                    $size = $file->getSize();

                    if (str_starts_with($mime, 'image/')) {
                        $imageService = app(\App\Services\ImageUploadService::class);
                        $media = $imageService->uploadImage($file, 'events/documentation', $event, 'events', 'Dokumentasi ' . $event->title);
                        $storedPath = $media->file_path;
                        $size = $media->compressed_size;
                    } else {
                        $storedPath = $file->store('events/documentation', 'public');
                    }

                    $doc = Document::create([
                        'title' => pathinfo($originalName, PATHINFO_FILENAME),
                        'document_type' => str_starts_with($mime, 'image/') ? 'Foto Kegiatan' : 'Dokumen',
                        'module' => 'events',
                        'record_id' => $event->id,
                        'file_name' => $originalName,
                        'file_path' => $storedPath,
                        'mime_type' => $mime,
                        'file_size' => $size,
                        'uploaded_by' => $userName,
                        'description' => 'Upload tambahan untuk ' . $event->title,
                    ]);

                    $uploaded[] = [
                        'id' => $doc->id,
                        'title' => $doc->title,
                        'file_name' => $doc->file_name,
                        'file_url' => $doc->file_url,
                        'mime_type' => $doc->mime_type,
                    ];
                }
            }

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'Events',
                'action' => 'UPLOAD',
                'record_type' => Event::class,
                'record_id' => $event->id,
                'description' => 'Mengunggah ' . count($uploaded) . " file dokumentasi baru untuk {$event->title}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Dokumentasi berhasil diunggah!',
            'files' => $uploaded,
        ]);
    }

    /**
     * Remove the specified event from storage.
     */
    public function destroy(Request $request, $id): RedirectResponse|JsonResponse
    {
        $event = Event::findOrFail($id);
        $userName = Auth::user()->name ?? 'Budi Santoso';
        $title = $event->title;

        DB::beginTransaction();
        try {
            // Delete associated documents
            $documents = Document::where('module', 'events')->where('record_id', $event->id)->get();
            foreach ($documents as $doc) {
                if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                    Storage::disk('public')->delete($doc->file_path);
                }
                $doc->delete();
            }

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'Events',
                'action' => 'HAPUS',
                'record_type' => Event::class,
                'record_id' => $event->id,
                'description' => "Menghapus kegiatan: {$title}",
                'changes' => [
                    'before' => ['title' => $title, 'category' => $event->category, 'unit' => $event->unit],
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $event->delete();

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kegiatan berhasil dihapus!',
                ]);
            }

            return redirect()->route('events.index')->with('success', 'Kegiatan berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus kegiatan: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Gagal menghapus kegiatan: ' . $e->getMessage());
        }
    }

    /**
     * Dapatkan daftar perusahaan / instansi master.
     */
    public function getCompanies(): JsonResponse
    {
        $companies = EventCompany::where('is_active', true)->orderBy('name')->pluck('name')->toArray();
        $existing = Event::query()->whereNotNull('company')->where('company', '!=', '')->distinct()->pluck('company')->toArray();
        $all = array_values(array_unique(array_merge($companies, $existing)));
        sort($all);

        return response()->json([
            'success' => true,
            'companies' => $all,
        ]);
    }

    /**
     * Simpan perusahaan / instansi baru ke master.
     */
    public function storeCompany(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
        ], [
            'name.required' => 'Nama perusahaan / instansi wajib diisi.',
        ]);

        $name = trim($validated['name']);
        $company = EventCompany::firstOrCreate(
            ['name' => $name],
            ['is_active' => true]
        );

        $companies = EventCompany::where('is_active', true)->orderBy('name')->pluck('name')->toArray();

        return response()->json([
            'success' => true,
            'message' => "Instansi/Perusahaan \"{$name}\" berhasil ditambahkan.",
            'company' => $company,
            'companies' => $companies,
        ], 201);
    }

    /**
     * Hapus perusahaan / instansi master.
     */
    public function destroyCompany(Request $request, $nameOrId): JsonResponse
    {
        try {
            $company = EventCompany::where('id', $nameOrId)
                ->orWhere('name', $nameOrId)
                ->first();

            $companyName = $company ? $company->name : trim($nameOrId);

            // Cek apakah instansi sedang digunakan dalam tabel events
            $inUseCount = Event::where('company', $companyName)->count();
            if ($inUseCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Instansi/Perusahaan \"{$companyName}\" sedang digunakan oleh {$inUseCount} kegiatan dan tidak dapat dihapus.",
                ], 422);
            }

            if ($company) {
                $company->delete();
            }

            // Dapatkan daftar instansi terbaru
            $companies = EventCompany::where('is_active', true)->orderBy('name')->pluck('name')->toArray();
            $existing = Event::query()->whereNotNull('company')->where('company', '!=', '')->distinct()->pluck('company')->toArray();
            $all = array_values(array_unique(array_merge($companies, $existing)));
            sort($all);

            return response()->json([
                'success' => true,
                'message' => "Instansi/Perusahaan \"{$companyName}\" berhasil dihapus.",
                'companies' => $all,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus instansi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unduh file dokumentasi kegiatan tertentu via ID Dokumen.
     */
    public function downloadDocumentation($documentId): BinaryFileResponse|RedirectResponse
    {
        $doc = Document::where('module', 'events')->find($documentId);

        if (!$doc) {
            return back()->with('error', 'Dokumen tidak ditemukan.');
        }

        $disk = Storage::disk('public');
        if (empty($doc->file_path) || !$disk->exists($doc->file_path)) {
            return back()->with('error', "File fisik \"{$doc->file_name}\" tidak ditemukan di server.");
        }

        return response()->download($disk->path($doc->file_path), $doc->file_name);
    }

    /**
     * Unduh file dokumentasi utama event.
     */
    public function downloadEventDocumentation($eventId): BinaryFileResponse|RedirectResponse
    {
        $event = Event::with('documents')->findOrFail($eventId);
        $doc = $event->documents->first();

        $disk = Storage::disk('public');
        $filePath = $doc ? $doc->file_path : $event->documentation;

        if (empty($filePath) || !$disk->exists($filePath)) {
            return back()->with('error', "File dokumentasi kegiatan \"{$event->title}\" belum diunggah atau tidak ditemukan.");
        }

        $fileName = $doc ? $doc->file_name : basename($filePath);
        return response()->download($disk->path($filePath), $fileName);
    }

    /**
     * Export filtered events to PDF format.
     */
    public function export(Request $request): Response
    {
        $query = Event::query()->withCount('documents')->with(['documents']);

        // Filter: Range Tanggal (start_date & end_date)
        if ($request->filled('start_date') || $request->filled('end_date')) {
            if ($request->filled('start_date')) {
                $query->where('event_date', '>=', $request->input('start_date'));
            }
            if ($request->filled('end_date')) {
                $query->where('event_date', '<=', $request->input('end_date'));
            }
        } elseif ($request->filled('date_range')) {
            $dates = explode(' - ', $request->input('date_range'));
            if (count($dates) === 2) {
                try {
                    $s = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->format('Y-m-d');
                    $e = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->format('Y-m-d');
                    $query->whereBetween('event_date', [$s, $e]);
                } catch (\Exception) {}
            }
        }

        // Filter: Status
        if ($request->filled('status') && !in_array(strtolower($request->input('status')), ['all', 'semua', ''])) {
            $query->where('status', $request->input('status'));
        }

        // Filter: Perusahaan / Instansi
        if ($request->filled('company') && !in_array(strtolower($request->input('company')), ['all', 'semua', ''])) {
            $query->where('company', $request->input('company'));
        }

        // Filter: Pencarian Global (Judul & Deskripsi)
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        $records = $query->orderBy('event_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $pdf = Pdf::loadView('exports.events-pdf', [
            'reportTitle' => 'LAPORAN DAFTAR KEGIATAN & EVENTS HSSE',
            'moduleName' => 'Events',
            'filterInfo' => [
                'Status' => $request->input('status', 'Semua'),
                'Perusahaan / Instansi' => $request->input('company') ?: 'Semua',
                'Tanggal Mulai' => $request->input('start_date') ?: '-',
                'Tanggal Selesai' => $request->input('end_date') ?: '-',
                'Pencarian' => $request->input('search') ?: '-',
            ],
            'records' => $records,
            'userName' => Auth::user()->name ?? 'Administrator',
            'generatedAt' => now()->translatedFormat('d F Y, H:i:s'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Laporan-Events-HSSE-' . now()->format('Ymd-His') . '.pdf');
    }
}

