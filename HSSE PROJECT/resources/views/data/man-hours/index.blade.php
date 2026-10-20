@extends('layouts.app', [
    'title' => 'Man Hours - Rekapitulasi Jam Kerja Aman',
    'activeModule' => 'man-hours'
])

@push('styles')
<style>
    .filter-grid-hsse {
        grid-template-columns: 140px 140px 180px 145px 135px 1fr auto;
    }

    .stats-grid-hsse {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 14px;
    }

    .charts-row-hsse {
        display: grid;
        grid-template-columns: 1.25fr 1fr 1fr;
        gap: 14px;
        margin-bottom: 14px;
    }

    .donut-layout-hsse {
        display: grid;
        grid-template-columns: 46% 54%;
        align-items: center;
        height: 175px;
        gap: 8px;
    }

    .wf-code-badge {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-weight: 700;
        background: #eff6ff;
        color: #1d4ed8;
        padding: 2px 7px;
        border-radius: 4px;
        border: 1px solid #bfdbfe;
        font-size: 10.5px;
        display: inline-block;
    }

    .cat-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 9px;
        border-radius: 5px;
        font-size: 11px;
        font-weight: 600;
        border: 1px solid transparent;
    }

    .cat-gde { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
    .cat-kodipa { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .cat-utility { background: #f0fdfa; color: #0f766e; border-color: #99f6e4; }
    .cat-security { background: #fffbeb; color: #b45309; border-color: #fde68a; }
    .cat-contractor { background: #f5f3ff; color: #6d28d9; border-color: #ddd6fe; }
    .cat-kp { background: #fdf2f8; color: #be185d; border-color: #fbcfe8; }
    .cat-default { background: #f1f5f9; color: #334155; border-color: #cbd5e1; }

    .mh-total-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
        padding: 3px 9px;
        border-radius: 5px;
        font-size: 11.5px;
        font-weight: 700;
    }

    .mh-formula-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #f8fafc;
        color: #475569;
        border: 1px solid #e2e8f0;
        padding: 2px 7px;
        border-radius: 4px;
        font-size: 10.5px;
        font-weight: 600;
    }

    .source-info-banner {
        background: linear-gradient(135deg, #f0fdf4 0%, #eff6ff 100%);
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        padding: 10px 16px;
        margin-bottom: 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .calc-preview-card {
        background: linear-gradient(135deg, #f0fdf4 0%, #eff6ff 100%);
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        padding: 12px 16px;
    }

    .detail-grid-mh {
        display: grid;
        grid-template-columns: 165px 12px 1fr;
        gap: 8px 0;
        font-size: 12px;
    }

    .detail-label-mh {
        color: #64748b;
        font-weight: 600;
    }

    .empty-state-mh {
        padding: 44px 20px;
        text-align: center;
    }

    .empty-state-mh-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #f1f5f9;
        color: #94a3b8;
        display: grid;
        place-items: center;
        font-size: 26px;
        margin: 0 auto 12px;
    }

    @media (max-width: 1200px) {
        .stats-grid-hsse { grid-template-columns: repeat(3, 1fr); }
        .charts-row-hsse { grid-template-columns: 1fr; }
        .filter-grid-hsse { grid-template-columns: 1fr 1fr; }
    }

    @media (max-width: 768px) {
        .stats-grid-hsse { grid-template-columns: 1fr; }
        .filter-grid-hsse { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
@php
    $isAdmin = auth()->user()?->isAdminManagement() ?? false;
@endphp

<!-- HEADER & ACTIONS (Referensi HSSE Plan) -->
<div class="page-header">
    <div class="header-title-wrapper">
        <div class="header-icon-box green">
            <i class="bi bi-clock-history"></i>
        </div>
        <div>
            <h1 class="page-heading">Man Hours <span class="ms-2 fs-6 fw-semibold text-secondary">Rekapitulasi Jam Kerja Aman</span></h1>
            <div class="breadcrumb-nav">
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <span class="mx-1 text-muted">›</span>
                <span class="text-secondary">Data</span>
                <span class="mx-1 text-muted">›</span>
                <span class="text-secondary fw-semibold">Man Hours</span>
            </div>
        </div>
    </div>

    <div class="header-actions">
        <a href="{{ route('workforce.index') }}" class="btn btn-action-outline" title="Kelola Sumber Jumlah Pekerja pada Menu Manpower">
            <i class="bi bi-people-fill me-1 text-success"></i> Kelola Sumber Manpower
        </a>
        @canExportPdf
        <a href="{{ route('man-hours.export', request()->query()) }}" id="btnExportManHours" class="btn-action-outline">
            <i class="bi bi-file-earmark-pdf-fill text-danger"></i> Export PDF
        </a>
        @endcanExportPdf
    </div>
</div>

<!-- BANNER INTEGRASI OTOMATIS MANPOWER -> MAN HOURS -->
<div class="source-info-banner">
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success"><i class="bi bi-link-45deg"></i> Otomatis dari Manpower</span>
        <span class="small text-dark">
            Jumlah pekerja diambil langsung dari menu <strong>Manpower</strong>. Perhitungan otomatis memakai rumus:
            <strong class="text-success">Man Hours Unit = Jumlah Pekerja Unit &times; Jam Kerja per Orang</strong> (Default: 8 Jam/Orang/Hari).
        </span>
    </div>
    <a href="{{ route('workforce.index') }}" class="btn btn-sm btn-outline-success py-1 px-2" style="font-size: 11px;">
        <i class="bi bi-plus-circle me-1"></i> Input Pekerja di Workforce
    </a>
</div>

<!-- BANNER DETEKSI DAN KETERANGAN PENAMBAHAN UNIT BARU -->
@if(!empty($unitExpansionInfo['has_new_units']))
<div class="alert alert-warning border-warning-subtle py-2 px-3 mb-3 d-flex flex-wrap justify-content-between align-items-center gap-2" id="unitExpansionBanner" style="border-radius: 8px;">
    <div class="d-flex align-items-start gap-2">
        <i class="bi bi-building-add text-warning-emphasis fs-5 mt-1"></i>
        <div>
            <div class="fw-bold text-dark" style="font-size: 12.5px;">
                Informasi Penambahan Unit / Area Operasi Baru pada Perhitungan Man Hours
                <span class="badge bg-warning text-dark ms-1">+{{ $unitExpansionInfo['new_units_count'] }} Unit Baru</span>
            </div>
            <div class="text-secondary" style="font-size: 11.5px;">
                {{ $unitExpansionInfo['summary_message'] }}
                Unit baru disertakan dalam perhitungan Man Hours mulai tanggal pencatatannya di database (tanpa mengubah atau memalsukan angka pada tanggal historis sebelumnya).
            </div>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-1">
        @foreach($unitExpansionInfo['new_units'] as $newU)
            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-2 py-1" style="font-size: 10.5px;">
                <i class="bi bi-stars me-1"></i> {{ $newU['name'] }} (Ditambahkan: {{ $newU['created_at_formatted'] }})
            </span>
        @endforeach
    </div>
</div>
@endif

<div id="manHourAlertBox"></div>

<!-- FILTER BAR (Referensi HSSE Plan) -->
<div class="filter-card">
    <div class="filter-grid filter-grid-hsse">
        <!-- Tanggal Mulai -->
        <div class="filter-input-wrap">
            <i class="bi bi-calendar3 filter-icon-addon"></i>
            <input type="date" id="filterStart" class="form-control form-control-filter" value="{{ request('start_date') }}" title="Tanggal Mulai">
        </div>

        <!-- Tanggal Selesai -->
        <div class="filter-input-wrap">
            <i class="bi bi-calendar3 filter-icon-addon"></i>
            <input type="date" id="filterEnd" class="form-control form-control-filter" value="{{ request('end_date') }}" title="Tanggal Selesai">
        </div>

        <!-- Kategori Pekerja -->
        <div>
            <select id="filterCategory" class="form-select form-select-filter">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </div>

        <!-- Unit Kerja -->
        <div>
            <select id="filterUnit" class="form-select form-select-filter">
                <option value="">Semua Unit</option>
                @foreach($units as $u)
                    <option value="{{ $u }}" {{ request('unit') === $u ? 'selected' : '' }}>{{ $u }}</option>
                @endforeach
            </select>
        </div>

        <!-- Periode Bulan/Tahun -->
        <div>
            <input type="month" id="filterPeriod" class="form-control form-control-filter px-2" value="{{ request('period') }}" title="Filter Bulan & Tahun">
        </div>

        <!-- Pencarian -->
        <div class="filter-input-wrap">
            <i class="bi bi-search filter-icon-addon"></i>
            <input type="search" id="filterSearch" class="form-control form-control-filter" value="{{ request('search') }}" placeholder="Cari kategori, kode sumber, unit, catatan...">
        </div>

        <!-- Reset -->
        <div>
            <button type="button" class="btn btn-sm btn-outline-secondary px-3" onclick="resetMhFilters()">
                Reset
            </button>
        </div>
    </div>
</div>

<!-- KARTU RINGKASAN (5 STAT CARDS SERAGAM HSSE PLAN) -->
<div class="stats-grid-hsse">
    <!-- 1. Total Akumulasi Man Hours (Periode) -->
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-completed rounded-circle">
            <i class="bi bi-clock-history"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Total Akumulasi Man Hours</div>
            <div class="stat-number text-success" id="statTotalManHours">{{ number_format((float) $stats['total_man_hours'], 0, ',', '.') }}</div>
            <div class="stat-trend">
                <span class="stat-trend-up">Σ {{ $stats['distinct_days_count'] ?? 0 }} hari • Kumulatif: {{ number_format((float) $stats['cumulative_man_hours'], 0, ',', '.') }} Jam</span>
            </div>
        </div>
    </div>

    <!-- 2. Jumlah Pekerja Sumber -->
    <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='{{ route('workforce.index') }}'" title="Lihat Sumber Data Pekerja di Menu Workforce">
        <div class="stat-icon-wrapper stat-icon-blue">
            <i class="bi bi-people-fill"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Jumlah Pekerja Sumber</div>
            <div class="stat-number text-primary" id="statTotalWorkers">{{ number_format((int) $stats['total_workers'], 0, ',', '.') }}</div>
            <div class="stat-trend"><span class="stat-trend-neutral">Dari {{ $stats['total_records'] }} data Workforce</span></div>
        </div>
    </div>

    <!-- 3. Total Man Hours Harian (Tanggal Terbaru) -->
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-ongoing rounded-circle">
            <i class="bi bi-calendar-check"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Man Hours Harian (Terbaru)</div>
            <div class="stat-number" id="statDailyManHours">{{ number_format((float) $stats['daily_man_hours'], 0, ',', '.') }}</div>
            <div class="stat-trend"><span class="stat-trend-neutral">Tanggal terbaru: {{ $stats['reference_date'] }} ({{ number_format((int) ($stats['daily_workers'] ?? 0), 0, ',', '.') }} org)</span></div>
        </div>
    </div>

    <!-- 4. Jumlah Unit Aktif & Penambahan Unit -->
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-blue">
            <i class="bi bi-building-check"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Jumlah Unit Aktif</div>
            <div class="stat-number text-primary" id="statActiveUnitsCount">
                {{ $stats['active_units_count'] ?? count($units) }} <span class="fs-6 fw-semibold text-secondary">Unit</span>
            </div>
            <div class="stat-trend">
                @if(($stats['new_units_count'] ?? 0) > 0)
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size: 10px;">
                        +{{ $stats['new_units_count'] }} unit baru dari {{ $unitExpansionInfo['initial_units_count'] }} unit awal
                    </span>
                @else
                    <span class="stat-trend-caption">Seluruh unit operasi aktif</span>
                @endif
            </div>
        </div>
    </div>

    <!-- 5. Rata-rata Man Hours Harian -->
    <div class="stat-card">
        <div class="stat-icon-wrapper stat-icon-total">
            <i class="bi bi-graph-up-arrow"></i>
        </div>
        <div class="stat-body">
            <div class="stat-title">Rata-rata Harian</div>
            <div class="stat-number" id="statAverageDailyManHours">{{ number_format((float) ($stats['average_daily_man_hours'] ?? 0), 0, ',', '.') }}</div>
            <div class="stat-trend"><span class="stat-trend-up">Bulan ini: {{ number_format((float) $stats['monthly_man_hours'], 0, ',', '.') }} Jam</span></div>
        </div>
    </div>
</div>

<!-- CHARTS ROW (3 CHARTS SERAGAM HSSE PLAN) -->
<div class="charts-row-hsse">
    <!-- Chart 1: Tren Man Hours per Tanggal -->
    <div class="chart-box-card">
        <div class="chart-header">
            <h2 class="chart-title">Tren Man Hours Harian</h2>
            <p class="chart-subtitle">Perkembangan jam kerja aman harian berdasarkan tanggal pencatatan Workforce</p>
        </div>
        <div class="chart-canvas-container">
            <canvas id="mhTrendChartCanvas"></canvas>
        </div>
    </div>

    <!-- Chart 2: Distribusi Man Hours per Kategori (Donut + Legend) -->
    <div class="chart-box-card">
        <div class="chart-header">
            <h2 class="chart-title">Distribusi Man Hours per Kategori / Unit</h2>
            <p class="chart-subtitle">Proporsi jam kerja per komponen dan unit area operasi</p>
        </div>
        <div class="donut-layout-hsse">
            <div style="height:140px; position:relative;">
                <canvas id="mhCategoryChartCanvas"></canvas>
            </div>
            <div class="custom-legend-list" id="mhCategoryLegendList">
                @php
                    $totalHoursForPct = max(1, (float) $stats['total_man_hours']);
                @endphp
                @foreach($categoryChart['labels'] as $idx => $catLabel)
                    @php
                        $val = (float) ($categoryChart['data'][$idx] ?? 0);
                        $pct = $stats['total_man_hours'] > 0 ? round(($val / $totalHoursForPct) * 100) : 0;
                        $color = $categoryChart['colors'][$idx] ?? '#64748b';
                    @endphp
                    <div class="custom-legend-item">
                        <span class="legend-indicator" style="background:{{ $color }}"></span>
                        <span class="legend-name" title="{{ $catLabel }}">{{ $catLabel }}</span>
                        <span class="legend-stats">{{ number_format($val, 0, ',', '.') }} ({{ $pct }}%)</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Chart 3: Perbandingan Man Hours per Unit / Area Operasi -->
    <div class="chart-box-card">
        <div class="chart-header">
            <h2 class="chart-title">Perbandingan per Unit / Area Operasi</h2>
            <p class="chart-subtitle">Akumulasi jam kerja aman pada setiap unit operasi (termasuk unit baru)</p>
        </div>
        <div class="chart-canvas-container">
            <canvas id="mhMonthlyChartCanvas"></canvas>
        </div>
    </div>
</div>

<!-- TABEL REKAP PERHITUNGAN MAN HOURS PER UNIT / AREA OPERASI -->
@if(!empty($unitRecap['rows']))
<div class="table-card mb-3">
    <div class="table-topbar">
        <div>
            <h2 class="table-section-title mb-0">
                <i class="bi bi-building-check text-success me-1"></i> Rekap Perhitungan Man Hours per Unit / Area Operasi
                <span class="badge bg-success-subtle text-success border border-success-subtle ms-2" style="font-size:10.5px;">
                    Man Hours Unit = Jumlah Pekerja Unit × Jam Kerja/Orang
                </span>
            </h2>
        </div>
        <div class="text-muted small">
            Tanggal Acuan Terbaru: <strong class="text-dark">{{ $unitRecap['reference_date'] }}</strong>
        </div>
    </div>

    <div class="table-responsive-custom">
        <table class="table-custom" id="unitRecapManHoursTable">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">No</th>
                    <th>Nama Unit / Area Operasi</th>
                    <th style="width: 145px; text-align: center;">Pekerja ({{ $unitRecap['reference_date'] }})</th>
                    <th style="width: 120px; text-align: center;">Jam Kerja / Orang</th>
                    <th style="width: 175px; text-align: center;">Man Hours Harian ({{ $unitRecap['reference_date'] }})</th>
                    <th style="width: 145px; text-align: center;">Total Pekerja (Periode)</th>
                    <th style="width: 175px; text-align: center;">Total Akumulasi Man Hours Unit</th>
                    <th>Status / Keterangan Unit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($unitRecap['rows'] as $uIdx => $uRow)
                    <tr>
                        <td style="text-align: center; color: #64748b;">{{ $uIdx + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold text-dark">{{ $uRow['unit_name'] }}</span>
                                @if(!empty($uRow['is_new_unit']))
                                    <span class="badge bg-warning text-dark" style="font-size: 9.5px;">
                                        <i class="bi bi-stars"></i> Unit Baru
                                    </span>
                                @endif
                                @if(!$uRow['is_active'])
                                    <span class="badge bg-secondary" style="font-size: 9.5px;">Nonaktif</span>
                                @endif
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <span class="fw-bold text-dark">{{ number_format((int) $uRow['latest_workers'], 0, ',', '.') }}</span>
                            <span class="text-muted small">Orang</span>
                        </td>
                        <td style="text-align: center;">
                            <span class="text-primary fw-semibold">{{ number_format((float) $uRow['hours_per_worker'], 1, ',', '.') }}</span>
                            <span class="text-muted small">Jam</span>
                        </td>
                        <td style="text-align: center;">
                            <span class="mh-formula-badge">
                                {{ number_format((int) $uRow['latest_workers'], 0, ',', '.') }} × {{ number_format((float) $uRow['hours_per_worker'], 1, ',', '.') }}
                            </span>
                            <span class="fw-bold text-success ms-1">{{ number_format((float) $uRow['latest_man_hours'], 1, ',', '.') }} Jam</span>
                        </td>
                        <td style="text-align: center;">
                            <span class="fw-semibold text-dark">{{ number_format((int) $uRow['period_workers'], 0, ',', '.') }}</span>
                            <span class="text-muted small">Orang</span>
                        </td>
                        <td style="text-align: center; min-width: 210px;" id="unitAccumCell-{{ $uRow['unit_id'] ?? $uIdx }}">
                            {{-- Tampilan Nilai Akumulasi & Tombol Edit --}}
                            <div id="unitAccumDisplay-{{ $uRow['unit_id'] ?? $uIdx }}" class="d-flex align-items-center justify-content-center gap-1 flex-wrap">
                                <span class="mh-total-badge" id="unitAccumVal-{{ $uRow['unit_id'] ?? $uIdx }}">
                                    {{ number_format((float) $uRow['period_man_hours'], 1, ',', '.') }} Jam
                                </span>
                                @if(!empty($uRow['has_manual_adjustment']))
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-1" style="font-size: 9.5px;" title="Koreksi manual: {{ $uRow['adjustment_delta'] >= 0 ? '+' : '' }}{{ number_format((float) $uRow['adjustment_delta'], 1, ',', '.') }} Jam (Sistem: {{ number_format((float) $uRow['raw_man_hours'], 1, ',', '.') }} Jam) oleh {{ $uRow['adjusted_by'] ?? 'User' }}" id="unitAccumBadge-{{ $uRow['unit_id'] ?? $uIdx }}">
                                        <i class="bi bi-pencil-fill me-1"></i>Koreksi Manual
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-1 d-none" style="font-size: 9.5px;" id="unitAccumBadge-{{ $uRow['unit_id'] ?? $uIdx }}">
                                        <i class="bi bi-pencil-fill me-1"></i>Koreksi Manual
                                    </span>
                                @endif
                                @if($isAdmin)
                                    <button type="button" 
                                            class="btn btn-outline-primary btn-sm py-0 px-1 ms-1 btn-edit-accum" 
                                            style="font-size: 11px; line-height: 1.2; border-radius: 4px;"
                                            title="Koreksi Manual Total Akumulasi Unit"
                                            onclick="showUnitAccumEdit('{{ $uRow['unit_id'] ?? $uIdx }}')">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                @endif
                            </div>

                            {{-- Form Edit Inline --}}
                            <div id="unitAccumForm-{{ $uRow['unit_id'] ?? $uIdx }}" class="d-none">
                                <form onsubmit="saveUnitAccum(event, '{{ $uRow['unit_id'] ?? $uRow['unit_name'] }}', '{{ $uRow['unit_id'] ?? $uIdx }}', '{{ addslashes($uRow['unit_name']) }}')">
                                    <div class="input-group input-group-sm mb-1" style="max-width: 210px; margin: 0 auto;">
                                        <input type="number" 
                                               step="0.01" 
                                               min="0" 
                                               class="form-control form-control-sm text-center fw-bold" 
                                               id="unitAccumInput-{{ $uRow['unit_id'] ?? $uIdx }}" 
                                               value="{{ (float) $uRow['period_man_hours'] }}" 
                                               data-raw="{{ (float) $uRow['raw_man_hours'] }}"
                                               required 
                                               style="font-size: 11.5px;">
                                        <button type="submit" class="btn btn-success btn-sm px-2" title="Simpan Perubahan" id="btnSaveAccum-{{ $uRow['unit_id'] ?? $uIdx }}">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm px-2" title="Batal" onclick="hideUnitAccumEdit('{{ $uRow['unit_id'] ?? $uIdx }}')">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center px-1" style="font-size: 9.5px;">
                                        <span class="text-muted" title="Akumulasi murni dari input workforce" id="unitAccumRawNote-{{ $uRow['unit_id'] ?? $uIdx }}">Sistem: {{ number_format((float) $uRow['raw_man_hours'], 1, ',', '.') }} Jam</span>
                                        <a href="javascript:void(0)" class="text-decoration-none text-danger" onclick="resetUnitAccum('{{ $uRow['unit_id'] ?? $uRow['unit_name'] }}', '{{ $uRow['unit_id'] ?? $uIdx }}', {{ (float) $uRow['raw_man_hours'] }})">Reset</a>
                                    </div>
                                </form>
                            </div>
                        </td>
                        <td>
                            <span class="small {{ !empty($uRow['is_new_unit']) ? 'text-warning-emphasis fw-semibold' : 'text-secondary' }}" id="unitStatusNote-{{ $uRow['unit_id'] ?? $uIdx }}">
                                {{ $uRow['status_notes'] }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="2" class="text-end text-dark">TOTAL KESELURUHAN SELURUH UNIT :</td>
                    <td style="text-align: center;" class="text-primary">
                        {{ number_format((int) $unitRecap['totals']['latest_workers'], 0, ',', '.') }} Orang
                    </td>
                    <td style="text-align: center;" class="text-muted">-</td>
                    <td style="text-align: center;" class="text-success">
                        {{ number_format((float) $unitRecap['totals']['latest_man_hours'], 1, ',', '.') }} Jam
                    </td>
                    <td style="text-align: center;" class="text-primary">
                        {{ number_format((int) $unitRecap['totals']['period_workers'], 0, ',', '.') }} Orang
                    </td>
                    <td style="text-align: center;">
                        <span class="mh-total-badge" id="overallTotalPeriodManHours" style="font-size: 12.5px;">
                            {{ number_format((float) $unitRecap['totals']['period_man_hours'], 1, ',', '.') }} Jam
                        </span>
                    </td>
                    <td class="text-muted small fw-normal">
                        Akumulasi dari {{ count($unitRecap['rows']) }} unit pada periode terpilih
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif

<!-- TABEL RIWAYAT HARIAN MAN HOURS (AKUMULASI HARIAN & KUMULATIF BERJALAN) -->
@if(!empty($dailyRecap))
<div class="table-card mb-3">
    <div class="table-topbar">
        <div>
            <h2 class="table-section-title mb-0">
                <i class="bi bi-calendar-week text-primary me-1"></i> Tabel Riwayat Harian &amp; Akumulasi Man Hours
                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle ms-2" style="font-size:10.5px;">
                    Total Akumulasi = Σ (Total Man Hours Harian tiap Tanggal)
                </span>
            </h2>
        </div>
        <div class="text-muted small">
            Urutan tanggal terbaru ke terlama (tanpa menggandakan akumulasi)
        </div>
    </div>

    <div class="table-responsive-custom">
        <table class="table-custom" id="dailyManHoursRecapTable">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">No</th>
                    <th style="width: 135px;">Tanggal Kerja</th>
                    <th style="width: 115px; text-align: center;">Unit Tercatat</th>
                    <th>Rincian Man Hours per Unit pada Tanggal Tersebut</th>
                    <th style="width: 140px; text-align: center;">Total Pekerja Harian</th>
                    <th style="width: 165px; text-align: center;">Total Man Hours Harian</th>
                    <th style="width: 185px; text-align: center;">Akumulasi Berjalan (Kumulatif)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dailyRecap as $dIdx => $dayRow)
                    <tr>
                        <td style="text-align: center; color: #64748b;">{{ $dIdx + 1 }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $dayRow['work_date_formatted'] }}</div>
                            <div class="text-muted" style="font-size: 10px;">{{ $dayRow['work_date_long'] }}</div>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge bg-secondary-subtle text-dark border">{{ $dayRow['units_count'] }} Unit</span>
                            @if(!empty($dayRow['new_units_included']))
                                <div class="mt-1">
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size: 9px;" title="Termasuk unit baru: {{ implode(', ', $dayRow['new_units_included']) }}">
                                        +{{ count($dayRow['new_units_included']) }} Unit Baru
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($dayRow['units'] as $uBreak)
                                    <span class="badge {{ !empty($uBreak['is_new_unit']) ? 'bg-warning-subtle text-dark border-warning' : 'bg-light text-dark' }} border px-2 py-1" style="font-size: 10.5px; font-weight: 600;">
                                        {{ $uBreak['unit'] }}: {{ $uBreak['workers'] }} org × {{ number_format((float) $uBreak['hours_per_worker'], 0, ',', '.') }}j =
                                        <strong class="text-success">{{ number_format((float) $uBreak['man_hours'], 0, ',', '.') }} Jam</strong>
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <span class="fw-bold text-dark">{{ number_format((int) $dayRow['total_workers'], 0, ',', '.') }}</span>
                            <span class="text-muted small">Orang</span>
                        </td>
                        <td style="text-align: center;">
                            <span class="mh-total-badge">
                                +{{ number_format((float) $dayRow['daily_man_hours'], 1, ',', '.') }} Jam
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 11.5px; font-weight: 700;">
                                <i class="bi bi-graph-up-arrow me-1"></i>
                                {{ number_format((float) $dayRow['running_cumulative_man_hours'], 1, ',', '.') }} Jam
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- TABEL RINCIAN PERHITUNGAN MAN HOURS -->
<div class="table-card">
    <div class="table-topbar">
        <div>
            <h2 class="table-section-title mb-0">
                Rincian Perhitungan Man Hours
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-2" style="font-size:10.5px;">
                    Rumus: Pekerja × Jam Kerja/Orang
                </span>
            </h2>
        </div>

        <div class="table-controls">
            <div class="table-search-wrap">
                <i class="bi bi-search filter-icon-addon"></i>
                <input type="search" class="form-control table-search-input" id="mhTableSearch" placeholder="Cari baris tabel...">
            </div>

            <select class="form-select form-select-sm" id="mhSortBy" style="width:170px; font-size:11.5px">
                <option value="date-desc" {{ request('sort', 'date-desc') === 'date-desc' ? 'selected' : '' }}>Tanggal Terbaru</option>
                <option value="date-asc" {{ request('sort') === 'date-asc' ? 'selected' : '' }}>Tanggal Terlama</option>
                <option value="hours-desc" {{ request('sort') === 'hours-desc' ? 'selected' : '' }}>Man Hours Tertinggi</option>
                <option value="hours-asc" {{ request('sort') === 'hours-asc' ? 'selected' : '' }}>Man Hours Terendah</option>
                <option value="workers-desc" {{ request('sort') === 'workers-desc' ? 'selected' : '' }}>Pekerja Terbanyak</option>
                <option value="category" {{ request('sort') === 'category' ? 'selected' : '' }}>Urut Kategori</option>
            </select>
        </div>
    </div>

    <div class="table-responsive-custom">
        <table class="table-custom" id="manHoursTable">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">No</th>
                    <th style="width: 110px;">Tanggal</th>
                    <th style="width: 130px;">Sumber Workforce</th>
                    <th>Kategori / Komponen</th>
                    <th>Unit &amp; Keterangan</th>
                    <th style="width: 135px; text-align: center;">Jumlah Pekerja</th>
                    <th style="width: 120px; text-align: center;">Jam / Orang</th>
                    <th style="width: 210px; text-align: center;">Rumus &amp; Hasil Man Hours</th>
                    <th style="width: 100px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody id="manHoursTableBody">
                @forelse($manHours as $index => $row)
                    @php
                        $catClass = match($row->category) {
                            'GDE' => 'cat-gde',
                            'Kodipa' => 'cat-kodipa',
                            'Utility' => 'cat-utility',
                            'Security' => 'cat-security',
                            'Contractor & Sub-Contractor' => 'cat-contractor',
                            'Kerja Praktik' => 'cat-kp',
                            default => 'cat-default',
                        };
                        $wfCode = $row->workforce?->employee_code ?? '-';
                        $wfSubUnit = $row->workforce?->department ?: ($row->notes ?: '');
                    @endphp
                    <tr data-id="{{ $row->id }}">
                        <td style="text-align: center; color: #64748b;">{{ $manHours->firstItem() + $index }}</td>
                        <td class="fw-semibold">{{ $row->work_date ? $row->work_date->format('d/m/Y') : '-' }}</td>
                        <td>
                            <span class="wf-code-badge" title="Terhubung ke Data Workforce {{ $wfCode }}">
                                <i class="bi bi-link-45deg"></i>{{ $wfCode }}
                            </span>
                        </td>
                        <td>
                            <span class="cat-pill {{ $catClass }}">{{ $row->category }}</span>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $row->unit }}</div>
                            @if($wfSubUnit)
                                <div class="text-muted" style="font-size: 10.5px;">{{ $wfSubUnit }}</div>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <span class="fw-bold text-dark" style="font-size: 13px;">{{ number_format((int) $row->number_of_workers, 0, ',', '.') }}</span>
                            <span class="text-muted small ms-1">Orang</span>
                        </td>
                        <td style="text-align: center;">
                            <span class="text-primary fw-bold">{{ number_format((float) $row->hours_per_worker, 1, ',', '.') }}</span>
                            <span class="text-muted small">Jam</span>
                        </td>
                        <td style="text-align: center;">
                            <div class="d-inline-flex align-items-center gap-1 flex-wrap justify-content-center">
                                <span class="mh-formula-badge">
                                    {{ number_format((int) $row->number_of_workers, 0, ',', '.') }} × {{ number_format((float) $row->hours_per_worker, 1, ',', '.') }}
                                </span>
                                <span class="text-muted fw-bold">=</span>
                                <span class="mh-total-badge">
                                    {{ number_format((float) $row->total_hours, 1, ',', '.') }} Jam
                                </span>
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <button class="btn-action-icon btn-action-view" onclick="viewManHour({{ $row->id }})" title="Lihat Detail Perhitungan">
                                <i class="bi bi-eye"></i>
                            </button>
                            @if($isAdmin)
                                <button class="btn-action-icon btn-action-edit" onclick="openAdjustModal({{ $row->id }})" title="Sesuaikan Jam Kerja per Orang">
                                    <i class="bi bi-sliders"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr id="emptyManHoursRow">
                        <td colspan="9" class="p-0">
                            <div class="empty-state-mh">
                                <div class="empty-state-mh-icon">
                                    <i class="bi bi-clock-history"></i>
                                </div>
                                <h6 class="fw-bold text-secondary mb-1">Belum ada data Workforce untuk perhitungan Man Hours</h6>
                                <p class="text-muted small mb-3">
                                    Perhitungan Man Hours diambil secara otomatis dari jumlah pekerja di menu <strong>Workforce</strong>. Silakan tambahkan data pekerja pada menu Workforce terlebih dahulu.
                                </p>
                                <a href="{{ route('workforce.index') }}" class="btn-action-primary d-inline-flex align-items-center gap-1 text-decoration-none">
                                    <i class="bi bi-people-fill"></i> Buka Menu Workforce
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($manHours->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
            <div class="text-muted small">
                Menampilkan {{ $manHours->firstItem() }}–{{ $manHours->lastItem() }} dari {{ $manHours->total() }} data Man Hours
            </div>
            <div>
                {{ $manHours->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>

<!-- MODAL DETAIL PERHITUNGAN MAN HOURS -->
<div class="modal fade" id="manHourDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="header-icon-box green" style="width:34px; height:34px; font-size:16px;">
                        <i class="bi bi-calculator-fill"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold">Detail Perhitungan Man Hours</h5>
                        <div class="text-muted" style="font-size:11px;">Rincian sumber Workforce, parameter jam kerja, dan total Man Hours</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="detail-grid-mh">
                    <div class="detail-label-mh">Kode Sumber Workforce</div><div>:</div>
                    <div><span id="detailMhCode" class="wf-code-badge">-</span></div>

                    <div class="detail-label-mh">Tanggal Kerja</div><div>:</div>
                    <div id="detailMhDate" class="fw-bold text-dark">-</div>

                    <div class="detail-label-mh">Kategori / Komponen</div><div>:</div>
                    <div id="detailMhCategory">-</div>

                    <div class="detail-label-mh">Unit / Area Kerja</div><div>:</div>
                    <div id="detailMhUnit" class="fw-semibold">-</div>

                    <div class="detail-label-mh">Sub-Unit / Perusahaan</div><div>:</div>
                    <div id="detailMhDepartment">-</div>

                    <div class="detail-label-mh">Jumlah Pekerja Sumber</div><div>:</div>
                    <div id="detailMhWorkers" class="fw-bold text-primary">-</div>

                    <div class="detail-label-mh">Jam Kerja per Orang</div><div>:</div>
                    <div id="detailMhHoursPerWorker" class="fw-bold text-dark">-</div>

                    <div class="detail-label-mh">Total Man Hours</div><div>:</div>
                    <div id="detailMhTotalHours" class="fw-bold text-success">-</div>

                    <div class="detail-label-mh">Catatan / Keterangan</div><div>:</div>
                    <div id="detailMhNotes" class="text-secondary">-</div>
                </div>

                <div class="calc-preview-card mt-3">
                    <div class="small fw-bold text-success mb-1">
                        <i class="bi bi-calculator me-1"></i> Rumus Perhitungan Otomatis
                    </div>
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="small fw-semibold text-dark" id="detailMhFormulaText">
                            Man Hours = Jumlah Pekerja × Jam Kerja per Orang
                        </div>
                        <a href="{{ route('workforce.index') }}" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size:11px;">
                            <i class="bi bi-people me-1"></i> Lihat Sumber di Workforce
                        </a>
                    </div>
                </div>

                <hr class="my-3">

                <h6 class="fw-bold mb-2" style="font-size: 12.5px;">
                    <i class="bi bi-clock-history me-1 text-primary"></i> Riwayat Aktivitas Data Ini
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0" style="font-size: 11px;">
                        <thead class="table-light">
                            <tr>
                                <th style="width:130px;">Waktu</th>
                                <th style="width:140px;">Pengguna</th>
                                <th style="width:80px; text-align:center;">Aksi</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="detailMhActivities">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-2">Belum ada riwayat aktivitas.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top bg-light">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
                @if($isAdmin)
                    <button type="button" class="btn btn-warning btn-sm px-3" id="btnAdjustFromDetailMh">
                        <i class="bi bi-sliders me-1"></i> Sesuaikan Jam Kerja
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

@if($isAdmin)
<!-- MODAL SESUAIKAN PARAMETER JAM KERJA PER ORANG -->
<div class="modal fade" id="manHourAdjustModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="manHourAdjustForm" onsubmit="saveManHourAdjustment(event)">
                <div class="modal-header border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="header-icon-box green" style="width:34px; height:34px; font-size:16px;">
                            <i class="bi bi-sliders"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold">Sesuaikan Parameter Jam Kerja</h5>
                            <div class="text-muted" style="font-size:11px;">Jumlah pekerja diambil otomatis dari sumber Workforce</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="adjMhId">
                    <input type="hidden" id="adjWorkersCount" value="0">

                    <div class="p-3 bg-light border rounded mb-3" style="font-size: 12px;">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Sumber Workforce:</span>
                            <span id="adjWfCode" class="wf-code-badge">-</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Tanggal &amp; Kategori:</span>
                            <span id="adjDateCategory" class="fw-bold text-dark">-</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Jumlah Pekerja Sumber:</span>
                            <span id="adjWorkersLabel" class="fw-bold text-primary">0 Orang</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:12px;">Jam Kerja per Orang (Jam/Hari) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-sm" id="adjHoursPerWorker" name="hours_per_worker"
                               min="0" max="24" step="0.5" required value="8" oninput="updateAdjustPreviewMh()">
                        <div class="d-flex flex-wrap gap-1 mt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size:11px;" onclick="setHoursPreset(7)">7 Jam</button>
                            <button type="button" class="btn btn-outline-success btn-sm py-0 px-2" style="font-size:11px;" onclick="setHoursPreset(8)">8 Jam (Standar)</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size:11px;" onclick="setHoursPreset(9)">9 Jam</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size:11px;" onclick="setHoursPreset(10)">10 Jam</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size:11px;" onclick="setHoursPreset(12)">12 Jam (Shift)</button>
                        </div>
                    </div>

                    <div class="calc-preview-card d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="fw-bold text-success" style="font-size: 11.5px;">
                                <i class="bi bi-lightning-charge-fill me-1"></i> Hasil Perhitungan Ulang
                            </div>
                            <div class="text-muted" style="font-size: 11px;" id="adjFormulaBreakdown">
                                0 Pekerja × 8 Jam/Orang
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="fs-5 fw-bold text-success" id="adjTotalManHoursPreview">0</span>
                            <span class="small fw-semibold text-secondary">Jam</span>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold" style="font-size:12px;">Catatan / Keterangan</label>
                        <textarea class="form-control form-control-sm" id="adjNotes" name="notes" rows="2" placeholder="Catatan jam kerja aktual atau shift..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm px-4" id="btnSaveMhAdjust">
                        <i class="bi bi-check2-circle me-1"></i> Simpan Parameter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    const mhTrendChartData = @json($trendChart);
    const mhCategoryChartData = @json($categoryChart);
    const mhMonthlyChartData = @json($monthlyChart);
    const mhUnitChartData = @json($unitChart ?? ['labels' => [], 'man_hours' => [], 'is_new' => []]);

    let mhTrendChartInstance = null;
    let mhCategoryChartInstance = null;
    let mhMonthlyChartInstance = null;

    document.addEventListener('DOMContentLoaded', function () {
        initManHoursCharts();
        bindManHoursFilters();
    });

    function initManHoursCharts() {
        if (typeof Chart === 'undefined') return;

        // 1. Tren Man Hours per Tanggal
        const trendCtx = document.getElementById('mhTrendChartCanvas');
        if (trendCtx) {
            const labels = mhTrendChartData.labels && mhTrendChartData.labels.length ? mhTrendChartData.labels : ['Belum Ada Data'];
            const hours = mhTrendChartData.man_hours && mhTrendChartData.man_hours.length ? mhTrendChartData.man_hours : [0];

            mhTrendChartInstance = new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Man Hours Harian (Jam)',
                        data: hours,
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.14)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointRadius: 3.5,
                        pointBackgroundColor: '#059669'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { font: { size: 10 } },
                            grid: { color: '#f1f5f9' }
                        },
                        x: {
                            ticks: { font: { size: 10 } },
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        // 2. Distribusi Man Hours per Kategori (Donut)
        const catCtx = document.getElementById('mhCategoryChartCanvas');
        if (catCtx) {
            const rawData = mhCategoryChartData.data || [];
            const total = rawData.reduce((a, b) => a + Number(b || 0), 0);
            const chartData = total > 0 ? rawData : [1];
            const chartColors = total > 0 ? mhCategoryChartData.colors : ['#e2e8f0'];
            const chartLabels = total > 0 ? mhCategoryChartData.labels : ['Belum Ada Data'];

            mhCategoryChartInstance = new Chart(catCtx, {
                type: 'doughnut',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        data: chartData,
                        backgroundColor: chartColors,
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '66%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            enabled: total > 0
                        }
                    }
                }
            });
        }

        // 3. Perbandingan Man Hours per Unit / Area Operasi
        const monthlyCtx = document.getElementById('mhMonthlyChartCanvas');
        if (monthlyCtx) {
            const uLabels = mhUnitChartData.labels && mhUnitChartData.labels.length
                ? mhUnitChartData.labels
                : (mhMonthlyChartData.labels && mhMonthlyChartData.labels.length ? mhMonthlyChartData.labels : ['Belum Ada Data']);
            const uValues = mhUnitChartData.man_hours && mhUnitChartData.man_hours.length
                ? mhUnitChartData.man_hours
                : (mhMonthlyChartData.data && mhMonthlyChartData.data.length ? mhMonthlyChartData.data : [0]);
            const uColors = (mhUnitChartData.is_new && mhUnitChartData.is_new.length)
                ? mhUnitChartData.is_new.map(isNew => isNew ? '#f59e0b' : '#2563eb')
                : '#2563eb';

            mhMonthlyChartInstance = new Chart(monthlyCtx, {
                type: 'bar',
                data: {
                    labels: uLabels,
                    datasets: [{
                        label: 'Total Man Hours Unit (Jam)',
                        data: uValues,
                        backgroundColor: uColors,
                        borderRadius: 4,
                        maxBarThickness: 32
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { font: { size: 10 } },
                            grid: { color: '#f1f5f9' }
                        },
                        x: {
                            ticks: { font: { size: 9.5 } },
                            grid: { display: false }
                        }
                    }
                }
            });
        }
    }

    function bindManHoursFilters() {
        const triggerApply = () => applyMhFilters();
        ['filterStart', 'filterEnd', 'filterCategory', 'filterUnit', 'filterPeriod', 'mhSortBy'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('change', triggerApply);
        });

        const searchEl = document.getElementById('filterSearch');
        if (searchEl) {
            let timer = null;
            searchEl.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(triggerApply, 400);
            });
        }

        const tableSearch = document.getElementById('mhTableSearch');
        if (tableSearch) {
            tableSearch.addEventListener('input', function () {
                const q = this.value.toLowerCase().trim();
                document.querySelectorAll('#manHoursTableBody tr[data-id]').forEach(tr => {
                    tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
                });
            });
        }
    }

    function applyMhFilters() {
        const params = new URLSearchParams();
        const start = document.getElementById('filterStart')?.value || '';
        const end = document.getElementById('filterEnd')?.value || '';
        const category = document.getElementById('filterCategory')?.value || '';
        const unit = document.getElementById('filterUnit')?.value || '';
        const period = document.getElementById('filterPeriod')?.value || '';
        const search = document.getElementById('filterSearch')?.value || '';
        const sort = document.getElementById('mhSortBy')?.value || 'date-desc';

        if (start) params.set('start_date', start);
        if (end) params.set('end_date', end);
        if (category) params.set('category', category);
        if (unit) params.set('unit', unit);
        if (period) params.set('period', period);
        if (search) params.set('search', search);
        if (sort && sort !== 'date-desc') params.set('sort', sort);

        const qs = params.toString();
        window.location.href = `{{ route('man-hours.index') }}${qs ? '?' + qs : ''}`;
    }

    function resetMhFilters() {
        window.location.href = `{{ route('man-hours.index') }}`;
    }

    async function viewManHour(id) {
        try {
            const res = await fetch(`/man-hours/${id}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const json = await res.json();
            if (!json.success) return;

            const mh = json.man_hour;
            const wf = json.workforce || {};
            const dateStr = mh.work_date ? mh.work_date.substring(0, 10).split('-').reverse().join('/') : '-';

            document.getElementById('detailMhCode').textContent = wf.employee_code || '-';
            document.getElementById('detailMhDate').textContent = dateStr;
            document.getElementById('detailMhCategory').textContent = mh.category || '-';
            document.getElementById('detailMhUnit').textContent = mh.unit || '-';
            document.getElementById('detailMhDepartment').textContent = wf.department || wf.name || '-';
            document.getElementById('detailMhWorkers').textContent = `${Number(mh.number_of_workers || 0).toLocaleString('id-ID')} Orang`;
            document.getElementById('detailMhHoursPerWorker').textContent = `${Number(mh.hours_per_worker || 8).toLocaleString('id-ID')} Jam / Orang`;
            document.getElementById('detailMhTotalHours').textContent = `${Number(mh.total_hours || 0).toLocaleString('id-ID')} Jam`;
            document.getElementById('detailMhNotes').textContent = mh.notes || '-';
            document.getElementById('detailMhFormulaText').textContent = json.formula_text || '';

            const actBody = document.getElementById('detailMhActivities');
            if (actBody) {
                const acts = json.activities || [];
                if (acts.length === 0) {
                    actBody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-2">Belum ada riwayat aktivitas.</td></tr>`;
                } else {
                    actBody.innerHTML = acts.map(a => {
                        const dt = a.created_at ? new Date(a.created_at).toLocaleString('id-ID') : '-';
                        return `
                            <tr>
                                <td>${dt}</td>
                                <td class="fw-semibold">${a.user_name || 'Sistem'}</td>
                                <td class="text-center"><span class="badge bg-secondary">${a.action || '-'}</span></td>
                                <td>${a.description || '-'}</td>
                            </tr>
                        `;
                    }).join('');
                }
            }

            const btnAdjust = document.getElementById('btnAdjustFromDetailMh');
            if (btnAdjust) {
                btnAdjust.onclick = () => {
                    bootstrap.Modal.getInstance(document.getElementById('manHourDetailModal'))?.hide();
                    openAdjustModal(mh.id);
                };
            }

            new bootstrap.Modal(document.getElementById('manHourDetailModal')).show();
        } catch (e) {
            console.error(e);
        }
    }

    async function openAdjustModal(id) {
        try {
            const res = await fetch(`/man-hours/${id}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const json = await res.json();
            if (!json.success) return;

            const mh = json.man_hour;
            const wf = json.workforce || {};
            const dateStr = mh.work_date ? mh.work_date.substring(0, 10).split('-').reverse().join('/') : '-';

            document.getElementById('adjMhId').value = mh.id;
            document.getElementById('adjWorkersCount').value = mh.number_of_workers || 0;
            document.getElementById('adjWfCode').textContent = wf.employee_code || '-';
            document.getElementById('adjDateCategory').textContent = `${dateStr} • ${mh.category || '-'}`;
            document.getElementById('adjWorkersLabel').textContent = `${Number(mh.number_of_workers || 0).toLocaleString('id-ID')} Orang`;
            document.getElementById('adjHoursPerWorker').value = parseFloat(mh.hours_per_worker || 8);
            document.getElementById('adjNotes').value = mh.notes || '';

            updateAdjustPreviewMh();
            new bootstrap.Modal(document.getElementById('manHourAdjustModal')).show();
        } catch (e) {
            console.error(e);
        }
    }

    function setHoursPreset(val) {
        const el = document.getElementById('adjHoursPerWorker');
        if (el) {
            el.value = val;
            updateAdjustPreviewMh();
        }
    }

    function updateAdjustPreviewMh() {
        const workers = Math.max(0, parseInt(document.getElementById('adjWorkersCount')?.value || '0', 10) || 0);
        const hours = Math.max(0, parseFloat(document.getElementById('adjHoursPerWorker')?.value || '0') || 0);
        const total = Math.round(workers * hours * 100) / 100;

        const breakdownEl = document.getElementById('adjFormulaBreakdown');
        const totalEl = document.getElementById('adjTotalManHoursPreview');
        if (breakdownEl) breakdownEl.textContent = `${workers.toLocaleString('id-ID')} Pekerja × ${hours.toLocaleString('id-ID')} Jam/Orang`;
        if (totalEl) totalEl.textContent = total.toLocaleString('id-ID');
    }

    async function saveManHourAdjustment(e) {
        e.preventDefault();
        const id = document.getElementById('adjMhId')?.value;
        if (!id) return;

        const payload = {
            hours_per_worker: parseFloat(document.getElementById('adjHoursPerWorker')?.value || '8'),
            notes: document.getElementById('adjNotes')?.value || ''
        };

        try {
            const res = await fetch(`/man-hours/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(payload)
            });

            const json = await res.json();
            if (res.ok && json.success) {
                window.location.reload();
            } else {
                alert(json.message || 'Gagal menyimpan penyesuaian parameter jam kerja.');
            }
        } catch (err) {
            alert('Terjadi kesalahan saat menyimpan penyesuaian.');
        }
    }

    function showUnitAccumEdit(id) {
        const display = document.getElementById(`unitAccumDisplay-${id}`);
        const form = document.getElementById(`unitAccumForm-${id}`);
        if (display && form) {
            display.classList.add('d-none');
            form.classList.remove('d-none');
            const inp = document.getElementById(`unitAccumInput-${id}`);
            if (inp) {
                inp.focus();
                inp.select();
            }
        }
    }

    function hideUnitAccumEdit(id) {
        const display = document.getElementById(`unitAccumDisplay-${id}`);
        const form = document.getElementById(`unitAccumForm-${id}`);
        if (display && form) {
            form.classList.add('d-none');
            display.classList.remove('d-none');
        }
    }

    function showMhNotification(type, message) {
        const box = document.getElementById('manHourAlertBox');
        if (!box) {
            alert(message);
            return;
        }
        const bgClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
        box.innerHTML = `
            <div class="alert ${bgClass} alert-dismissible fade show d-flex align-items-center gap-2 py-2 px-3 mb-3 shadow-sm" role="alert" style="border-radius: 8px;">
                <i class="bi ${icon} fs-5"></i>
                <div class="flex-grow-1" style="font-size: 12px; font-weight: 500;">${message}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="padding: 0.75rem;"></button>
            </div>
        `;
        box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function recalculateOverallFooterTotal() {
        let total = 0;
        document.querySelectorAll('[id^="unitAccumVal-"]').forEach(el => {
            const numStr = el.textContent.replace('Jam', '').trim().replace(/\./g, '').replace(',', '.');
            const val = parseFloat(numStr) || 0;
            total += val;
        });
        const footerTotal = document.getElementById('overallTotalPeriodManHours');
        if (footerTotal) {
            footerTotal.textContent = `${total.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 })} Jam`;
        }
        const statTotal = document.getElementById('statTotalManHours');
        if (statTotal) {
            statTotal.textContent = Math.round(total).toLocaleString('id-ID');
        }
    }

    async function saveUnitAccum(e, unitParam, id, unitName) {
        e.preventDefault();
        const input = document.getElementById(`unitAccumInput-${id}`);
        const btnSave = document.getElementById(`btnSaveAccum-${id}`);
        if (!input) return;

        const val = parseFloat(input.value);
        if (isNaN(val) || val < 0) {
            showMhNotification('danger', 'Nilai Total Akumulasi Man Hours Unit harus berupa angka non-negatif.');
            return;
        }

        if (btnSave) {
            btnSave.disabled = true;
            btnSave.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const res = await fetch(`/man-hours/units/${encodeURIComponent(unitParam)}/accumulated-hours`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    accumulated_hours: val,
                    notes: `Koreksi manual Total Akumulasi Unit ${unitName}`
                })
            });

            const json = await res.json();
            if (res.ok && json.success) {
                // Update tampilan tabel langsung tanpa refresh seluruh halaman
                const valEl = document.getElementById(`unitAccumVal-${id}`);
                if (valEl) {
                    valEl.textContent = `${val.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 })} Jam`;
                }

                const badgeEl = document.getElementById(`unitAccumBadge-${id}`);
                const rawSystem = parseFloat(input.getAttribute('data-raw') || '0');
                const delta = Math.round((val - rawSystem) * 100) / 100;
                if (badgeEl) {
                    if (Math.abs(delta) > 0.001) {
                        badgeEl.classList.remove('d-none');
                        const sign = delta >= 0 ? '+' : '';
                        badgeEl.title = `Koreksi manual: ${sign}${delta.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 })} Jam (Sistem: ${rawSystem.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 })} Jam)`;
                    } else {
                        badgeEl.classList.add('d-none');
                    }
                }

                const noteEl = document.getElementById(`unitStatusNote-${id}`);
                if (noteEl && Math.abs(delta) > 0.001) {
                    const sign = delta >= 0 ? '+' : '';
                    noteEl.textContent = `Koreksi manual (${sign}${delta.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 })} Jam)`;
                    noteEl.className = 'small text-warning-emphasis fw-semibold';
                }

                hideUnitAccumEdit(id);
                recalculateOverallFooterTotal();
                showMhNotification('success', json.message || `Total Akumulasi Man Hours Unit ${unitName} berhasil diperbarui.`);
            } else {
                showMhNotification('danger', json.message || 'Gagal menyimpan koreksi akumulasi.');
            }
        } catch (err) {
            console.error(err);
            showMhNotification('danger', 'Terjadi kesalahan sistem saat menyimpan data.');
        } finally {
            if (btnSave) {
                btnSave.disabled = false;
                btnSave.innerHTML = '<i class="bi bi-check-lg"></i>';
            }
        }
    }

    async function resetUnitAccum(unitParam, id, rawVal) {
        if (!confirm('Kembalikan nilai akumulasi unit ini ke perhitungan otomatis sistem?')) {
            return;
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const res = await fetch(`/man-hours/units/${encodeURIComponent(unitParam)}/accumulated-hours`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    reset: true,
                    accumulated_hours: rawVal,
                    notes: 'Direset ke perhitungan otomatis sistem'
                })
            });

            const json = await res.json();
            if (res.ok && json.success) {
                const valEl = document.getElementById(`unitAccumVal-${id}`);
                if (valEl) {
                    valEl.textContent = `${rawVal.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 })} Jam`;
                }

                const inpEl = document.getElementById(`unitAccumInput-${id}`);
                if (inpEl) {
                    inpEl.value = rawVal;
                }

                const badgeEl = document.getElementById(`unitAccumBadge-${id}`);
                if (badgeEl) {
                    badgeEl.classList.add('d-none');
                }

                const noteEl = document.getElementById(`unitStatusNote-${id}`);
                if (noteEl) {
                    noteEl.textContent = 'Aktif dalam perhitungan';
                    noteEl.className = 'small text-secondary';
                }

                hideUnitAccumEdit(id);
                recalculateOverallFooterTotal();
                showMhNotification('success', 'Akumulasi unit berhasil dikembalikan ke perhitungan otomatis.');
            } else {
                showMhNotification('danger', json.message || 'Gagal mereset akumulasi.');
            }
        } catch (err) {
            console.error(err);
            showMhNotification('danger', 'Terjadi kesalahan sistem saat mereset data.');
        }
    }
</script>
@endpush

