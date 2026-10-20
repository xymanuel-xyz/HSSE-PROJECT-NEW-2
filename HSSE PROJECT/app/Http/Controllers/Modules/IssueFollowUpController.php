<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIssueFollowUpRequest;
use App\Http\Requests\UpdateIssueFollowUpRequest;
use App\Models\ActivityLog;
use App\Models\FindingSource;
use App\Models\IssueDocumentation;
use App\Models\IssueFollowUp;
use App\Services\IssueFollowUpService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class IssueFollowUpController extends Controller
{
    public function __construct(
        protected IssueFollowUpService $service
    ) {}

    /**
     * Tampilkan halaman utama Issue & Follow Up beserta ringkasan visual, grafik per fungsi, dan tabel temuan.
     */
    public function index(Request $request): View|JsonResponse
    {
        $activeFunction = trim((string) $request->input('hsse_function', $request->input('function', $request->input('hsse_division', $request->input('division', '')))));
        if (strtoupper($activeFunction) === 'ALL' || strtoupper($activeFunction) === 'SEMUA') {
            $activeFunction = '';
        }

        $activeStatus = trim((string) $request->input('status', ''));

        $query = $this->service->buildFilteredQuery($request);
        $allFiltered = (clone $query)->get();

        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 15, 25, 50, 100], true) ? $perPage : 10;
        $issues = $query->paginate($perPage)->withQueryString();

        // Ringkasan aktual dari database
        $stats = $this->service->calculateStats($allFiltered);

        // Ringkasan per 4 Fungsi (HSSE, Social, Security, Environment)
        $reqWithoutFunc = Request::create('/issue-follow-up', 'GET', collect($request->query())->except(['hsse_function', 'function', 'hsse_division', 'division', 'page'])->all());
        $baseWithoutFunc = $this->service->buildFilteredQuery($reqWithoutFunc)->get();
        $functionSummary = $this->service->calculateDivisionSummary($baseWithoutFunc);

        // Grafik aktual per masing-masing fungsi (Requirement 18: Open, Close, Total)
        $functionCharts = $this->service->buildFunctionCharts($baseWithoutFunc);

        // Sumber temuan dari tabel master finding_sources
        $sources = $this->service->getActiveSources();
        $functions = IssueFollowUp::HSSE_FUNCTIONS;
        $statuses = IssueFollowUp::STATUSES;

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'active_function' => $activeFunction,
                'active_status' => $activeStatus,
                'issues' => $issues->items(),
                'pagination' => [
                    'current_page' => $issues->currentPage(),
                    'last_page' => $issues->lastPage(),
                    'per_page' => $issues->perPage(),
                    'total' => $issues->total(),
                ],
                'stats' => $stats,
                'function_summary' => $functionSummary,
                'function_charts' => $functionCharts,
                'sources' => $sources,
            ]);
        }

        return view('data.issue-follow-up.index', compact(
            'issues',
            'allFiltered',
            'stats',
            'functionSummary',
            'functionCharts',
            'activeFunction',
            'activeStatus',
            'functions',
            'sources',
            'statuses'
        ));
    }

    /**
     * Halaman form tambah temuan baru.
     */
    public function create(): View
    {
        $functions = IssueFollowUp::HSSE_FUNCTIONS;
        $sources = $this->service->getActiveSources();
        $statuses = IssueFollowUp::STATUSES;

        return view('data.issue-follow-up.create', compact(
            'functions',
            'sources',
            'statuses'
        ));
    }

    /**
     * Simpan temuan baru ke database MySQL.
     */
    public function store(StoreIssueFollowUpRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $issue = $this->service->createIssue(
                $request->validated(),
                $request->file('attachment'),
                $request
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Temuan HSSE baru berhasil disimpan ke database.',
                    'issue' => $issue,
                    'stats' => $this->service->calculateStats(IssueFollowUp::all()),
                ], 201);
            }

            return redirect()->route('issue-follow-up.index')
                ->with('success', "Temuan [{$issue->issue_code}] \"{$issue->title}\" berhasil ditambahkan.");
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan temuan: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Gagal menyimpan temuan: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan detail temuan beserta riwayat tindak lanjut & daftar dokumentasi.
     */
    public function show(Request $request, $id): View|JsonResponse|RedirectResponse
    {
        $issue = IssueFollowUp::with(['logs', 'chronologicalLogs', 'documentations'])->find($id);

        if (!$issue) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data temuan tidak ditemukan.',
                ], 404);
            }

            return redirect()->route('issue-follow-up.index')
                ->with('error', 'Data temuan tidak ditemukan.');
        }

        $activities = ActivityLog::where('module', 'Issue & Follow Up')
            ->where('record_id', $issue->id)
            ->latest('id')
            ->take(20)
            ->get();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'issue' => $issue,
                'logs' => $issue->logs,
                'chronological_logs' => $issue->chronologicalLogs,
                'documentations' => $issue->documentations,
                'activities' => $activities,
            ]);
        }

        return view('data.issue-follow-up.show', compact('issue', 'activities'));
    }

    /**
     * Halaman form edit temuan.
     */
    public function edit($id): View|RedirectResponse
    {
        $issue = IssueFollowUp::with(['logs', 'documentations'])->find($id);

        if (!$issue) {
            return redirect()->route('issue-follow-up.index')
                ->with('error', 'Data temuan tidak ditemukan.');
        }

        $functions = IssueFollowUp::HSSE_FUNCTIONS;
        $sources = $this->service->getActiveSources();
        if ($issue->category && !in_array($issue->category, $sources, true)) {
            $sources[] = $issue->category;
        }
        $statuses = IssueFollowUp::STATUSES;

        return view('data.issue-follow-up.edit', compact(
            'issue',
            'functions',
            'sources',
            'statuses'
        ));
    }

    /**
     * Perbarui data temuan di MySQL.
     */
    public function update(UpdateIssueFollowUpRequest $request, $id): JsonResponse|RedirectResponse
    {
        $issue = IssueFollowUp::find($id);

        if (!$issue) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data temuan tidak ditemukan.',
                ], 404);
            }

            return redirect()->route('issue-follow-up.index')
                ->with('error', 'Data temuan tidak ditemukan.');
        }

        try {
            $updated = $this->service->updateIssue(
                $issue,
                $request->validated(),
                $request->file('attachment'),
                $request
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data temuan berhasil diperbarui.',
                    'issue' => $updated,
                    'stats' => $this->service->calculateStats(IssueFollowUp::all()),
                ]);
            }

            return redirect()->route('issue-follow-up.show', $updated->id)
                ->with('success', "Data temuan [{$updated->issue_code}] berhasil diperbarui.");
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui temuan: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Gagal memperbarui temuan: ' . $e->getMessage());
        }
    }

    /**
     * Tutup temuan (tandai Close).
     */
    public function close(Request $request, $id): JsonResponse|RedirectResponse
    {
        $issue = IssueFollowUp::find($id);

        if (!$issue) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data temuan tidak ditemukan.',
                ], 404);
            }
            return redirect()->route('issue-follow-up.index')->with('error', 'Data temuan tidak ditemukan.');
        }

        $validated = $request->validate([
            'note' => ['nullable', 'string'],
            'closed_date' => ['nullable', 'date'],
        ]);

        try {
            $updated = $this->service->changeStatus(
                $issue,
                'Close',
                $validated['note'] ?? 'Temuan telah diselesaikan dan ditutup (Close).',
                $request,
                $validated['closed_date'] ?? null
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Temuan berhasil ditutup (Close).',
                    'issue' => $updated,
                    'stats' => $this->service->calculateStats(IssueFollowUp::all()),
                ]);
            }

            return back()->with('success', "Temuan [{$updated->issue_code}] berhasil ditutup (Close).");
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menutup temuan: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Gagal menutup temuan: ' . $e->getMessage());
        }
    }

    /**
     * Buka kembali temuan yang sudah Close (Reopen to Open).
     */
    public function reopen(Request $request, $id): JsonResponse|RedirectResponse
    {
        $issue = IssueFollowUp::find($id);

        if (!$issue) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data temuan tidak ditemukan.',
                ], 404);
            }
            return redirect()->route('issue-follow-up.index')->with('error', 'Data temuan tidak ditemukan.');
        }

        $validated = $request->validate([
            'note' => ['required', 'string'],
        ], [
            'note.required' => 'Alasan membuka kembali temuan wajib diisi.',
        ]);

        try {
            $updated = $this->service->changeStatus(
                $issue,
                'Open',
                $validated['note'],
                $request
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Temuan berhasil dibuka kembali dengan status Open.",
                    'issue' => $updated,
                    'stats' => $this->service->calculateStats(IssueFollowUp::all()),
                ]);
            }

            return back()->with('success', "Temuan [{$updated->issue_code}] berhasil dibuka kembali.");
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuka kembali temuan: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Gagal membuka kembali temuan: ' . $e->getMessage());
        }
    }

    /**
     * Hapus temuan beserta riwayat dan dokumentasinya.
     */
    public function destroy(Request $request, $id): JsonResponse|RedirectResponse
    {
        $issue = IssueFollowUp::find($id);

        if (!$issue) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data temuan tidak ditemukan.',
                ], 404);
            }
            return redirect()->route('issue-follow-up.index')->with('error', 'Data temuan tidak ditemukan.');
        }

        try {
            $code = $issue->issue_code;
            $this->service->deleteIssue($issue, $request);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Data temuan [{$code}] berhasil dihapus.",
                    'stats' => $this->service->calculateStats(IssueFollowUp::all()),
                ]);
            }

            return redirect()->route('issue-follow-up.index')
                ->with('success', "Data temuan [{$code}] berhasil dihapus.");
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus temuan: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Gagal menghapus temuan: ' . $e->getMessage());
        }
    }

    /**
     * Fitur Add Documentation: Unggah satu atau lebih file dokumentasi untuk temuan.
     */
    public function storeDocumentation(Request $request, $id): JsonResponse|RedirectResponse
    {
        $issue = IssueFollowUp::find($id);

        if (!$issue) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Temuan tidak ditemukan.'], 404);
            }
            return back()->with('error', 'Data temuan tidak ditemukan.');
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx', 'max:10240'],
            'caption' => ['nullable', 'string', 'max:255'],
        ], [
            'file.required' => 'Pilih file dokumentasi yang akan diunggah.',
            'file.mimes' => 'Format file yang diperbolehkan: JPG, PNG, WEBP, PDF, DOC, DOCX, XLS, XLSX.',
            'file.max' => 'Ukuran file dokumentasi maksimal 10 MB.',
        ]);

        try {
            $doc = $this->service->addDocumentation(
                $issue,
                $request->file('file'),
                $request->input('caption'),
                $request
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Dokumentasi \"{$doc->original_name}\" berhasil ditambahkan.",
                    'documentation' => $doc,
                ], 201);
            }

            return back()->with('success', "Dokumentasi \"{$doc->original_name}\" berhasil ditambahkan.");
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengunggah dokumentasi: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Gagal mengunggah dokumentasi: ' . $e->getMessage());
        }
    }

    /**
     * Hapus file dokumentasi temuan.
     */
    public function destroyDocumentation(Request $request, $docId): JsonResponse|RedirectResponse
    {
        $doc = IssueDocumentation::find($docId);

        if (!$doc) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Dokumentasi tidak ditemukan.'], 404);
            }
            return back()->with('error', 'Dokumentasi tidak ditemukan.');
        }

        try {
            $name = $doc->original_name;
            $this->service->deleteDocumentation($doc, $request);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Dokumentasi \"{$name}\" berhasil dihapus.",
                ]);
            }

            return back()->with('success', "Dokumentasi \"{$name}\" berhasil dihapus.");
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Gagal menghapus dokumentasi: ' . $e->getMessage());
        }
    }

    /**
     * Lihat / pratinjau file dokumentasi di browser (inline).
     */
    public function viewDocumentation($docId): Response|BinaryFileResponse|RedirectResponse
    {
        $doc = IssueDocumentation::find($docId);

        if (!$doc) {
            abort(404, 'Data dokumentasi tidak ditemukan.');
        }

        $filePath = $doc->file_path;
        $disk = Storage::disk('public');

        if (empty($filePath) || !$disk->exists($filePath)) {
            // Berikan feedback yang ramah pengguna jika file fisik tidak ada di server
            return back()->with('error', "File fisik \"{$doc->original_name}\" tidak ditemukan pada server penyimpanan.");
        }

        $fullPath = $disk->path($filePath);
        $mime = $doc->mime_type ?: ($disk->mimeType($filePath) ?: 'application/octet-stream');

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . addslashes($doc->original_name) . '"',
        ]);
    }

    /**
     * Unduh file dokumentasi.
     */
    public function downloadDocumentation($docId): BinaryFileResponse|RedirectResponse
    {
        $doc = IssueDocumentation::find($docId);

        if (!$doc) {
            return back()->with('error', 'Data dokumentasi tidak ditemukan.');
        }

        $filePath = $doc->file_path;
        $disk = Storage::disk('public');

        if (empty($filePath) || !$disk->exists($filePath)) {
            return back()->with('error', "File fisik \"{$doc->original_name}\" tidak ditemukan pada server penyimpanan.");
        }

        return response()->download(
            $disk->path($filePath),
            $doc->original_name
        );
    }

    /**
     * Tambahkan sumber temuan baru melalui antarmuka website (AJAX / Form).
     */
    public function storeSource(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Nama sumber temuan wajib diisi.',
        ]);

        try {
            $source = $this->service->addFindingSource($validated['name'], $validated['description'] ?? null);
            $allSources = $this->service->getActiveSources();

            return response()->json([
                'success' => true,
                'message' => "Sumber temuan \"{$source->name}\" berhasil ditambahkan dan langsung tersedia.",
                'source' => $source,
                'sources' => $allSources,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan sumber temuan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hapus sumber temuan dari master data.
     */
    public function destroySource(Request $request, $nameOrId): JsonResponse
    {
        try {
            $source = FindingSource::where('id', $nameOrId)
                ->orWhere('name', $nameOrId)
                ->first();

            if ($source) {
                $sourceName = $source->name;
                $source->delete();
            } else {
                $sourceName = $nameOrId;
            }

            return response()->json([
                'success' => true,
                'message' => "Sumber temuan \"{$sourceName}\" berhasil dihapus dari opsi.",
                'sources' => $this->service->getActiveSources(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus sumber temuan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Dapatkan daftar sumber temuan aktif (JSON).
     */
    public function getSources(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'sources' => $this->service->getActiveSources(),
        ]);
    }

    /**
     * Ekspor daftar temuan ke format PDF per Fungsi (Requirement 12).
     */
    public function export(Request $request): Response
    {
        // Tangani filter multiple functions (hsse_functions) atau single function
        $functionsInput = $request->input('hsse_functions', $request->input('hsse_function', $request->input('function', $request->input('hsse_division', $request->input('division', '')))));
        
        $selectedFunctions = [];
        if (is_array($functionsInput)) {
            $selectedFunctions = array_values(array_filter($functionsInput, fn($f) => !empty($f) && strtoupper(trim($f)) !== 'ALL' && strtoupper(trim($f)) !== 'SEMUA'));
        } elseif (!empty($functionsInput) && strtoupper($functionsInput) !== 'ALL' && strtoupper($functionsInput) !== 'SEMUA') {
            $selectedFunctions = [$functionsInput];
        }

        // Ambil data sesuai filter fungsi & filter status
        $query = $this->service->buildFilteredQuery($request);
        $records = $query->with('documentations')->get();

        $functionTitle = !empty($selectedFunctions)
            ? implode(', ', $selectedFunctions)
            : "Seluruh Fungsi Safeguard";

        $statusFilter = $request->input('status', 'Semua');
        if (empty($statusFilter)) {
            $statusFilter = 'Semua (All)';
        }

        $pdf = Pdf::loadView('exports.issue-follow-up-pdf', [
            'reportTitle' => 'LAPORAN TEMUAN & TINDAK LANJUT SAFEGUARD',
            'functionName' => $functionTitle,
            'moduleName' => 'Issue & Follow Up',
            'filterInfo' => [
                'Fungsi Safeguard' => $functionTitle,
                'Status' => $statusFilter,
                'Sumber Temuan' => $request->input('source', $request->input('category', 'Semua')),
                'Pencarian' => $request->input('search') ?: '-',
            ],
            'records' => $records,
            'userName' => auth()->user()?->name ?? 'Administrator',
            'generatedAt' => now()->translatedFormat('d F Y, H:i:s'),
        ])->setPaper('a4', 'landscape');

        $fileName = 'Laporan-Temuan-Safeguard-' . now()->format('Ymd-His') . '.pdf';
        return $pdf->download($fileName);
    }
}
