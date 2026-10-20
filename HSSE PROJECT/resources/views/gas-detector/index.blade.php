@extends('layouts.app', [
    'title' => 'Gas Monitoring - Pemantauan Gas Well Pad',
    'activeModule' => 'gas-detector'
])

@push('styles')
<style>
    /* Styling Nav Tabs */
    .gas-nav-tabs {
        border-bottom: 1.5px solid #e2e8f0;
        margin-bottom: 20px;
        gap: 8px;
    }
    .gas-nav-tabs .nav-link {
        font-weight: 700;
        font-size: 13px;
        color: #64748b;
        border: none;
        border-bottom: 2.5px solid transparent;
        padding: 10px 18px;
        border-radius: 0;
        background: transparent;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .gas-nav-tabs .nav-link:hover {
        color: #0f172a;
        border-color: #cbd5e1;
    }
    .gas-nav-tabs .nav-link.active {
        color: #059669;
        border-bottom: 2.5px solid #059669;
        background: transparent;
    }

    /* Styling Gas Monitoring (sesuai template acuan) */
    .nab-card {
        border-radius: 12px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .nab-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }
    .stat-card-gas {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        padding: 18px 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .badge-sesuai {
        background-color: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
        font-weight: 600;
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 6px;
        display: inline-block;
    }
    .badge-tidaksesuai {
        background-color: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
        font-weight: 600;
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 6px;
        display: inline-block;
    }
    .badge-warning-custom {
        background-color: #fffbeb;
        color: #d97706;
        border: 1px solid #fde68a;
        font-weight: 600;
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 6px;
        display: inline-block;
    }
    .badge-secondary-custom {
        background-color: #f8fafc;
        color: #64748b;
        border: 1px solid #e2e8f0;
        font-weight: 500;
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 6px;
        display: inline-block;
    }
    .chart-card-gas {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        padding: 18px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .chart-card-gas h6 {
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .table-gas thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 11.5px;
        font-weight: 600;
        border-top: 1px solid #edf2f7;
        border-bottom: 1px solid #edf2f7;
        padding: 10px 12px;
    }
    .table-gas tbody td {
        font-size: 12px;
        padding: 10px 12px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }

    /* Device Inventory Styling */
    .filter-grid-gas {
        display: grid;
        grid-template-columns: 160px 170px 200px 1fr auto;
        gap: 10px;
        align-items: center;
    }
    .charts-row-gas {
        display: grid;
        grid-template-columns: 1.2fr 1fr 1fr;
        gap: 14px;
        margin-bottom: 14px;
    }
    .donut-layout-gas {
        display: grid;
        grid-template-columns: 46% 54%;
        align-items: center;
        height: 175px;
        gap: 8px;
    }
    .device-code-badge {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-weight: 700;
        background: #f1f5f9;
        color: #0f172a;
        padding: 2px 7px;
        border-radius: 4px;
        border: 1px solid #cbd5e1;
        font-size: 11px;
        display: inline-block;
    }
    .status-badge-custom {
        display: inline-block;
        padding: 3px 9px;
        border-radius: 4px;
        font-size: 10.5px;
        font-weight: 600;
        border: 1px solid;
        white-space: nowrap;
    }
    .status-tersedia { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
    .status-inuse { background: #dbeafe; color: #1d4ed8; border-color: #bfdbfe; }
    .status-maintenance { background: #fef3c7; color: #b45309; border-color: #fde68a; }
    .status-calibration { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }

    .cal-due-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fca5a5;
        border-radius: 4px;
        padding: 1px 6px;
        font-size: 10px;
        font-weight: 700;
    }
    .cal-ok-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        color: #475569;
        font-size: 11px;
    }
</style>
@endpush

@section('content')
<!-- HEADER HALAMAN (Konsisten Tema HSSE Dieng) -->
<div class="page-header">
    <div class="header-title-wrapper">
        <div class="header-icon-box teal">
            <i class="bi bi-broadcast-pin"></i>
        </div>
        <div>
            <h1 class="page-heading">
                Gas Monitoring
                <span class="ms-2 fs-6 fw-semibold text-secondary">Pemantauan Gas di Well Pad</span>
            </h1>
            <div class="breadcrumb-nav">
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <span class="mx-1 text-muted">›</span>
                <span class="text-secondary">Data</span>
                <span class="mx-1 text-muted">›</span>
                <span class="text-secondary fw-semibold">Gas Monitoring</span>
            </div>
        </div>
    </div>

    <div class="header-actions">
        @adminManagement
            <button type="button" class="btn-action-primary" data-bs-toggle="modal" data-bs-target="#modalCreateGas">
                <i class="bi bi-plus-lg"></i> Tambah Data Gas
            </button>
        @endadminManagement
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3 py-2 px-3 small" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3 py-2 px-3 small" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- NAV TABS: Inventaris Perangkat disembunyikan dari tampilan utama sesuai instruksi (hanya menampilkan Pemantauan Gas) -->
<ul class="nav gas-nav-tabs d-none" id="gasDetektorTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tabMonitoringBtn" data-bs-toggle="tab" data-bs-target="#tabMonitoring" type="button" role="tab" aria-controls="tabMonitoring" aria-selected="true">
            <i class="bi bi-speedometer2"></i> Pemantauan Gas di Well Pad (Gas Monitoring)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tabInventoryBtn" data-bs-toggle="tab" data-bs-target="#tabInventory" type="button" role="tab" aria-controls="tabInventory" aria-selected="false">
            <i class="bi bi-broadcast-pin"></i> Inventaris Perangkat Gas Monitoring
        </button>
    </li>
</ul>

<div class="tab-content" id="gasDetektorTabContent">

    <!-- =================================================================== -->
    <!-- TAB 1: PEMANTAUAN GAS DI WELL PAD (SESUAI MOCKUP ACUAN)            -->
    <!-- =================================================================== -->
    <div class="tab-pane fade show active" id="tabMonitoring" role="tabpanel" aria-labelledby="tabMonitoringBtn">
        
        <!-- SECTION B: KARTU BATAS PEMANTAUAN (NAB CARDS) -->
        <div class="row g-3 mb-4">
            <!-- CO -->
            <div class="col-md-4">
                <div class="nab-card">
                    <div class="nab-icon-box" style="background: #f0fdf4; color: #16a34a;">
                        <i class="bi bi-cloud"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">CO</h6>
                        <div class="d-flex align-items-center gap-1">
                            <span class="text-secondary small fw-medium">NAB: <strong>{{ $nabConfig['co']['val'] }} {{ $nabConfig['co']['unit'] }}</strong></span>
                            <i class="bi bi-info-circle text-muted" style="font-size: 11px;" data-bs-toggle="tooltip" title="{{ $nabConfig['co']['desc'] }}"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- H2S -->
            <div class="col-md-4">
                <div class="nab-card">
                    <div class="nab-icon-box" style="background: #fef2f2; color: #ef4444;">
                        <i class="bi bi-radioactive"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">H₂S</h6>
                        <div class="d-flex align-items-center gap-1">
                            <span class="text-secondary small fw-medium">NAB: <strong>{{ $nabConfig['h2s']['val'] }} {{ $nabConfig['h2s']['unit'] }}</strong></span>
                            <i class="bi bi-info-circle text-muted" style="font-size: 11px;" data-bs-toggle="tooltip" title="{{ $nabConfig['h2s']['desc'] }}"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pressure -->
            <div class="col-md-4">
                <div class="nab-card">
                    <div class="nab-icon-box" style="background: #eff6ff; color: #2563eb;">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">Pressure</h6>
                        <div class="d-flex align-items-center gap-1">
                            <span class="text-secondary small fw-medium">NAB: <strong>{{ $nabConfig['pressure']['val'] }} {{ $nabConfig['pressure']['unit'] }}</strong></span>
                            <i class="bi bi-info-circle text-muted" style="font-size: 11px;" data-bs-toggle="tooltip" title="{{ $nabConfig['pressure']['desc'] }}"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION C: KARTU RINGKASAN STATISTIK (SESUAI MOCKUP 4 KOTAK) -->
        <div class="row g-3 mb-4">
            <!-- CO Tertinggi -->
            <div class="col-sm-6 col-lg-3">
                <div class="stat-card-gas">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex align-items-center gap-3">
                            <div style="font-size: 28px; color: #64748b;"><i class="bi bi-cloud"></i></div>
                            <div>
                                <span class="text-muted d-block small" style="font-size: 11.5px;">Nilai CO Tertinggi</span>
                                <div class="d-flex align-items-baseline gap-2 mt-1">
                                    <span class="fw-bold text-dark" style="font-size: 20px;">
                                        {{ $highestCo ? number_format($highestCo->co_level, 0) . ' ppm' : '-' }}
                                    </span>
                                    @if($highestCo)
                                        <span class="{{ $highestCo->co_status === 'Sesuai' ? 'badge-sesuai' : 'badge-tidaksesuai' }}">
                                            {{ $highestCo->co_status }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-muted small mt-2" style="font-size: 10.5px;">
                        @if($highestCo)
                            {{ $highestCo->measurement_date->format('d/m/Y') }} &bull; {{ $highestCo->well_pad }}
                        @else
                            Tidak ada data di periode ini
                        @endif
                    </div>
                </div>
            </div>

            <!-- H2S Tertinggi -->
            <div class="col-sm-6 col-lg-3">
                <div class="stat-card-gas">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex align-items-center gap-3">
                            <div style="font-size: 28px; color: #ef4444;"><i class="bi bi-radioactive"></i></div>
                            <div>
                                <span class="text-muted d-block small" style="font-size: 11.5px;">Nilai H₂S Tertinggi</span>
                                <div class="d-flex align-items-baseline gap-2 mt-1">
                                    <span class="fw-bold text-dark" style="font-size: 20px;">
                                        {{ $highestH2s ? number_format($highestH2s->h2s_level, 0) . ' ppm' : '-' }}
                                    </span>
                                    @if($highestH2s)
                                        <span class="{{ $highestH2s->h2s_status === 'Sesuai' ? 'badge-sesuai' : 'badge-tidaksesuai' }}">
                                            {{ $highestH2s->h2s_status }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-muted small mt-2" style="font-size: 10.5px;">
                        @if($highestH2s)
                            {{ $highestH2s->measurement_date->format('d/m/Y') }} &bull; {{ $highestH2s->well_pad }}
                        @else
                            Tidak ada data di periode ini
                        @endif
                    </div>
                </div>
            </div>

            <!-- Pressure Tertinggi -->
            <div class="col-sm-6 col-lg-3">
                <div class="stat-card-gas">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex align-items-center gap-3">
                            <div style="font-size: 28px; color: #2563eb;"><i class="bi bi-speedometer2"></i></div>
                            <div>
                                <span class="text-muted d-block small" style="font-size: 11.5px;">Pressure Tertinggi</span>
                                <div class="d-flex align-items-baseline gap-2 mt-1">
                                    <span class="fw-bold text-dark" style="font-size: 20px;">
                                        {{ $highestPressure ? number_format($highestPressure->pressure, 1) . ' barg' : '-' }}
                                    </span>
                                    @if($highestPressure)
                                        <span class="{{ $highestPressure->pressure_status === 'Sesuai' ? 'badge-sesuai' : 'badge-tidaksesuai' }}">
                                            {{ $highestPressure->pressure_status }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-muted small mt-2" style="font-size: 10.5px;">
                        @if($highestPressure)
                            {{ $highestPressure->measurement_date->format('d/m/Y') }} &bull; {{ $highestPressure->well_pad }}
                        @else
                            Tidak ada data di periode ini
                        @endif
                    </div>
                </div>
            </div>

            <!-- Total Data -->
            <div class="col-sm-6 col-lg-3">
                <div class="stat-card-gas">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex align-items-center gap-3">
                            <div style="font-size: 28px; color: #64748b;"><i class="bi bi-file-earmark-text"></i></div>
                            <div>
                                <span class="text-muted d-block small" style="font-size: 11.5px;">Total Data</span>
                                <div class="d-flex align-items-baseline gap-2 mt-1">
                                    <span class="fw-bold text-dark" style="font-size: 20px;">
                                        {{ $totalDataInMonth }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-muted small mt-2" style="font-size: 10.5px;">
                        dalam {{ $currentMonthName }}
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTER BAR INTERAKTIF SESUAI MOCKUP -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
            <div class="card-body p-3">
                <form action="{{ route('gas-detector.index') }}" method="GET" class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <!-- Pilih Bulan & Tahun -->
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted fw-semibold small" style="font-size: 12px;">Pilih Periode:</span>
                            <div class="d-flex align-items-center bg-white border rounded px-2 py-1 shadow-sm" style="height: 36px; gap: 6px;">
                                <i class="bi bi-calendar3 text-muted me-1" style="font-size: 13px;"></i>
                                <select name="month" class="form-select form-select-sm border-0 bg-transparent py-0 ps-1 pe-3 fw-semibold text-dark" style="font-size: 12px; min-width: 125px; width: 125px;" onchange="this.form.submit()">
                                    @foreach ($monthNames as $num => $name)
                                        <option value="{{ $num }}" @selected($month == $num)>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <span class="text-muted opacity-50">/</span>
                                <select name="year" class="form-select form-select-sm border-0 bg-transparent py-0 ps-1 pe-3 fw-semibold text-dark" style="font-size: 12px; min-width: 85px; width: 85px;" onchange="this.form.submit()">
                                    @for ($y = 2024; $y <= 2028; $y++)
                                        <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                                    @endfor
                                </select>
                                <div class="d-flex align-items-center border-start ps-1 ms-1 gap-1">
                                    <a href="{{ route('gas-detector.index', ['month' => $prevDate->month, 'year' => $prevDate->year, 'well_pad' => $wellPadFilter]) }}"
                                       class="btn btn-sm btn-light p-0 d-flex align-items-center justify-content-center text-secondary border rounded" style="width: 22px; height: 22px;" title="Bulan Sebelumnya">
                                        <i class="bi bi-chevron-left" style="font-size: 10px;"></i>
                                    </a>
                                    <a href="{{ route('gas-detector.index', ['month' => $nextDate->month, 'year' => $nextDate->year, 'well_pad' => $wellPadFilter]) }}"
                                       class="btn btn-sm btn-light p-0 d-flex align-items-center justify-content-center text-secondary border rounded" style="width: 22px; height: 22px;" title="Bulan Berikutnya">
                                        <i class="bi bi-chevron-right" style="font-size: 10px;"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Pilih Well Pad -->
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted fw-semibold small" style="font-size: 12px;">Pilih Well Pad</span>
                            <select name="well_pad" class="form-select form-select-sm" style="font-size: 12px; height: 34px; width: 170px;">
                                <option value="all" @selected($wellPadFilter === 'all')>Semua Well Pad</option>
                                @foreach (\App\Http\Controllers\Modules\GasDetectorController::WELL_PADS as $wp)
                                    <option value="{{ $wp }}" @selected($wellPadFilter === $wp)>{{ $wp }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn text-white btn-sm px-3 d-flex align-items-center gap-1 shadow-sm" style="background: #088c5d; height: 34px; font-size: 12px;">
                            <i class="bi bi-funnel"></i> Terapkan Filter
                        </button>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('gas-detector.index', ['month' => $month, 'year' => $year]) }}" class="btn btn-outline-secondary btn-sm px-3 d-flex align-items-center gap-1" style="height: 34px; font-size: 12px; background: white;">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </a>
                        @canExportPdf
                        <button type="button" id="btnExportPdf" class="btn text-white btn-sm px-3 d-flex align-items-center gap-1 shadow-sm" style="background: #dc2626; height: 34px; font-size: 12px;">
                            <i class="bi bi-file-earmark-pdf-fill"></i> Export PDF
                        </button>
                        @endcanExportPdf
                    </div>
                </form>
            </div>
        </div>

        <!-- TABEL DATA GAS MONITORING SESUAI MOCKUP -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
            <div class="card-header bg-white py-3 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2 border-0">
                <div>
                    <h6 class="fw-bold mb-1 text-dark" style="font-size: 14px;">Data Gas Monitoring - {{ $currentMonthName }}</h6>
                    <span class="text-muted" style="font-size: 11.5px;">Menampilkan seluruh rekaman data pemantauan gas dan pressure di well pad</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm" style="width: 220px;">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" id="gasTableSearchInput" class="form-control border-start-0 ps-0" placeholder="Cari well pad / tanggal..." style="font-size: 11.5px;">
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-gas" id="gasMainTable">
                        <thead>
                            <tr>
                                <th class="ps-3 text-center" style="width: 45px;">No</th>
                                <th>Tanggal</th>
                                <th>Well Pad</th>
                                <th class="text-center">CO (ppm)</th>
                                <th class="text-center">Status CO</th>
                                <th class="text-center">H₂S (ppm)</th>
                                <th class="text-center">Status H₂S</th>
                                <th class="text-center">Pressure (barg)</th>
                                <th class="text-center">Status Pressure</th>
                                <th>Keterangan</th>
                                <th class="text-center pe-3" style="width: 110px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($records as $item)
                                <tr>
                                    <td class="ps-3 text-center text-muted">{{ $records->firstItem() + $loop->index }}</td>
                                    <td class="text-nowrap" style="color: #334155;">{{ $item->measurement_date ? $item->measurement_date->format('d/m/Y') : '-' }}</td>
                                    <td class="fw-semibold text-dark">{{ $item->well_pad }}</td>
                                    
                                    <!-- CO -->
                                    <td class="text-center fw-semibold text-dark">{{ number_format($item->co_level, 0) }}</td>
                                    <td class="text-center">
                                        <span class="{{ $item->co_status === 'Sesuai' ? 'badge-sesuai' : 'badge-tidaksesuai' }}">
                                            {{ $item->co_status }}
                                        </span>
                                    </td>

                                    <!-- H2S -->
                                    <td class="text-center fw-semibold text-dark">{{ number_format($item->h2s_level, 0) }}</td>
                                    <td class="text-center">
                                        <span class="{{ $item->h2s_status === 'Sesuai' ? 'badge-sesuai' : 'badge-tidaksesuai' }}">
                                            {{ $item->h2s_status }}
                                        </span>
                                    </td>

                                    <!-- Pressure -->
                                    <td class="text-center fw-semibold text-dark">{{ number_format($item->pressure, 1) }}</td>
                                    <td class="text-center">
                                        <span class="{{ $item->pressure_status === 'Sesuai' ? 'badge-sesuai' : 'badge-tidaksesuai' }}">
                                            {{ $item->pressure_status }}
                                        </span>
                                    </td>

                                    <td class="text-muted" style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $item->notes ?: '-' }}
                                    </td>

                                    <!-- Aksi Sesuai Desain: Detail (Biru), Edit (Kuning), Hapus (Merah) -->
                                    <td class="text-center pe-3">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm p-1 rounded" style="color: #2563eb; background: #eff6ff;" onclick='openDetailGasModal(@json($item))' title="Lihat Detail">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            @adminManagement
                                                <button type="button" class="btn btn-sm p-1 rounded" style="color: #d97706; background: #fffbeb;" onclick='openEditGasModal(@json($item))' title="Edit Data">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form action="{{ route('gas-monitoring.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pemantauan ini?');" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm p-1 rounded" style="color: #dc2626; background: #fef2f2;" title="Hapus Data">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endadminManagement
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center py-5 text-muted">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-clipboard-x fs-1 text-secondary mb-2"></i>
                                            <p class="mb-1 fw-semibold">Belum ada data pemantauan untuk periode {{ $currentMonthName }}</p>
                                            <span class="small text-muted mb-3">Silakan klik "Tambah Data" untuk mencatat pengukuran baru.</span>
                                            @adminManagement
                                            <button type="button" class="btn text-white btn-sm px-3 shadow-sm" style="background: #088c5d;" data-bs-toggle="modal" data-bs-target="#modalCreateGas">
                                                <i class="bi bi-plus-lg me-1"></i> Tambah Data Sekarang
                                            </button>
                                            @endadminManagement
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <div class="p-3 d-flex justify-content-between align-items-center flex-wrap gap-2 border-top">
                    <span class="text-muted small" style="font-size: 11.5px;">
                        @if($records->total() > 0)
                            Menampilkan {{ $records->firstItem() }} - {{ $records->lastItem() }} dari {{ $records->total() }} data
                        @else
                            Menampilkan 0 data
                        @endif
                    </span>
                    <div>
                        {{ $records->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 8: 3 PANEL GRAFIK TERPISAH PER KATEGORI WELL PAD (SESUAI MOCKUP) -->
        <div class="row g-3 mb-4">
            <!-- Grafik 1: CO per Well Pad -->
            <div class="col-lg-4">
                <div class="chart-card-gas">
                    <h6><i class="bi bi-bar-chart-fill text-primary"></i> Grafik CO per Well Pad - {{ $currentMonthName }}</h6>
                    <div style="position: relative; height: 260px;">
                        <canvas id="chartCo"></canvas>
                    </div>
                </div>
            </div>

            <!-- Grafik 2: H2S per Well Pad -->
            <div class="col-lg-4">
                <div class="chart-card-gas">
                    <h6><i class="bi bi-activity text-info"></i> Grafik H₂S per Well Pad - {{ $currentMonthName }}</h6>
                    <div style="position: relative; height: 260px;">
                        <canvas id="chartH2s"></canvas>
                    </div>
                </div>
            </div>

            <!-- Grafik 3: Pressure per Well Pad -->
            <div class="col-lg-4">
                <div class="chart-card-gas">
                    <h6><i class="bi bi-speedometer2 text-primary"></i> Grafik Pressure per Well Pad - {{ $currentMonthName }}</h6>
                    <div style="position: relative; height: 260px;">
                        <canvas id="chartPressure"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 9: REKAPITULASI PER WELL PAD (SESUAI MOCKUP TABEL BAWAH) -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
            <div class="card-header bg-white py-3 px-3 border-0">
                <h6 class="fw-bold mb-0 text-dark" style="font-size: 14px;">Rekapitulasi per Well Pad - {{ $currentMonthName }}</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-gas">
                        <thead>
                            <tr>
                                <th rowspan="2" class="ps-3 align-middle">Well Pad</th>
                                <th rowspan="2" class="text-center align-middle">Jumlah Data</th>
                                <th colspan="2" class="text-center" style="border-bottom: 1px solid #cbd5e1;">CO (ppm)</th>
                                <th colspan="2" class="text-center" style="border-bottom: 1px solid #cbd5e1;">H₂S (ppm)</th>
                                <th colspan="2" class="text-center" style="border-bottom: 1px solid #cbd5e1;">Pressure (barg)</th>
                                <th rowspan="2" class="text-center pe-3 align-middle">Status Keseluruhan</th>
                            </tr>
                            <tr>
                                <th class="text-center" style="font-size: 11px;">Rata-rata</th>
                                <th class="text-center" style="font-size: 11px;">Maksimum</th>
                                <th class="text-center" style="font-size: 11px;">Rata-rata</th>
                                <th class="text-center" style="font-size: 11px;">Maksimum</th>
                                <th class="text-center" style="font-size: 11px;">Rata-rata</th>
                                <th class="text-center" style="font-size: 11px;">Maksimum</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rekapPerWellPad as $r)
                                <tr>
                                    <td class="ps-3 fw-semibold text-dark">{{ $r['well_pad'] }}</td>
                                    <td class="text-center text-muted">{{ $r['count'] }}</td>
                                    <td class="text-center">{{ $r['avg_co'] }}</td>
                                    <td class="text-center fw-semibold">{{ $r['max_co'] }}</td>
                                    <td class="text-center">{{ $r['avg_h2s'] }}</td>
                                    <td class="text-center fw-semibold">{{ $r['max_h2s'] }}</td>
                                    <td class="text-center">{{ $r['avg_pressure'] }}</td>
                                    <td class="text-center fw-semibold">{{ $r['max_pressure'] }}</td>
                                    <td class="text-center pe-3">
                                        <span class="badge-{{ $r['status_class'] === 'success' ? 'sesuai' : ($r['status_class'] === 'danger' ? 'tidaksesuai' : ($r['status_class'] === 'warning' ? 'warning-custom' : 'secondary-custom')) }}">
                                            {{ $r['status'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- =================================================================== -->
    <!-- TAB 2: INVENTARIS PERANGKAT GASDETEKTOR (DISEMBUNYIKAN DARI TAMPILAN) -->
    <!-- =================================================================== -->
    <div class="tab-pane fade d-none" id="tabInventory" role="tabpanel" aria-labelledby="tabInventoryBtn">
        
        <!-- 4 KARTU STATISTIK RINGKASAN PERANGKAT -->
        <div class="stats-grid mb-4">
            <!-- Total Perangkat -->
            <div class="stat-card">
                <div class="stat-icon-wrapper stat-icon-blue">
                    <i class="bi bi-broadcast-pin"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-title">Total Perangkat</div>
                    <div class="stat-number" id="statTotal">{{ $stats['total'] }}</div>
                    <div class="stat-trend"><span class="stat-trend-neutral">Perangkat terdaftar</span></div>
                </div>
            </div>

            <!-- Perangkat Tersedia -->
            <div class="stat-card">
                <div class="stat-icon-wrapper stat-icon-completed rounded-circle">
                    <i class="bi bi-check-lg"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-title">Perangkat Tersedia</div>
                    <div class="stat-number text-success" id="statAvailable">{{ $stats['available'] }}</div>
                    <div class="stat-trend"><span class="stat-trend-up">Siap digunakan operasi</span></div>
                </div>
            </div>

            <!-- Sedang Digunakan -->
            <div class="stat-card">
                <div class="stat-icon-wrapper stat-icon-ongoing rounded-circle">
                    <i class="bi bi-lightning-charge-fill"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-title">Sedang Digunakan</div>
                    <div class="stat-number text-primary" id="statInUse">{{ $stats['in_use'] }}</div>
                    <div class="stat-trend"><span class="stat-trend-neutral">Aktif di lapangan</span></div>
                </div>
            </div>

            <!-- Dalam Perawatan / Perlu Kalibrasi -->
            <div class="stat-card">
                <div class="stat-icon-wrapper stat-icon-cancelled rounded-circle">
                    <i class="bi bi-tools"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-title">Perawatan &amp; Kalibrasi</div>
                    <div class="stat-number text-danger" id="statMaintenance">{{ $stats['maintenance_or_calibration'] }}</div>
                    <div class="stat-trend"><span class="stat-trend-down">Perlu servis / kalibrasi</span></div>
                </div>
            </div>
        </div>

        <!-- FILTER BAR INVENTARIS PERANGKAT -->
        <div class="filter-card mb-4">
            <div class="filter-grid-gas">
                <div>
                    <select id="filterUnit" class="form-select form-select-filter">
                        <option value="">Semua Unit</option>
                        @foreach($units as $u)
                            <option value="{{ $u }}" {{ request('unit') === $u ? 'selected' : '' }}>{{ $u }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select id="filterStatus" class="form-select form-select-filter">
                        <option value="">Semua Status</option>
                        @foreach($statuses as $st)
                            <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select id="filterGasType" class="form-select form-select-filter">
                        <option value="">Semua Tipe Gas / Sensor</option>
                        @foreach($gasTypes as $gt)
                            <option value="{{ $gt }}" {{ request('gas_type') === $gt ? 'selected' : '' }}>{{ $gt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-input-wrap">
                    <i class="bi bi-search filter-icon-addon"></i>
                    <input type="search" id="filterSearch" class="form-control form-control-filter" 
                           value="{{ request('search') }}" placeholder="Cari kode alat, model, nomor seri, lokasi, PIC...">
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" onclick="resetFilters()">
                        Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- 3 CHARTS ROW INVENTARIS -->
        <div class="charts-row-gas">
            <div class="chart-box-card">
                <div class="chart-header">
                    <h2 class="chart-title">Perangkat per Unit Operasi</h2>
                    <p class="chart-subtitle">Distribusi penempatan gas detector di wilayah kerja</p>
                </div>
                <div class="chart-canvas-container">
                    <canvas id="unitChartCanvas"></canvas>
                </div>
            </div>
            <div class="chart-box-card">
                <div class="chart-header">
                    <h2 class="chart-title">Proporsi Status Alat</h2>
                    <p class="chart-subtitle">Kondisi ketersediaan dan status kalibrasi</p>
                </div>
                <div class="donut-layout-gas">
                    <div style="height:140px; position:relative;">
                        <canvas id="statusChartCanvas"></canvas>
                    </div>
                    <div class="custom-legend-list" id="statusLegendList">
                        @php $totalDeviceCount = max(1, $stats['total']); @endphp
                        @foreach($statusChart['labels'] as $idx => $label)
                            @php
                                $val = $statusChart['data'][$idx] ?? 0;
                                $pct = round(($val / $totalDeviceCount) * 100);
                                $col = $statusChart['colors'][$idx] ?? '#64748b';
                            @endphp
                            <div class="custom-legend-item">
                                <span class="legend-indicator" style="background:{{ $col }}"></span>
                                <span class="legend-name">{{ $label }}</span>
                                <span class="legend-stats">{{ $val }} ({{ $pct }}%)</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="chart-box-card">
                <div class="chart-header">
                    <h2 class="chart-title">Tipe Gas &amp; Sensor</h2>
                    <p class="chart-subtitle">Jumlah perangkat per deteksi gas</p>
                </div>
                <div class="chart-canvas-container">
                    <canvas id="gasTypeChartCanvas"></canvas>
                </div>
            </div>
        </div>

        <!-- TABEL INVENTARIS PERANGKAT -->
        <div class="table-card">
            <div class="table-header">
                <div>
                    <h2 class="table-title">Daftar Perangkat Gas Monitoring</h2>
                    <p class="table-subtitle">Data inventaris fisik, kondisi operasional, dan tanggal jatuh tempo kalibrasi</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark border px-2 py-1" style="font-size:11px;">
                        <span id="tableCountBadge">{{ $devices->count() }}</span> Perangkat Terdaftar
                    </span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table" id="devicesTable">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Kode Alat</th>
                            <th>Model / Merk</th>
                            <th>Nomor Seri</th>
                            <th>Tipe Gas</th>
                            <th>Unit Operasi</th>
                            <th>Status</th>
                            <th>Jadwal Kalibrasi</th>
                            <th>PIC</th>
                            <th style="width: 110px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="devicesTableBody">
                        @forelse($devices as $index => $d)
                            <tr id="deviceRow-{{ $d->id }}">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <span class="device-code-badge">{{ $d->device_code }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $d->model_name }}</div>
                                    @if($d->location)
                                        <small class="text-muted"><i class="bi bi-geo-alt"></i> {{ $d->location }}</small>
                                    @endif
                                </td>
                                <td class="text-muted font-monospace small">{{ $d->serial_number ?: '-' }}</td>
                                <td><span class="badge bg-light text-secondary border">{{ $d->gas_type ?: 'Multi-Gas' }}</span></td>
                                <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $d->unit }}</span></td>
                                <td>
                                    @php
                                        $badgeClass = match($d->status) {
                                            'Tersedia' => 'status-tersedia',
                                            'Sedang Digunakan' => 'status-inuse',
                                            'Dalam Perawatan' => 'status-maintenance',
                                            default => 'status-calibration',
                                        };
                                    @endphp
                                    <span class="status-badge-custom {{ $badgeClass }}">{{ $d->status }}</span>
                                </td>
                                <td>
                                    @if($d->next_calibration_date)
                                        @if($d->isCalibrationDue())
                                            <span class="cal-due-pill">
                                                <i class="bi bi-exclamation-triangle-fill"></i>
                                                {{ $d->next_calibration_date->format('d/m/Y') }} (Jatuh Tempo)
                                            </span>
                                        @else
                                            <span class="cal-ok-pill">
                                                <i class="bi bi-calendar-check"></i>
                                                {{ $d->next_calibration_date->format('d/m/Y') }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>{{ $d->user_in_charge ?: '-' }}</td>
                                <td style="text-align: center;">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="openDetailModal({{ $d->id }})" title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        @adminManagement
                                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="openEditModal({{ $d->id }})" title="Edit Perangkat">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteDevice({{ $d->id }}, '{{ $d->device_code }}')" title="Hapus Perangkat">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endadminManagement
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyDeviceRow">
                                <td colspan="10" class="text-center py-4 text-muted">
                                    <i class="bi bi-broadcast-pin fs-2 text-secondary mb-2 d-block"></i>
                                    Belum ada data perangkat Gas Monitoring.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- =================================================================== -->
<!-- MODALS GAS MONITORING (PENGUKURAN DI WELL PAD)                     -->
<!-- =================================================================== -->

<!-- MODAL 1: TAMBAH DATA GAS (CREATE) -->
<div class="modal fade" id="modalCreateGas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header py-3 px-4" style="background: #088c5d; color: white;">
                <h6 class="modal-title fw-bold" style="font-size: 14px;"><i class="bi bi-plus-circle me-2"></i>Tambah Data Gas Monitoring</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('gas-monitoring.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Tanggal Pengukuran *</label>
                            <input type="date" name="measurement_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Well Pad *</label>
                            <select name="well_pad" class="form-select form-select-sm" required>
                                <option value="">Pilih Well Pad</option>
                                @foreach (\App\Http\Controllers\Modules\GasDetectorController::WELL_PADS as $wp)
                                    <option value="{{ $wp }}">{{ $wp }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">CO (ppm) *</label>
                            <input type="number" step="0.01" min="0" name="co_level" class="form-control form-control-sm" placeholder="Contoh: 12" required>
                            <div class="form-text text-muted" style="font-size: 10px;">NAB: 25 ppm</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">H₂S (ppm) *</label>
                            <input type="number" step="0.01" min="0" name="h2s_level" class="form-control form-control-sm" placeholder="Contoh: 8" required>
                            <div class="form-text text-muted" style="font-size: 10px;">NAB: 10 ppm</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Pressure (barg) *</label>
                            <input type="number" step="0.01" min="0" name="pressure" class="form-control form-control-sm" placeholder="Contoh: 18.5" required>
                            <div class="form-text text-muted" style="font-size: 10px;">NAB: 20 barg</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Keterangan</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Catatan kondisi operasional..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-3 bg-light">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white btn-sm px-4" style="background: #088c5d;">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 2: EDIT DATA GAS -->
<div class="modal fade" id="modalEditGas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header py-3 px-4" style="background: #f59e0b; color: white;">
                <h6 class="modal-title fw-bold" style="font-size: 14px;"><i class="bi bi-pencil-square me-2"></i>Edit Data Gas Monitoring</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editGasForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Tanggal Pengukuran *</label>
                            <input type="date" name="measurement_date" id="editMeasurementDate" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Well Pad *</label>
                            <select name="well_pad" id="editWellPad" class="form-select form-select-sm" required>
                                @foreach (\App\Http\Controllers\Modules\GasDetectorController::WELL_PADS as $wp)
                                    <option value="{{ $wp }}">{{ $wp }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">CO (ppm) *</label>
                            <input type="number" step="0.01" min="0" name="co_level" id="editCoLevel" class="form-control form-control-sm" required>
                            <div class="form-text text-muted" style="font-size: 10px;">NAB: 25 ppm</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">H₂S (ppm) *</label>
                            <input type="number" step="0.01" min="0" name="h2s_level" id="editH2sLevel" class="form-control form-control-sm" required>
                            <div class="form-text text-muted" style="font-size: 10px;">NAB: 10 ppm</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Pressure (barg) *</label>
                            <input type="number" step="0.01" min="0" name="pressure" id="editPressure" class="form-control form-control-sm" required>
                            <div class="form-text text-muted" style="font-size: 10px;">NAB: 20 barg</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Keterangan</label>
                            <textarea name="notes" id="editNotes" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-3 bg-light">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-white btn-sm px-4">Perbarui Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 3: DETAIL DATA GAS -->
<div class="modal fade" id="modalDetailGas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header py-3 px-4" style="background: #2563eb; color: white;">
                <h6 class="modal-title fw-bold" style="font-size: 14px;"><i class="bi bi-info-circle me-2"></i>Detail Pengukuran Gas</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <table class="table table-borderless table-sm mb-0" style="font-size: 12.5px;">
                    <tr>
                        <td class="text-muted" style="width: 140px;">Tanggal Pengukuran</td>
                        <td>: <strong id="dtlTanggal">-</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Well Pad</td>
                        <td>: <span class="fw-bold text-dark" id="dtlWellPad">-</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Parameter CO</td>
                        <td>: <span id="dtlCo">-</span> <span id="dtlBadgeCo" class="ms-2"></span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Parameter H₂S</td>
                        <td>: <span id="dtlH2s">-</span> <span id="dtlBadgeH2s" class="ms-2"></span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Parameter Pressure</td>
                        <td>: <span id="dtlPressure">-</span> <span id="dtlBadgePress" class="ms-2"></span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Keterangan</td>
                        <td>: <span id="dtlNotes">-</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Petugas Pencatat</td>
                        <td>: <span id="dtlRecordedBy">-</span></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer p-2 bg-light">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- =================================================================== -->
<!-- MODALS INVENTARIS PERANGKAT (CREATE / EDIT / DETAIL)                -->
<!-- =================================================================== -->
<div class="modal fade" id="deviceFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="deviceForm" onsubmit="saveDeviceForm(event)">
                <input type="hidden" id="formDeviceId" value="">
                <div class="modal-header border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="header-icon-box teal" style="width:34px; height:34px; font-size:16px;">
                            <i class="bi bi-broadcast-pin"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold" id="deviceFormModalTitle">Tambah Perangkat Gas Monitoring</h5>
                            <div class="text-muted" style="font-size:11px;">Kelola data teknis perangkat deteksi gas keselamatan</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:12px;">Kode Alat <span class="text-danger">*</span></label>
                            <input type="text" id="formDeviceCode" class="form-control form-control-sm" placeholder="Contoh: GD-DIENG-01" required>
                            <div class="form-text" style="font-size:10.5px;" id="deviceCodeFeedback">Kode unik perangkat</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:12px;">Model / Merk Alat <span class="text-danger">*</span></label>
                            <input type="text" id="formModelName" class="form-control form-control-sm" placeholder="Contoh: Honeywell BW Clip4" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:12px;">Nomor Seri (Serial Number)</label>
                            <input type="text" id="formSerialNumber" class="form-control form-control-sm" placeholder="Contoh: SN-883921">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:12px;">Tipe Gas / Sensor</label>
                            <input type="text" id="formGasType" class="form-control form-control-sm" placeholder="Contoh: Multi-Gas (H2S, CO, O2, LEL)" list="gasTypeListOptions">
                            <datalist id="gasTypeListOptions">
                                @foreach($gasTypes as $gt)
                                    <option value="{{ $gt }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:12px;">Unit Operasi <span class="text-danger">*</span></label>
                            <select id="formUnit" class="form-select form-select-sm" required>
                                @foreach($units as $u)
                                    <option value="{{ $u }}">{{ $u }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:12px;">Lokasi Penyimpanan</label>
                            <input type="text" id="formLocation" class="form-control form-control-sm" placeholder="Contoh: Lemari APD Control Room Unit 1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:12px;">Status Operasional <span class="text-danger">*</span></label>
                            <select id="formStatus" class="form-select form-select-sm" required>
                                <option value="Tersedia">Tersedia</option>
                                <option value="Sedang Digunakan">Sedang Digunakan</option>
                                <option value="Dalam Perawatan">Dalam Perawatan</option>
                                <option value="Perlu Kalibrasi">Perlu Kalibrasi</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:12px;">Tanggal Kalibrasi Terakhir</label>
                            <input type="date" id="formLastCalDate" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:12px;">Tanggal Kalibrasi Berikutnya</label>
                            <input type="date" id="formNextCalDate" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:12px;">Penanggung Jawab / Pemegang Alat</label>
                            <input type="text" id="formUserInCharge" class="form-control form-control-sm" placeholder="Nama staf / operator">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:12px;">Catatan Tambahan</label>
                            <textarea id="formNotes" class="form-control form-control-sm" rows="2" placeholder="Kondisi fisik, riwayat sensor..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-action-primary btn-sm px-4" id="btnSubmitDevice">
                        <i class="bi bi-save me-1"></i> Simpan Perangkat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL DETAIL INVENTARIS PERANGKAT -->
<div class="modal fade" id="deviceDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="header-icon-box teal" style="width:34px; height:34px; font-size:16px;">
                        <i class="bi bi-info-circle"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold" id="detailDeviceTitle">Detail Perangkat Gas Monitoring</h5>
                        <div class="text-muted" style="font-size:11px;" id="detailDeviceSubtitle">Informasi spesifikasi dan riwayat audit aktivitas</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded border">
                            <h6 class="fw-bold mb-3 text-dark border-bottom pb-2">Spesifikasi Alat</h6>
                            <table class="table table-borderless table-sm mb-0 small">
                                <tr>
                                    <td class="text-muted" style="width:140px;">Kode Perangkat</td>
                                    <td>: <span class="device-code-badge" id="dtlDeviceCode">-</span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Model / Merk</td>
                                    <td>: <strong id="dtlModelName">-</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Nomor Seri</td>
                                    <td>: <span id="dtlSerialNumber" class="font-monospace">-</span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Sensor Gas</td>
                                    <td>: <span id="dtlGasType">-</span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Unit Operasi</td>
                                    <td>: <span id="dtlUnit">-</span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Lokasi Simpan</td>
                                    <td>: <span id="dtlLocation">-</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded border">
                            <h6 class="fw-bold mb-3 text-dark border-bottom pb-2">Status &amp; Kalibrasi</h6>
                            <table class="table table-borderless table-sm mb-0 small">
                                <tr>
                                    <td class="text-muted" style="width:140px;">Status Alat</td>
                                    <td>: <span id="dtlStatusBadge">-</span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Terakhir Kalibrasi</td>
                                    <td>: <span id="dtlLastCal">-</span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Kalibrasi Berikutnya</td>
                                    <td>: <span id="dtlNextCal">-</span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Kondisi Kalibrasi</td>
                                    <td>: <span id="dtlCalCondition">-</span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Penanggung Jawab</td>
                                    <td>: <span id="dtlUserInCharge">-</span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Catatan</td>
                                    <td>: <span id="dtlNotes">-</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold text-dark mb-2">Riwayat Audit &amp; Perubahan Data</h6>
                <div class="table-responsive border rounded" style="max-height: 220px;">
                    <table class="table table-sm table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Waktu</th>
                                <th>Pengguna</th>
                                <th>Aksi</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="detailActivityLogsBody">
                            <tr>
                                <td colspan="4" class="text-center py-3 text-muted">Memuat riwayat log...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top bg-light">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const CSRF_TOKEN = '{{ csrf_token() }}';
    const GD_BASE_URL = '{{ url("/gas-detector") }}';

    // =========================================================================
    // 1. GAS MONITORING SCRIPTS (SESUAI MOCKUP ACUAN)
    // =========================================================================

    // Ekspor PDF Terpadu: mengunduh seluruh data pemantauan sesuai filter aktif
    document.getElementById('btnExportPdf')?.addEventListener('click', function () {
        const month = document.querySelector('select[name="month"]')?.value || '{{ $month }}';
        const year = document.querySelector('select[name="year"]')?.value || '{{ $year }}';
        const wellPad = document.querySelector('select[name="well_pad"]')?.value || '{{ $wellPadFilter }}';
        const exportUrl = `{{ route('gas-monitoring.export') }}?month=${encodeURIComponent(month)}&year=${encodeURIComponent(year)}&well_pad=${encodeURIComponent(wellPad)}`;
        window.location.href = exportUrl;
    });

    // Client-side Quick Search untuk tabel Gas Monitoring
    document.getElementById('gasTableSearchInput')?.addEventListener('keyup', function() {
        const val = this.value.toLowerCase();
        const rows = document.querySelectorAll('#gasMainTable tbody tr');
        rows.forEach(r => {
            r.style.display = r.innerText.toLowerCase().includes(val) ? '' : 'none';
        });
    });

    // Inisialisasi 3 Grafik Garis Chart.js Gas Monitoring
    const chartData = @json($chartData);
    const labels = chartData.labels;

    function makeDatasets(paramKey) {
        return chartData.series.map(s => ({
            label: s.short,
            data: s[paramKey],
            borderColor: s.color,
            backgroundColor: s.color,
            borderWidth: 1.8,
            tension: 0.2,
            pointRadius: 2.5,
            pointHoverRadius: 4,
            fill: false
        }));
    }

    // Plugin Garis Batas NAB
    function nabLinePlugin(nabVal, labelText) {
        return {
            id: 'nabLine',
            afterDraw(chart) {
                const {ctx, chartArea: {left, right}, scales: {y}} = chart;
                if (!y || isNaN(y.getPixelForValue(nabVal))) return;
                const yPos = y.getPixelForValue(nabVal);

                ctx.save();
                ctx.setLineDash([5, 5]);
                ctx.strokeStyle = '#ef4444';
                ctx.lineWidth = 1.5;
                ctx.beginPath();
                ctx.moveTo(left, yPos);
                ctx.lineTo(right, yPos);
                ctx.stroke();

                ctx.fillStyle = '#dc2626';
                ctx.font = 'bold 10px Inter, sans-serif';
                ctx.textAlign = 'right';
                ctx.fillText(labelText, right, yPos - 5);
                ctx.restore();
            }
        };
    }

    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 8,
                    usePointStyle: true,
                    pointStyle: 'circle',
                    font: { size: 10 }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return `${context.dataset.label}: ${context.parsed.y}`;
                    }
                }
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { font: { size: 9 }, maxTicksLimit: 12 }
            },
            y: {
                beginAtZero: true,
                grid: { color: '#f1f5f9' },
                ticks: { font: { size: 9 } }
            }
        }
    };

    if (typeof Chart !== 'undefined') {
        const ctxCo = document.getElementById('chartCo')?.getContext('2d');
        if (ctxCo) {
            new Chart(ctxCo, {
                type: 'line',
                data: { labels: labels, datasets: makeDatasets('co') },
                options: commonOptions,
                plugins: [nabLinePlugin(chartData.nabCo, `NAB ${chartData.nabCo} ppm`)]
            });
        }

        const ctxH2s = document.getElementById('chartH2s')?.getContext('2d');
        if (ctxH2s) {
            new Chart(ctxH2s, {
                type: 'line',
                data: { labels: labels, datasets: makeDatasets('h2s') },
                options: commonOptions,
                plugins: [nabLinePlugin(chartData.nabH2s, `NAB ${chartData.nabH2s} ppm`)]
            });
        }

        const ctxPressure = document.getElementById('chartPressure')?.getContext('2d');
        if (ctxPressure) {
            new Chart(ctxPressure, {
                type: 'line',
                data: { labels: labels, datasets: makeDatasets('pressure') },
                options: commonOptions,
                plugins: [nabLinePlugin(chartData.nabPressure, `NAB ${chartData.nabPressure} barg`)]
            });
        }
    }

    // =========================================================================
    // 2. INVENTARIS PERANGKAT SCRIPTS (FUNGSI ASLI YANG DIPERTAHANKAN)
    // =========================================================================

    const unitChartData = @json($unitChart);
    const statusChartData = @json($statusChart);
    const gasTypeChartData = @json($gasTypeChart);

    if (typeof Chart !== 'undefined') {
        // Chart Unit
        const unitCtx = document.getElementById('unitChartCanvas')?.getContext('2d');
        if (unitCtx) {
            new Chart(unitCtx, {
                type: 'bar',
                data: {
                    labels: unitChartData.labels,
                    datasets: [{
                        label: 'Jumlah Perangkat',
                        data: unitChartData.data,
                        backgroundColor: '#0d9488',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } } },
                        x: { ticks: { font: { size: 10 } } }
                    }
                }
            });
        }

        // Chart Status Donut
        const statusCtx = document.getElementById('statusChartCanvas')?.getContext('2d');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: statusChartData.labels,
                    datasets: [{
                        data: statusChartData.data,
                        backgroundColor: statusChartData.colors,
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: { legend: { display: false } }
                }
            });
        }

        // Chart Gas Type
        const gasTypeCtx = document.getElementById('gasTypeChartCanvas')?.getContext('2d');
        if (gasTypeCtx) {
            new Chart(gasTypeCtx, {
                type: 'bar',
                data: {
                    labels: gasTypeChartData.labels,
                    datasets: [{
                        label: 'Jumlah',
                        data: gasTypeChartData.data,
                        backgroundColor: '#3b82f6',
                        borderRadius: 4
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } } },
                        y: { ticks: { font: { size: 9.5 } } }
                    }
                }
            });
        }
    }
});

// Helper Modal Edit Gas
function openEditGasModal(item) {
    if (!item) return;
    const form = document.getElementById('editGasForm');
    form.action = `/gas-monitoring/${item.id}`;

    document.getElementById('editMeasurementDate').value = item.measurement_date ? item.measurement_date.substring(0, 10) : '';
    document.getElementById('editWellPad').value = item.well_pad || '';
    document.getElementById('editCoLevel').value = item.co_level || 0;
    document.getElementById('editH2sLevel').value = item.h2s_level || 0;
    document.getElementById('editPressure').value = item.pressure || 0;
    document.getElementById('editNotes').value = item.notes || '';

    const modal = new bootstrap.Modal(document.getElementById('modalEditGas'));
    modal.show();
}

// Helper Modal Detail Gas
function openDetailGasModal(item) {
    if (!item) return;
    document.getElementById('dtlTanggal').innerText = item.measurement_date ? new Date(item.measurement_date).toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'}) : '-';
    document.getElementById('dtlWellPad').innerText = item.well_pad || '-';
    document.getElementById('dtlCo').innerText = (item.co_level || 0) + ' ppm';
    document.getElementById('dtlH2s').innerText = (item.h2s_level || 0) + ' ppm';
    document.getElementById('dtlPressure').innerText = (item.pressure || 0) + ' barg';
    document.getElementById('dtlNotes').innerText = item.notes || '-';
    document.getElementById('dtlRecordedBy').innerText = item.recorded_by || 'Petugas HSSE Dieng';

    const coPass = parseFloat(item.co_level) <= 25.0;
    const h2sPass = parseFloat(item.h2s_level) <= 10.0;
    const pressPass = parseFloat(item.pressure) <= 20.0;

    document.getElementById('dtlBadgeCo').className = coPass ? 'badge-sesuai' : 'badge-tidaksesuai';
    document.getElementById('dtlBadgeCo').innerText = coPass ? 'Sesuai' : 'Tidak Sesuai';

    document.getElementById('dtlBadgeH2s').className = h2sPass ? 'badge-sesuai' : 'badge-tidaksesuai';
    document.getElementById('dtlBadgeH2s').innerText = h2sPass ? 'Sesuai' : 'Tidak Sesuai';

    document.getElementById('dtlBadgePress').className = pressPass ? 'badge-sesuai' : 'badge-tidaksesuai';
    document.getElementById('dtlBadgePress').innerText = pressPass ? 'Sesuai' : 'Tidak Sesuai';

    const modal = new bootstrap.Modal(document.getElementById('modalDetailGas'));
    modal.show();
}

// Helper Modal Inventaris Perangkat (Device)
function openCreateModal() {
    const form = document.getElementById('deviceForm');
    if (form) form.reset();
    document.getElementById('formDeviceId').value = '';
    document.getElementById('deviceFormModalTitle').textContent = 'Tambah Perangkat Gas Monitoring';
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('deviceFormModal'));
    modal.show();
}

