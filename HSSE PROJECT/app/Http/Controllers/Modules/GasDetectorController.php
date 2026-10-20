<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\GasDetector;
use App\Models\GasMonitoring;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GasDetectorController extends Controller
{
    public const WELL_PADS = [
        'Well Pad 7',
        'Well Pad 28',
        'Well Pad 30',
        'Well Pad 31',
    ];

    public const NAB_CONFIG = [
        'co' => [
            'val' => 25,
            'unit' => 'ppm',
            'desc' => 'Nilai Ambang Batas CO adalah 25 ppm sesuai standar Kemenaker',
        ],
        'h2s' => [
            'val' => 10,
            'unit' => 'ppm',
            'desc' => 'Nilai Ambang Batas H2S adalah 10 ppm untuk paparan kerja aman',
        ],
        'pressure' => [
            'val' => 20,
            'unit' => 'barg',
            'desc' => 'Batas operasi normal pressure adalah maksimal 20 barg',
        ],
    ];

    /**
     * Tampilkan halaman utama Gasdetektor:
     * - Tab 1: Pemantauan Gas di Well Pad (Gas Monitoring sesuai mockup acuan)
     * - Tab 2: Inventaris Perangkat Gasdetektor
     */
    public function index(Request $request): View|JsonResponse
    {
        // ---------------------------------------------------------
        // A. BAGIAN 1: PEMANTAUAN GAS DI WELL PAD (GAS MONITORING)
        // ---------------------------------------------------------
        $this->seedInitialMonitoringDataIfEmpty();

        $month = (int) $request->input('month', date('n'));
        $year = (int) $request->input('year', date('Y'));
        $wellPadFilter = (string) $request->input('well_pad', 'all');

        $currentDate = Carbon::create($year, $month, 1);
        $prevDate = (clone $currentDate)->subMonth();
        $nextDate = (clone $currentDate)->addMonth();

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $currentMonthName = ($monthNames[$month] ?? 'Bulan') . ' ' . $year;

        $nabConfig = self::NAB_CONFIG;

        // Query Gas Monitoring
        $monitoringQuery = GasMonitoring::query()
            ->byMonthYear($month, $year)
            ->byWellPad($wellPadFilter);

        $allMonitoringMonth = (clone $monitoringQuery)->get();
        $records = $monitoringQuery
            ->orderBy('measurement_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        // 4 Kartu Ringkasan Statistik Pengukuran Gas
        $highestCo = $allMonitoringMonth->sortByDesc('co_level')->first();
        $highestH2s = $allMonitoringMonth->sortByDesc('h2s_level')->first();
        $highestPressure = $allMonitoringMonth->sortByDesc('pressure')->first();
        $totalDataInMonth = $allMonitoringMonth->count();

        // 3 Panel Grafik Chart.js (CO, H2S, Pressure per Well Pad)
        $chartData = $this->buildChartData($month, $year, $wellPadFilter);

        // Rekapitulasi per Well Pad (Tabel Bawah)
        $rekapPerWellPad = $this->buildRekapPerWellPad($month, $year);

        // ---------------------------------------------------------
        // B. BAGIAN 2: INVENTARIS PERANGKAT GASDETEKTOR
        // ---------------------------------------------------------
        $deviceQuery = GasDetector::query();

        if ($request->filled('unit')) {
            $deviceQuery->where('unit', $request->input('unit'));
        }
        if ($request->filled('status')) {
            $deviceQuery->where('status', $request->input('status'));
        }
        if ($request->filled('gas_type')) {
            $deviceQuery->where('gas_type', 'like', '%' . $request->input('gas_type') . '%');
        }
        if ($request->filled('search')) {
            $term = trim($request->input('search'));
            $deviceQuery->where(function ($q) use ($term) {
                $q->where('device_code', 'like', "%{$term}%")
                  ->orWhere('model_name', 'like', "%{$term}%")
                  ->orWhere('serial_number', 'like', "%{$term}%")
                  ->orWhere('gas_type', 'like', "%{$term}%")
                  ->orWhere('unit', 'like', "%{$term}%")
                  ->orWhere('location', 'like', "%{$term}%")
                  ->orWhere('user_in_charge', 'like', "%{$term}%")
                  ->orWhere('notes', 'like', "%{$term}%");
            });
        }

        $sort = $request->input('sort', 'code');
        if ($sort === 'model') {
            $deviceQuery->orderBy('model_name', 'asc');
        } elseif ($sort === 'calibration-asc') {
            $deviceQuery->orderByRaw('CASE WHEN next_calibration_date IS NULL THEN 1 ELSE 0 END, next_calibration_date ASC');
        } elseif ($sort === 'calibration-desc') {
            $deviceQuery->orderByRaw('CASE WHEN next_calibration_date IS NULL THEN 1 ELSE 0 END, next_calibration_date DESC');
        } elseif ($sort === 'date-desc') {
            $deviceQuery->orderBy('created_at', 'desc');
        } else {
            $deviceQuery->orderBy('device_code', 'asc');
        }

        $devices = $deviceQuery->get();

        $totalDevices = $devices->count();
        $availableDevices = $devices->where('status', 'Tersedia')->count();
        $inUseDevices = $devices->where('status', 'Sedang Digunakan')->count();
        $inMaintenanceDevices = $devices->where('status', 'Dalam Perawatan')->count();
        $dueCalibrationDevices = $devices->where('status', 'Perlu Kalibrasi')->count();
        $maintenanceOrCalibration = $inMaintenanceDevices + $dueCalibrationDevices;

        $stats = [
            'total' => $totalDevices,
            'available' => $availableDevices,
            'in_use' => $inUseDevices,
            'in_maintenance' => $inMaintenanceDevices,
            'due_calibration' => $dueCalibrationDevices,
            'maintenance_or_calibration' => $maintenanceOrCalibration,
        ];

        $unitCounts = $devices->groupBy('unit')->map->count();
        $unitChart = [
            'labels' => $unitCounts->keys()->values()->all(),
            'data' => $unitCounts->values()->all(),
        ];

        $statusChart = [
            'labels' => ['Tersedia', 'Sedang Digunakan', 'Dalam Perawatan', 'Perlu Kalibrasi'],
            'data' => [$availableDevices, $inUseDevices, $inMaintenanceDevices, $dueCalibrationDevices],
            'colors' => ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
        ];

        $gasTypeCounts = $devices->groupBy(function ($item) {
            $val = trim((string) $item->gas_type);
            return empty($val) ? 'Multi-Gas' : $val;
        })->map->count();
        $gasTypeChart = [
            'labels' => $gasTypeCounts->keys()->values()->all(),
            'data' => $gasTypeCounts->values()->all(),
        ];

        $defaultUnits = ['Dieng Unit 1', 'Dieng Unit 2', 'PMU', 'Power Plant', 'Wellpad'];
        $dbUnits = GasDetector::select('unit')->distinct()->pluck('unit')->filter()->values()->all();
        $units = array_values(array_unique(array_merge($defaultUnits, $dbUnits)));

        $defaultGasTypes = [
            'Multi-Gas (H2S, CO, O2, LEL)',
            'H2S Single Gas',
            'CO Single Gas',
            'O2 Single Gas',
            'Combustible / LEL',
        ];
        $dbGasTypes = GasDetector::select('gas_type')->distinct()->pluck('gas_type')->filter()->values()->all();
        $gasTypes = array_values(array_unique(array_merge($defaultGasTypes, $dbGasTypes)));

        $statuses = ['Tersedia', 'Sedang Digunakan', 'Dalam Perawatan', 'Perlu Kalibrasi'];

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'devices' => $devices,
                'stats' => $stats,
                'unitChart' => $unitChart,
                'statusChart' => $statusChart,
                'gasTypeChart' => $gasTypeChart,
                'records' => $records,
                'chartData' => $chartData,
                'rekapPerWellPad' => $rekapPerWellPad,
            ]);
        }

        return view('gas-detector.index', compact(
            // Gas Monitoring Variables (sesuai template acuan)
            'currentMonthName',
            'prevDate',
            'nextDate',
            'month',
            'year',
            'monthNames',
            'wellPadFilter',
            'nabConfig',
            'highestCo',
            'highestH2s',
            'highestPressure',
            'totalDataInMonth',
            'records',
            'chartData',
            'rekapPerWellPad',

            // Device Inventory Variables
            'devices',
            'stats',
            'unitChart',
            'statusChart',
            'gasTypeChart',
            'units',
            'gasTypes',
            'statuses'
        ));
    }

    /**
     * Simpan data pengukuran gas baru di well pad.
     */
    public function storeMeasurement(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'measurement_date' => ['required', 'date'],
            'well_pad' => ['required', 'string', 'max:50'],
            'co_level' => ['required', 'numeric', 'min:0'],
            'h2s_level' => ['required', 'numeric', 'min:0'],
            'pressure' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $userName = Auth::user()?->name ?? 'Petugas HSSE Dieng';

        $monitoring = GasMonitoring::create([
            'measurement_date' => $validated['measurement_date'],
            'well_pad' => $validated['well_pad'],
            'co_level' => $validated['co_level'],
            'h2s_level' => $validated['h2s_level'],
            'pressure' => $validated['pressure'],
            'notes' => $validated['notes'] ?? null,
            'recorded_by' => $userName,
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'user_name' => $userName,
            'module' => 'Gasdetektor',
            'action' => 'TAMBAH',
            'record_type' => GasMonitoring::class,
            'record_id' => $monitoring->id,
            'description' => "Mencatat pengukuran gas di {$monitoring->well_pad}: CO {$monitoring->co_level} ppm ({$monitoring->co_status}), H2S {$monitoring->h2s_level} ppm ({$monitoring->h2s_status}), Pressure {$monitoring->pressure} barg ({$monitoring->pressure_status})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data pengukuran gas berhasil disimpan.',
                'data' => $monitoring,
            ]);
        }

        return redirect()->route('gas-detector.index', [
            'month' => Carbon::parse($validated['measurement_date'])->month,
            'year' => Carbon::parse($validated['measurement_date'])->year,
            'well_pad' => $validated['well_pad'],
        ])->with('success', 'Data pengukuran gas berhasil disimpan.');
    }

    /**
     * Perbarui data pengukuran gas di well pad.
     */
    public function updateMeasurement(Request $request, GasMonitoring $gasMonitoring): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'measurement_date' => ['required', 'date'],
            'well_pad' => ['required', 'string', 'max:50'],
            'co_level' => ['required', 'numeric', 'min:0'],
            'h2s_level' => ['required', 'numeric', 'min:0'],
            'pressure' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $userName = Auth::user()?->name ?? 'Petugas HSSE Dieng';

        $gasMonitoring->update([
            'measurement_date' => $validated['measurement_date'],
            'well_pad' => $validated['well_pad'],
            'co_level' => $validated['co_level'],
            'h2s_level' => $validated['h2s_level'],
            'pressure' => $validated['pressure'],
            'notes' => $validated['notes'] ?? null,
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'user_name' => $userName,
            'module' => 'Gasdetektor',
            'action' => 'UBAH',
            'record_type' => GasMonitoring::class,
            'record_id' => $gasMonitoring->id,
            'description' => "Memperbarui pengukuran gas di {$gasMonitoring->well_pad}: CO {$gasMonitoring->co_level} ppm, H2S {$gasMonitoring->h2s_level} ppm, Pressure {$gasMonitoring->pressure} barg",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data pengukuran gas berhasil diperbarui.',
                'data' => $gasMonitoring,
            ]);
        }

        return redirect()->route('gas-detector.index', [
            'month' => Carbon::parse($validated['measurement_date'])->month,
            'year' => Carbon::parse($validated['measurement_date'])->year,
        ])->with('success', 'Data pengukuran gas berhasil diperbarui.');
    }

    /**
     * Hapus data pengukuran gas di well pad.
     */
    public function destroyMeasurement(GasMonitoring $gasMonitoring): RedirectResponse|JsonResponse
    {
        $userName = Auth::user()?->name ?? 'Petugas HSSE Dieng';

        ActivityLog::create([
            'user_id' => Auth::id(),
            'user_name' => $userName,
            'module' => 'Gasdetektor',
            'action' => 'HAPUS',
            'record_type' => GasMonitoring::class,
            'record_id' => $gasMonitoring->id,
            'description' => "Menghapus rekaman pengukuran gas di {$gasMonitoring->well_pad} tanggal {$gasMonitoring->measurement_date?->format('d/m/Y')}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $gasMonitoring->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data pengukuran gas berhasil dihapus.',
            ]);
        }

        return redirect()->route('gas-detector.index')->with('success', 'Data pengukuran gas berhasil dihapus.');
    }


    /**
     * Bangun data grafik tren bulanan untuk Chart.js (3 line chart).
     */
    protected function buildChartData(int $month, int $year, string $wellPadFilter): array
    {
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $labels = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $labels[] = str_pad($d, 2, '0', STR_PAD_LEFT);
        }

        $allData = GasMonitoring::whereMonth('measurement_date', $month)
            ->whereYear('measurement_date', $year)
            ->get();

        $wellPadsConfig = [
            'Well Pad 7' => ['short' => 'WP 7', 'color' => '#10b981'],
            'Well Pad 28' => ['short' => 'WP 28', 'color' => '#3b82f6'],
            'Well Pad 30' => ['short' => 'WP 30', 'color' => '#f59e0b'],
            'Well Pad 31' => ['short' => 'WP 31', 'color' => '#8b5cf6'],
        ];

        $series = [];
        foreach ($wellPadsConfig as $wpName => $cfg) {
            if ($wellPadFilter !== 'all' && $wellPadFilter !== $wpName) {
                continue;
            }

            $wpRecords = $allData->where('well_pad', $wpName);

            $coData = [];
            $h2sData = [];
            $pressData = [];

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $targetDate = sprintf('%04d-%02d-%02d', $year, $month, $d);
                $found = $wpRecords->first(function ($item) use ($targetDate) {
                    return $item->measurement_date && $item->measurement_date->format('Y-m-d') === $targetDate;
                });

                $coData[] = $found ? round($found->co_level, 2) : null;
                $h2sData[] = $found ? round($found->h2s_level, 2) : null;
                $pressData[] = $found ? round($found->pressure, 2) : null;
            }

            $series[] = [
                'name' => $wpName,
                'short' => $cfg['short'],
                'color' => $cfg['color'],
                'co' => $coData,
                'h2s' => $h2sData,
                'pressure' => $pressData,
            ];
        }

        return [
            'labels' => $labels,
            'series' => $series,
            'nabCo' => self::NAB_CONFIG['co']['val'],
            'nabH2s' => self::NAB_CONFIG['h2s']['val'],
            'nabPressure' => self::NAB_CONFIG['pressure']['val'],
        ];
    }

    /**
     * Bangun ringkasan rekapitulasi per Well Pad (tabel bawah).
     */
    protected function buildRekapPerWellPad(int $month, int $year): array
    {
        $allData = GasMonitoring::whereMonth('measurement_date', $month)
            ->whereYear('measurement_date', $year)
            ->get();

        $rekap = [];
        foreach (self::WELL_PADS as $wp) {
            $subset = $allData->where('well_pad', $wp);
            $count = $subset->count();

            if ($count > 0) {
                $avgCo = round($subset->avg('co_level'), 1);
                $maxCo = round($subset->max('co_level'), 1);
                $avgH2s = round($subset->avg('h2s_level'), 1);
                $maxH2s = round($subset->max('h2s_level'), 1);
                $avgPress = round($subset->avg('pressure'), 1);
                $maxPress = round($subset->max('pressure'), 1);

                $exceeded = ($maxCo > self::NAB_CONFIG['co']['val']) ||
                            ($maxH2s > self::NAB_CONFIG['h2s']['val']) ||
                            ($maxPress > self::NAB_CONFIG['pressure']['val']);

                $status = $exceeded ? 'Tidak Sesuai' : 'Sesuai';
                $statusClass = $exceeded ? 'danger' : 'success';
            } else {
                $avgCo = '-';
                $maxCo = '-';
                $avgH2s = '-';
                $maxH2s = '-';
                $avgPress = '-';
                $maxPress = '-';
                $status = 'Belum Ada Data';
                $statusClass = 'secondary';
            }

            $rekap[] = [
                'well_pad' => $wp,
                'count' => $count,
                'avg_co' => $avgCo,
                'max_co' => $maxCo,
                'avg_h2s' => $avgH2s,
                'max_h2s' => $maxH2s,
                'avg_pressure' => $avgPress,
                'max_pressure' => $maxPress,
                'status' => $status,
                'status_class' => $statusClass,
            ];
        }

        return $rekap;
    }

    /**
     * Isi data awal realistis untuk well pad Dieng bila tabel masih kosong.
     */
    protected function seedInitialMonitoringDataIfEmpty(): void
    {
        if (GasMonitoring::count() > 0) {
            return;
        }

        $now = Carbon::now();
        $daysInMonth = $now->daysInMonth;
        $year = $now->year;
        $month = $now->month;

        $samples = [
            'Well Pad 7' => ['co_base' => 8.5, 'h2s_base' => 3.2, 'press_base' => 15.5],
            'Well Pad 28' => ['co_base' => 12.0, 'h2s_base' => 4.5, 'press_base' => 16.8],
            'Well Pad 30' => ['co_base' => 6.0, 'h2s_base' => 2.8, 'press_base' => 14.2],
            'Well Pad 31' => ['co_base' => 10.2, 'h2s_base' => 3.9, 'press_base' => 17.1],
        ];

        // Buat pengukuran setiap 2-3 hari sepanjang bulan berjalan
        for ($d = 1; $d <= min($now->day, $daysInMonth); $d += 2) {
            $mDate = sprintf('%04d-%02d-%02d', $year, $month, $d);

            foreach ($samples as $wp => $cfg) {
                $co = round($cfg['co_base'] + (sin($d) * 3) + (rand(-10, 15) / 10), 1);
                $h2s = round($cfg['h2s_base'] + (cos($d) * 1.5) + (rand(-5, 10) / 10), 1);
                $press = round($cfg['press_base'] + (sin($d * 0.5) * 1.8) + (rand(-5, 8) / 10), 1);

                $co = max(1.0, $co);
                $h2s = max(0.5, $h2s);
                $press = max(5.0, $press);

                GasMonitoring::create([
                    'measurement_date' => $mDate,
                    'well_pad' => $wp,
                    'co_level' => $co,
                    'h2s_level' => $h2s,
                    'pressure' => $press,
                    'notes' => "Pemantauan rutin operasional {$wp}",
                    'recorded_by' => 'Petugas HSSE Dieng',
                ]);
            }
        }
    }

    // =========================================================================
    // C. INVENTARIS PERANGKAT GASDETEKTOR (FUNGSI ASLI YANG DIPERTAHANKAN 100%)
    // =========================================================================

    public function checkDuplicate(Request $request): JsonResponse
    {
        $deviceCode = trim((string) $request->input('device_code'));
        $ignoreId = $request->input('ignore_id');

        if (empty($deviceCode)) {
            return response()->json(['duplicate' => false]);
        }

        $query = GasDetector::where('device_code', $deviceCode);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $existing = $query->first();

        if ($existing) {
            return response()->json([
                'duplicate' => true,
                'message' => "Kode perangkat '{$existing->device_code}' sudah terdaftar untuk {$existing->model_name} ({$existing->unit}).",
                'data' => $existing,
            ]);
        }

        return response()->json(['duplicate' => false]);
    }

    public function show(Request $request, $id): JsonResponse|View
    {
        $device = GasDetector::find($id);

        if (!$device) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data perangkat Gasdetektor tidak ditemukan.',
                ], 404);
            }
            return redirect()->route('gas-detector.index')->with('error', 'Data perangkat Gasdetektor tidak ditemukan.');
        }

        $activityLogs = ActivityLog::where('module', 'Gasdetektor')
            ->where(function ($q) use ($device) {
                $q->where('record_id', $device->id)
                  ->orWhere('description', 'like', "%{$device->device_code}%");
            })
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'device' => $device,
                'is_calibration_due' => $device->isCalibrationDue(),
                'activity_logs' => $activityLogs,
            ]);
        }

        return view('gas-detector.show', compact('device', 'activityLogs'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya Administrator yang dapat menambahkan perangkat Gasdetektor.',
                ], 403);
            }
            abort(403, 'Akses ditolak. Hanya Administrator yang dapat menambahkan perangkat Gasdetektor.');
        }

        $validated = $request->validate([
            'device_code' => ['required', 'string', 'max:50', 'unique:gas_detectors,device_code'],
            'model_name' => ['required', 'string', 'max:150'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'gas_type' => ['nullable', 'string', 'max:150'],
            'unit' => ['required', 'string', 'max:50'],
            'location' => ['nullable', 'string', 'max:150'],
            'status' => ['required', 'string', 'in:Tersedia,Sedang Digunakan,Dalam Perawatan,Perlu Kalibrasi'],
            'last_calibration_date' => ['nullable', 'date'],
            'next_calibration_date' => ['nullable', 'date', 'after_or_equal:last_calibration_date'],
            'user_in_charge' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ], [
            'device_code.required' => 'Kode alat wajib diisi.',
            'device_code.unique' => 'Kode alat sudah terdaftar di sistem. Gunakan kode lain.',
            'model_name.required' => 'Model atau tipe alat wajib diisi.',
            'unit.required' => 'Unit operasi wajib dipilih.',
            'status.required' => 'Status alat wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
            'next_calibration_date.after_or_equal' => 'Tanggal kalibrasi berikutnya tidak boleh sebelum tanggal kalibrasi terakhir.',
        ]);

        $userName = Auth::user()?->name ?? 'Administrator Dieng';

        DB::beginTransaction();
        try {
            $device = GasDetector::create($validated);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'Gasdetektor',
                'action' => 'TAMBAH',
                'record_type' => GasDetector::class,
                'record_id' => $device->id,
                'description' => "Menambahkan perangkat Gasdetektor baru: {$device->device_code} - {$device->model_name} ({$device->unit}, Status: {$device->status})",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Perangkat Gasdetektor berhasil ditambahkan.',
                    'device' => $device,
                ], 201);
            }

            return redirect()->route('gas-detector.index')->with('success', 'Perangkat Gasdetektor berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan perangkat Gasdetektor: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Gagal menyimpan perangkat Gasdetektor: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id): RedirectResponse|JsonResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya Administrator yang dapat mengubah data perangkat Gasdetektor.',
                ], 403);
            }
            abort(403, 'Akses ditolak. Hanya Administrator yang dapat mengubah data perangkat Gasdetektor.');
        }

        $device = GasDetector::find($id);

        if (!$device) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data perangkat Gasdetektor tidak ditemukan.',
                ], 404);
            }
            return redirect()->route('gas-detector.index')->with('error', 'Data perangkat Gasdetektor tidak ditemukan.');
        }

        $validated = $request->validate([
            'device_code' => ['required', 'string', 'max:50', 'unique:gas_detectors,device_code,' . $device->id],
            'model_name' => ['required', 'string', 'max:150'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'gas_type' => ['nullable', 'string', 'max:150'],
            'unit' => ['required', 'string', 'max:50'],
            'location' => ['nullable', 'string', 'max:150'],
            'status' => ['required', 'string', 'in:Tersedia,Sedang Digunakan,Dalam Perawatan,Perlu Kalibrasi'],
            'last_calibration_date' => ['nullable', 'date'],
            'next_calibration_date' => ['nullable', 'date', 'after_or_equal:last_calibration_date'],
            'user_in_charge' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ], [
            'device_code.required' => 'Kode alat wajib diisi.',
            'device_code.unique' => 'Kode alat sudah terdaftar di sistem. Gunakan kode lain.',
            'model_name.required' => 'Model atau tipe alat wajib diisi.',
            'unit.required' => 'Unit operasi wajib dipilih.',
            'status.required' => 'Status alat wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
            'next_calibration_date.after_or_equal' => 'Tanggal kalibrasi berikutnya tidak boleh sebelum tanggal kalibrasi terakhir.',
        ]);

        $userName = Auth::user()?->name ?? 'Administrator Dieng';
        $oldSnapshot = $device->only(array_keys($validated));

        DB::beginTransaction();
        try {
            $device->update($validated);

            $before = [];
            $after = [];
            foreach ($validated as $field => $newVal) {
                $oldVal = $oldSnapshot[$field] ?? null;
                if ($oldVal instanceof Carbon) {
                    $oldVal = $oldVal->format('Y-m-d');
                }
                if ($newVal instanceof Carbon) {
                    $newVal = $newVal->format('Y-m-d');
                }
                if ((string) $oldVal !== (string) $newVal) {
                    $before[$field] = $oldVal;
                    $after[$field] = $newVal;
                }
            }

            $diffText = '';
            if (!empty($before)) {
                $diffText = ' Perubahan: ' . implode(', ', array_map(function ($key, $oldVal, $newVal) {
                    return "{$key} ('{$oldVal}' -> '{$newVal}')";
                }, array_keys($before), $before, $after));
            }

            $changesPayload = !empty($before) ? ['before' => $before, 'after' => $after] : null;

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'Gasdetektor',
                'action' => 'UBAH',
                'record_type' => GasDetector::class,
                'record_id' => $device->id,
                'description' => "Memperbarui perangkat Gasdetektor: {$device->device_code} - {$device->model_name} ({$device->unit}).{$diffText}",
                'changes' => $changesPayload,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data perangkat Gasdetektor berhasil diperbarui.',
                    'device' => $device,
                ]);
            }

            return redirect()->route('gas-detector.index')->with('success', 'Data perangkat Gasdetektor berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui perangkat Gasdetektor: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Gagal memperbarui perangkat Gasdetektor: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id): RedirectResponse|JsonResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya Administrator yang dapat menghapus perangkat Gasdetektor.',
                ], 403);
            }
            abort(403, 'Akses ditolak. Hanya Administrator yang dapat menghapus perangkat Gasdetektor.');
        }

        $device = GasDetector::find($id);

        if (!$device) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data perangkat Gasdetektor tidak ditemukan.',
                ], 404);
            }
            return redirect()->route('gas-detector.index')->with('error', 'Data perangkat Gasdetektor tidak ditemukan.');
        }

        $userName = Auth::user()?->name ?? 'Administrator Dieng';

        DB::beginTransaction();
        try {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $userName,
                'module' => 'Gasdetektor',
                'action' => 'HAPUS',
                'record_type' => GasDetector::class,
                'record_id' => $device->id,
                'description' => "Menghapus perangkat Gasdetektor: {$device->device_code} - {$device->model_name} ({$device->unit})",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $device->delete();

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Perangkat Gasdetektor berhasil dihapus.',
                ]);
            }

            return redirect()->route('gas-detector.index')->with('success', 'Perangkat Gasdetektor berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus perangkat Gasdetektor: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Gagal menghapus perangkat Gasdetektor: ' . $e->getMessage());
        }
    }

    /**
     * Export Gas Monitoring measurements to PDF.
     */
    public function exportMeasurements(Request $request): Response
    {
        $month = (int) $request->input('month', date('n'));
        $year = (int) $request->input('year', date('Y'));
        $wellPadFilter = (string) $request->input('well_pad', 'all');

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $periodLabel = ($monthNames[$month] ?? 'Bulan ' . $month) . ' ' . $year;

        $query = GasMonitoring::query()
            ->byMonthYear($month, $year)
            ->byWellPad($wellPadFilter);

        $records = $query->orderBy('measurement_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $pdf = Pdf::loadView('exports.gas-monitoring-pdf', [
            'reportTitle' => 'LAPORAN PEMANTAUAN GAS WELL PAD (GAS MONITORING)',
            'moduleName' => 'Gas Monitoring',
            'filterInfo' => [
                'Periode' => $periodLabel,
                'Well Pad' => $wellPadFilter === 'all' ? 'Semua Well Pad' : $wellPadFilter,
                'Total Rekord' => $records->count() . ' Pengukuran',
            ],
            'records' => $records,
            'userName' => Auth::user()->name ?? 'Administrator',
            'generatedAt' => now()->translatedFormat('d F Y, H:i:s'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Laporan-Gas-Monitoring-' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . now()->format('His') . '.pdf');
    }

    /**
     * Alias export function to support both route formats.
     */
    public function export(Request $request): Response
    {
        return $this->exportMeasurements($request);
    }
}