async function openEditModal(id) {
    try {
        const res = await fetch(`/gas-detector/${id}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        if (!data.success) {
            Swal.fire('Error', data.message || 'Data tidak ditemukan.', 'error');
            return;
        }

        const d = data.device;
        document.getElementById('formDeviceId').value = d.id;
        document.getElementById('formDeviceCode').value = d.device_code || '';
        document.getElementById('formModelName').value = d.model_name || '';
        document.getElementById('formSerialNumber').value = d.serial_number || '';
        document.getElementById('formGasType').value = d.gas_type || '';
        document.getElementById('formUnit').value = d.unit || 'Dieng Unit 1';
        document.getElementById('formLocation').value = d.location || '';
        document.getElementById('formStatus').value = d.status || 'Tersedia';
        document.getElementById('formLastCalDate').value = d.last_calibration_date ? String(d.last_calibration_date).substring(0, 10) : '';
        document.getElementById('formNextCalDate').value = d.next_calibration_date ? String(d.next_calibration_date).substring(0, 10) : '';
        document.getElementById('formUserInCharge').value = d.user_in_charge || '';
        document.getElementById('formNotes').value = d.notes || '';
        document.getElementById('deviceFormModalTitle').textContent = `Edit Perangkat [${d.device_code}]`;

        bootstrap.Modal.getOrCreateInstance(document.getElementById('deviceFormModal')).show();
    } catch (err) {
        Swal.fire('Error', 'Gagal memuat data perangkat.', 'error');
    }
}

async function openDetailModal(id) {
    try {
        const res = await fetch(`/gas-detector/${id}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        if (!data.success) {
            Swal.fire('Error', data.message || 'Data tidak ditemukan.', 'error');
            return;
        }

        const d = data.device;
        document.getElementById('detailDeviceTitle').textContent = `${d.model_name} [${d.device_code}]`;
        document.getElementById('dtlDeviceCode').textContent = d.device_code || '-';
        document.getElementById('dtlModelName').textContent = d.model_name || '-';
        document.getElementById('dtlSerialNumber').textContent = d.serial_number || '-';
        document.getElementById('dtlGasType').textContent = d.gas_type || 'Multi-Gas';
        document.getElementById('dtlUnit').textContent = d.unit || '-';
        document.getElementById('dtlLocation').textContent = d.location || '-';
        document.getElementById('dtlStatusBadge').innerHTML = `<span class="status-badge-custom status-${d.status ? d.status.toLowerCase().replace(' ', '') : 'tersedia'}">${d.status}</span>`;
        document.getElementById('dtlLastCal').textContent = d.last_calibration_date ? new Date(d.last_calibration_date).toLocaleDateString('id-ID') : '-';
        document.getElementById('dtlNextCal').textContent = d.next_calibration_date ? new Date(d.next_calibration_date).toLocaleDateString('id-ID') : '-';
        document.getElementById('dtlCalCondition').textContent = d.is_calibration_due ? 'Jatuh Tempo (Perlu Kalibrasi Segera)' : 'Normal';
        document.getElementById('dtlUserInCharge').textContent = d.user_in_charge || '-';
        document.getElementById('dtlNotes').textContent = d.notes || '-';

        const tbody = document.getElementById('detailActivityLogsBody');
        tbody.innerHTML = '';
        if (data.activity_logs && data.activity_logs.length > 0) {
            data.activity_logs.forEach(log => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="text-nowrap text-muted">${new Date(log.created_at).toLocaleString('id-ID')}</td>
                    <td class="fw-semibold">${log.user_name || '-'}</td>
                    <td><span class="badge bg-secondary">${log.action || '-'}</span></td>
                    <td>${log.description || '-'}</td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-2 text-muted">Belum ada catatan aktivitas untuk alat ini.</td></tr>';
        }

        bootstrap.Modal.getOrCreateInstance(document.getElementById('deviceDetailModal')).show();
    } catch (err) {
        Swal.fire('Error', 'Gagal memuat detail perangkat.', 'error');
    }
}

async function saveDeviceForm(e) {
    e.preventDefault();
    const id = document.getElementById('formDeviceId').value;
    const isEdit = Boolean(id);
    const url = isEdit ? `/gas-detector/${id}` : '/gas-detector';
    const method = isEdit ? 'PUT' : 'POST';

    const payload = {
        device_code: document.getElementById('formDeviceCode').value,
        model_name: document.getElementById('formModelName').value,
        serial_number: document.getElementById('formSerialNumber').value,
        gas_type: document.getElementById('formGasType').value,
        unit: document.getElementById('formUnit').value,
        location: document.getElementById('formLocation').value,
        status: document.getElementById('formStatus').value,
        last_calibration_date: document.getElementById('formLastCalDate').value || null,
        next_calibration_date: document.getElementById('formNextCalDate').value || null,
        user_in_charge: document.getElementById('formUserInCharge').value,
        notes: document.getElementById('formNotes').value,
    };

    const btn = document.getElementById('btnSubmitDevice');
    btn.disabled = true;

    try {
        const res = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const body = await res.json();
        btn.disabled = false;

        if (!res.ok || !body.success) {
            let msg = body.message || 'Gagal menyimpan perangkat.';
            if (body.errors) {
                msg = Object.values(body.errors).flat().join('<br>');
            }
            Swal.fire({ icon: 'error', title: 'Validasi Gagal', html: msg });
            return;
        }

        bootstrap.Modal.getInstance(document.getElementById('deviceFormModal')).hide();
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: body.message,
            timer: 1500,
            showConfirmButton: false
        }).then(() => {
            window.location.reload();
        });
    } catch (err) {
        btn.disabled = false;
        Swal.fire('Error', 'Terjadi kesalahan sistem saat menyimpan perangkat.', 'error');
    }
}

function deleteDevice(id, code) {
    Swal.fire({
        title: 'Hapus Perangkat?',
        text: `Apakah Anda yakin ingin menghapus data perangkat [${code}]?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const res = await fetch(`/gas-detector/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Terhapus!', text: data.message, timer: 1200, showConfirmButton: false })
                        .then(() => window.location.reload());
                } else {
                    Swal.fire('Error', data.message || 'Gagal menghapus perangkat.', 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'Terjadi kesalahan sistem saat menghapus perangkat.', 'error');
            }
        }
    });
}

function resetFilters() {
    window.location.href = "{{ route('gas-detector.index') }}";
}
</script>
@endpush
