@extends('layouts.app', [
    'title' => 'Issue & Follow Up - HSSE Monitoring Dashboard',
    'activeModule' => 'issue-follow-up'
])

@push('styles')
<style>
    .filter-grid-ifu-revised {
        display: grid;
        grid-template-columns: minmax(250px, 290px) 150px 145px 175px 1fr auto;
        gap: 8px;
        align-items: center;
    }

    @media (max-width: 1300px) {
        .filter-grid-ifu-revised {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 768px) {
        .filter-grid-ifu-revised {
            grid-template-columns: 1fr;
        }
    }

    /* Date Range Picker Container & Styles - Harmonized with form-control-filter */
    .date-range-container {
        position: relative;
        display: flex;
        align-items: center;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 0 10px;
        height: 36px;
        width: 100%;
        box-sizing: border-box;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .date-range-container:hover {
        border-color: #94a3b8;
    }

    .date-range-container:focus-within {
        border-color: #059669;
        box-shadow: 0 0 0 0.25rem rgba(5, 150, 105, 0.15);
    }

    .date-range-icon {
        color: #94a3b8;
        font-size: 13px;
        margin-right: 8px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
    }

    .date-range-inputs {
        display: flex;
        align-items: center;
        flex: 1;
        gap: 4px;
        min-width: 0;
    }

    .date-range-input {
        width: 100%;
        border: none;
        outline: none;
        background: transparent;
        font-family: inherit;
        font-size: 12px;
        color: #334155;
        font-weight: 400;
        padding: 0;
        line-height: normal;
        cursor: pointer;
    }

    /* Ikon kalender bawaan browser: tampil rapi dan responsif saat diklik */
    .date-range-input::-webkit-calendar-picker-indicator {
        opacity: 0.6;
        cursor: pointer;
        font-size: 13px;
        margin-left: 2px;
        margin-right: 2px;
        transition: opacity 0.15s;
    }

    .date-range-input::-webkit-calendar-picker-indicator:hover {
        opacity: 1;
    }

    .date-range-input::placeholder {
        color: #94a3b8;
        font-size: 12px;
        font-weight: 400;
    }

    .date-range-separator {
        color: #94a3b8;
        font-size: 12px;
        font-weight: 500;
        padding: 0 4px;
        display: flex;
        align-items: center;
        user-select: none;
        flex-shrink: 0;
    }

    .date-range-clear-btn {
        background: transparent;
        border: none;
        color: #94a3b8;
        padding: 0;
        margin-left: 6px;
        font-size: 12px;
        cursor: pointer;
        display: none;
        align-items: center;
        justify-content: center;
        transition: color 0.15s;
    }

    .date-range-clear-btn:hover {
        color: #ef4444;
    }

    .date-range-container.has-date .date-range-clear-btn {
        display: flex;
    }

    /* 3 Kartu Ringkasan Visual Utama di Bawah Search: Open, Close, Total */
    .summary-visual-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .summary-visual-grid {
            grid-template-columns: 1fr;
        }
    }

    .visual-stat-card {
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 20px;
        padding: 26px 30px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 10px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .visual-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06);
    }

    .visual-stat-label {
        font-size: 22px;
        font-weight: 900;
        color: #0f172a;
        letter-spacing: -0.02em;
        text-transform: none;
        margin-bottom: 0;
        line-height: 1.15;
    }

    .visual-stat-value {
        font-size: 52px;
        font-weight: 900;
        line-height: 1;
        letter-spacing: -0.03em;
        font-family: inherit;
    }

    /* Grid 4 Fungsi HSSE & Grafiknya */
    .functions-grid-container {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 22px;
    }

    @media (max-width: 1200px) {
        .functions-grid-container {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 576px) {
        .functions-grid-container {
            grid-template-columns: 1fr;
        }
    }

    .function-box-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .function-box-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #f1f5f9;
    }

    .function-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 10px;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 700;
    }

    .function-pill.hsse { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .function-pill.social { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    .function-pill.security { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
    .function-pill.environment { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }

    /* Styling Header Card 4 Fungsi Safeguard */
    .function-box-title {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 16.5px;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.015em;
    }

    .function-box-icon {
        font-size: 16px;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .function-total-pill {
        display: inline-flex;
        align-items: center;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        font-size: 11.5px;
        font-weight: 600;
        padding: 2px 9px;
        border-radius: 999px;
    }

    .function-chart-wrapper {
        position: relative;
        height: 145px;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 6px;
        margin-bottom: 6px;
    }

    /* Badges */
    .status-pill-ifu {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
    }

    .status-open {
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fde68a;
    }

    .status-closed {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }

    .ifu-code-badge {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-weight: 700;
        background: #f1f5f9;
        color: #0f172a;
        padding: 2px 7px;
        border-radius: 4px;
        border: 1px solid #cbd5e1;
        font-size: 10.5px;
        display: inline-block;
    }

    .badge-overdue {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 700;
    }

    .dropdown-export-menu {
        min-width: 200px;
        border-radius: 10px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        border: 1px solid #e2e8f0;
    }
</style>
@endpush

@section('content')
<!-- 1. HEADER HALAMAN -->
<div class="page-header">
    <div class="header-title-wrapper">
        <div class="header-icon-box green">
            <i class="bi bi-clipboard-check-fill"></i>
        </div>
        <div>
            <h1 class="page-heading mb-0">
                Issue &amp; Follow Up
            </h1>
        </div>
    </div>

    <div class="header-actions d-flex flex-wrap align-items-center gap-2">
        @adminManagement
            <button type="button" class="btn-action-primary" id="btnTambahTemuan" onclick="openCreateIssueModal()">
                <i class="bi bi-plus-lg"></i> Tambah Temuan
            </button>
            <button type="button" class="btn-action-outline text-success" onclick="openManageSourcesModal()" title="Kelola Sumber Temuan">
                <i class="bi bi-tag-fill me-1"></i> Kelola Sumber Temuan
            </button>
        @endadminManagement

        <!-- Tombol Export PDF dengan Pilihan Multi-Safeguard -->
        @canExportPdf
        <button type="button" class="btn-action-outline text-dark border-dark-subtle" onclick="openExportSafeguardModal()" title="Ekspor PDF Berdasarkan Safeguard">
            <i class="bi bi-file-earmark-pdf-fill text-dark me-1"></i> Export PDF
        </button>
        @endcanExportPdf
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success py-2 px-3 small mb-3 d-flex align-items-center justify-content-between">
        <div><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger py-2 px-3 small mb-3 d-flex align-items-center justify-content-between">
        <div><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}</div>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- 2. KOMPONEN PENCARIAN & FILTER -->
<div class="filter-card mb-3">
    <form method="GET" action="{{ route('issue-follow-up.index') }}" id="ifuFilterForm">
        <div class="filter-grid-ifu-revised">
            <!-- Rentang Tanggal (Date Range Picker Menyatu & Rapi) -->
            <div class="date-range-container @if(request('start_date') || request('end_date')) has-date @endif" id="dateRangeBox">
                <div class="date-range-inputs">
                    <input type="date" 
                           name="start_date" 
                           id="filterStartDate" 
                           class="date-range-input" 
                           value="{{ request('start_date') }}" 
                           placeholder="Tanggal Mulai"
                           title="Tanggal Mulai"
                           onclick="try{this.showPicker()}catch(e){}">
                    <span class="date-range-separator">s/d</span>
                    <input type="date" 
                           name="end_date" 
                           id="filterEndDate" 
                           class="date-range-input" 
                           value="{{ request('end_date') }}" 
                           placeholder="Tanggal Akhir"
                           title="Tanggal Akhir"
                           onclick="try{this.showPicker()}catch(e){}">
                </div>
                <button type="button" 
                        class="date-range-clear-btn" 
                        id="btnClearDateRange" 
                        onclick="clearDateRange()" 
                        title="Hapus filter tanggal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <!-- Filter: Fungsi Safeguard -->
            <div>
                <select name="hsse_function" id="filterHsseFunction" class="form-select form-select-filter">
                    <option value="">Semua Safeguard</option>
                    @foreach($functions as $funcCode => $funcLabel)
                        <option value="{{ $funcCode }}" @selected($activeFunction === $funcCode)>{{ $funcLabel }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter: Status (All, Open, Close) - Requirement 11 -->
            <div>
                <select name="status" id="filterStatus" class="form-select form-select-filter">
                    <option value="" @selected(empty($activeStatus) || strcasecmp($activeStatus, 'All') === 0)>All (Semua Status)</option>
                    <option value="Open" @selected(strcasecmp($activeStatus, 'Open') === 0)>Open</option>
                    <option value="Close" @selected(strcasecmp($activeStatus, 'Close') === 0 || strcasecmp($activeStatus, 'Closed') === 0)>Close</option>
                </select>
            </div>

            <!-- Filter: Sumber Temuan (Requirement 1) -->
            <div>
                <select name="source" id="filterSource" class="form-select form-select-filter">
                    <option value="">Semua Sumber Temuan</option>
                    @foreach($sources as $src)
                        <option value="{{ $src }}" @selected(request('source') === $src || request('category') === $src)>{{ $src }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Pencarian Kata Kunci -->
            <div class="filter-input-wrap">
                <i class="bi bi-search filter-icon-addon"></i>
                <input type="search" name="search" id="filterSearch" class="form-control form-control-filter" value="{{ request('search') }}" placeholder="Cari judul, deskripsi, kode temuan, lokasi...">
            </div>

            <!-- Tombol Aksi -->
            <div class="d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-success px-3">Filter</button>
                <a href="{{ route('issue-follow-up.index') }}" class="btn btn-sm btn-outline-secondary px-2">Reset</a>
            </div>
        </div>
    </form>
</div>

<!-- 3. RINGKASAN VISUAL TEPAT DI BAWAH PENCARIAN (OPEN, CLOSE, TOTAL) - Minimalist Typographic Layout -->
<div class="summary-visual-grid">
    <!-- Kartu Open -->
    <div class="visual-stat-card">
        <div class="visual-stat-label">Open</div>
        <div class="visual-stat-value text-danger">{{ number_format($stats['open_issues'], 0, ',', '.') }}</div>
    </div>

    <!-- Kartu Close -->
    <div class="visual-stat-card">
        <div class="visual-stat-label">Close</div>
        <div class="visual-stat-value text-success">{{ number_format($stats['closed_issues'], 0, ',', '.') }}</div>
    </div>

    <!-- Kartu Total -->
    <div class="visual-stat-card">
        <div class="visual-stat-label">Total Temuan</div>
        <div class="visual-stat-value text-primary">{{ number_format($stats['total_issues'], 0, ',', '.') }}</div>
    </div>
</div>

<!-- 4. GRAFIK PER MASING-MASING FUNGSI SAFEGUARD (HEALTH & SAFETY, SECURITY, ENVIRONMENT, SOCIAL) -->
<div class="functions-grid-container">
    <!-- Fungsi 1: Health & Safety -->
    <div class="function-box-card">
        <div>
            <div class="function-box-header">
                <span class="function-box-title">
                    Health &amp; Safety
                </span>
                <span class="function-total-pill">Total: {{ $functionCharts['Health & Safety']['total'] ?? 0 }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center px-1 mb-2" style="font-size: 11.5px;">
                <span class="text-danger fw-bold">Open: {{ $functionCharts['Health & Safety']['open'] ?? 0 }}</span>
                <span class="text-success fw-bold">Close: {{ $functionCharts['Health & Safety']['close'] ?? 0 }}</span>
            </div>
            <div class="function-chart-wrapper">
                <canvas id="chartFunctionHealthSafety"></canvas>
            </div>
        </div>
        <div class="pt-2 border-top mt-2 text-center">
            <a href="{{ route('issue-follow-up.export', array_merge(request()->query(), ['hsse_function' => 'Health & Safety'])) }}" class="text-decoration-none small text-dark fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11.5px;">
                <i class="bi bi-file-earmark-pdf-fill text-dark"></i> Export PDF
            </a>
        </div>
    </div>

    <!-- Fungsi 2: Security -->
    <div class="function-box-card">
        <div>
            <div class="function-box-header">
                <span class="function-box-title">
                    Security
                </span>
                <span class="function-total-pill">Total: {{ $functionCharts['Security']['total'] ?? 0 }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center px-1 mb-2" style="font-size: 11.5px;">
                <span class="text-danger fw-bold">Open: {{ $functionCharts['Security']['open'] ?? 0 }}</span>
                <span class="text-success fw-bold">Close: {{ $functionCharts['Security']['close'] ?? 0 }}</span>
            </div>
            <div class="function-chart-wrapper">
                <canvas id="chartFunctionSecurity"></canvas>
            </div>
        </div>
        <div class="pt-2 border-top mt-2 text-center">
            <a href="{{ route('issue-follow-up.export', array_merge(request()->query(), ['hsse_function' => 'Security'])) }}" class="text-decoration-none small text-dark fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11.5px;">
                <i class="bi bi-file-earmark-pdf-fill text-dark"></i> Export PDF
            </a>
        </div>
    </div>

    <!-- Fungsi 3: Environment -->
    <div class="function-box-card">
        <div>
            <div class="function-box-header">
                <span class="function-box-title">
                    Environment
                </span>
                <span class="function-total-pill">Total: {{ $functionCharts['Environment']['total'] ?? 0 }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center px-1 mb-2" style="font-size: 11.5px;">
                <span class="text-danger fw-bold">Open: {{ $functionCharts['Environment']['open'] ?? 0 }}</span>
                <span class="text-success fw-bold">Close: {{ $functionCharts['Environment']['close'] ?? 0 }}</span>
            </div>
            <div class="function-chart-wrapper">
                <canvas id="chartFunctionEnvironment"></canvas>
            </div>
        </div>
        <div class="pt-2 border-top mt-2 text-center">
            <a href="{{ route('issue-follow-up.export', array_merge(request()->query(), ['hsse_function' => 'Environment'])) }}" class="text-decoration-none small text-dark fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11.5px;">
                <i class="bi bi-file-earmark-pdf-fill text-dark"></i> Export PDF
            </a>
        </div>
    </div>

    <!-- Fungsi 4: Social -->
    <div class="function-box-card">
        <div>
            <div class="function-box-header">
                <span class="function-box-title">
                    Social
                </span>
                <span class="function-total-pill">Total: {{ $functionCharts['Social']['total'] ?? 0 }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center px-1 mb-2" style="font-size: 11.5px;">
                <span class="text-danger fw-bold">Open: {{ $functionCharts['Social']['open'] ?? 0 }}</span>
                <span class="text-success fw-bold">Close: {{ $functionCharts['Social']['close'] ?? 0 }}</span>
            </div>
            <div class="function-chart-wrapper">
                <canvas id="chartFunctionSocial"></canvas>
            </div>
        </div>
        <div class="pt-2 border-top mt-2 text-center">
            <a href="{{ route('issue-follow-up.export', array_merge(request()->query(), ['hsse_function' => 'Social'])) }}" class="text-decoration-none small text-dark fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11.5px;">
                <i class="bi bi-file-earmark-pdf-fill text-dark"></i> Export PDF
            </a>
        </div>
    </div>
</div>

<!-- 5. TABEL DAFTAR TEMUAN (DENGAN FIELD YANG TELAH DIREVISI) - Requirement 16 -->
<div class="table-card">
    <div class="table-topbar">
        <div>
            <h2 class="table-section-title mb-0">
                Daftar Temuan &amp; Tindak Lanjut
                <span class="badge bg-secondary-subtle text-secondary ms-2" style="font-size:10.5px;">{{ $issues->total() }} Data</span>
            </h2>
        </div>

        <div class="table-controls">
            <form method="GET" action="{{ route('issue-follow-up.index') }}" class="d-flex align-items-center gap-2">
                @foreach(request()->except(['sort', 'per_page', 'page']) as $k => $v)
                    @if(!is_array($v))
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endif
                @endforeach
                <select name="sort" class="form-select form-select-sm" style="width:165px; font-size:11.5px" onchange="this.form.submit()">
                    <option value="date-desc" @selected(request('sort', 'date-desc') === 'date-desc')>Terbaru (Default)</option>
                    <option value="date-asc" @selected(request('sort') === 'date-asc')>Terlama</option>
                    <option value="status" @selected(request('sort') === 'status')>Status</option>
                </select>
            </form>
        </div>
    </div>

    <div class="table-responsive-custom">
        <table class="table-custom" id="issueFollowUpTable">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">No</th>
                    <th style="width: 105px;">Tanggal</th>
                    <th style="width: 130px;">Fungsi Safeguard</th>
                    <th style="width: 160px;">Sumber Temuan</th>
                    <th style="width: 220px;">Judul Temuan</th>
                    <th>Deskripsi Temuan</th>
                    <th style="width: 110px; text-align: center;">Status</th>
                    <th style="width: 100px; text-align: center;">Dokumentasi</th>
                    <th style="width: 110px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody id="issueTableBody">
                @forelse($issues as $index => $issue)
                    @php
                        $isClose = in_array($issue->status, ['Close', 'Closed'], true);
                        $statusBadge = $isClose ? 'status-closed' : 'status-open';
                        $statusDisplay = $isClose ? 'Close' : 'Open';
                        $docCount = $issue->documentations ? $issue->documentations->count() : 0;
                        $closeDate = $issue->closed_date ?? $issue->due_date;
                    @endphp
                    <tr data-id="{{ $issue->id }}">
                        <td style="text-align: center; color: #64748b;">{{ $issues->firstItem() + $index }}</td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $issue->reported_date ? $issue->reported_date->format('d/m/Y') : '-' }}</div>
                        </td>
                        <td>
                            <span class="function-pill {{ strtolower($issue->function_label) }}">{{ $issue->function_label }}</span>
                        </td>
                        <td>
                            <span class="fw-semibold text-dark" style="font-size: 12px;">{{ $issue->source_label }}</span>
                            @if($issue->location)
                                <div class="text-muted" style="font-size: 10.5px;"><i class="bi bi-geo-alt"></i> {{ $issue->location }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $issue->title }}</div>
                        </td>
                        <td>
                            <div class="text-secondary" style="font-size: 11.5px; line-height: 1.4;">
                                {{ \Illuminate\Support\Str::limit($issue->description, 110) }}
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <div class="d-inline-flex flex-column align-items-center gap-1">
                                <span class="status-pill-ifu {{ $statusBadge }}">{{ $statusDisplay }}</span>
                                @if($isClose && $closeDate)
                                    <span class="text-muted fw-semibold" style="font-size: 10.5px; line-height: 1.2;">
                                        {{ \Carbon\Carbon::parse($closeDate)->format('d/m/Y') }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge {{ $docCount > 0 ? 'bg-info-subtle text-info-emphasis border border-info-subtle' : 'bg-light text-muted border' }}">
                                <i class="bi bi-paperclip me-1"></i>{{ $docCount }} File
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <div class="d-inline-flex align-items-center justify-content-center gap-1 flex-nowrap">
                                <!-- Detail (sekaligus ubah status Close/Open) -->
                                <button type="button" class="btn-action-icon btn-action-view" onclick="openIssueDetailModal({{ $issue->id }})" title="Lihat Detail &amp; Status">
                                    <i class="bi bi-eye"></i>
                                </button>

                                @adminManagement
                                    <!-- Edit -->
                                    <button type="button" class="btn-action-icon btn-action-edit" onclick="openEditIssueModal({{ $issue->id }})" title="Edit Temuan">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    <!-- Hapus -->
                                    <button type="button" class="btn-action-icon btn-action-delete" onclick="deleteIssuePrompt({{ $issue->id }}, '{{ addslashes($issue->title) }}', '{{ $issue->issue_code }}')" title="Hapus Temuan">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endadminManagement
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="p-4 text-center">
                            <div class="py-4">
                                <div class="mx-auto mb-2 d-flex align-items-center justify-content-center rounded-circle bg-light text-secondary" style="width:56px;height:56px;font-size:24px;">
                                    <i class="bi bi-clipboard-check"></i>
                                </div>
                                <h6 class="fw-bold text-secondary mb-1">Belum ada data temuan yang sesuai dengan filter</h6>
                                <p class="text-muted small mb-3">Sesuaikan filter atau klik tombol <strong>+ Tambah Temuan</strong> untuk menambahkan rekaman baru.</p>
                                @adminManagement
                                    <button type="button" class="btn-action-primary mx-auto" onclick="openCreateIssueModal()">
                                        <i class="bi bi-plus-lg"></i> Tambah Temuan
                                    </button>
                                @endadminManagement
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($issues->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
            <div class="text-muted small">
                Menampilkan {{ $issues->firstItem() }}–{{ $issues->lastItem() }} dari {{ $issues->total() }} temuan
            </div>
            <div>
                {{ $issues->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>

<!-- MODAL 1: TAMBAH / EDIT TEMUAN -->
<div class="modal fade" id="issueFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="issueForm" method="POST" action="{{ route('issue-follow-up.store') }}" enctype="multipart/form-data" onsubmit="saveIssueForm(event)">
                @csrf
                <input type="hidden" id="formIssueId" name="id" value="">
                <div class="modal-header border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="header-icon-box green" style="width:34px; height:34px; font-size:16px;">
                            <i class="bi bi-clipboard-plus"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold" id="issueFormModalTitle">Tambah Temuan Baru</h5>
                            <div class="text-muted" style="font-size:11px;">Pilih fungsi safeguard, sumber temuan, dan rincian temuan</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:12px;">Tanggal Temuan <span class="text-danger">*</span></label>
                            <input type="date" name="reported_date" id="formReportedDate" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:12px;">Fungsi Safeguard <span class="text-danger">*</span></label>
                            <select name="hsse_division" id="formHsseDivision" class="form-select form-select-sm" required>
                                <option value="">-- Pilih Fungsi Safeguard --</option>
                                @foreach($functions as $funcCode => $funcLabel)
                                    <option value="{{ $funcCode }}">{{ $funcLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label fw-semibold mb-0" style="font-size:12px;">Sumber Temuan <span class="text-danger">*</span></label>
                                <button type="button" class="btn btn-link btn-sm p-0 text-success text-decoration-none" onclick="openManageSourcesModal()" style="font-size: 11px;">
                                    + Kelola
                                </button>
                            </div>
                            <select name="category" id="formCategory" class="form-select form-select-sm mt-1" required>
                                <option value="">-- Pilih Sumber Temuan --</option>
                                @foreach($sources as $src)
                                    <option value="{{ $src }}">{{ $src }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-semibold" style="font-size:12px;">Judul Temuan <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="formTitle" class="form-control form-control-sm" placeholder="Contoh: Temuan kebocoran pipa / kabel listrik tidak standar" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:12px;">Lokasi Spesifik <span class="text-danger">*</span></label>
                            <input type="text" name="location" id="formLocation" class="form-control form-control-sm" placeholder="Contoh: Wellpad 9 / Power Plant" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:12px;">Deskripsi Temuan <span class="text-danger">*</span></label>
                            <textarea name="description" id="formDescription" class="form-control form-control-sm" rows="3" placeholder="Jelaskan kondisi temuan secara lengkap..." required></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:12px;">Status Temuan <span class="text-danger">*</span></label>
                            <select name="status" id="formStatus" class="form-select form-select-sm" required onchange="handleModalStatusChange(this.value)">
                                <option value="Open">Open</option>
                                <option value="Close">Close</option>
                            </select>
                            <div class="form-text small" id="formStatusHint">Status <strong>Open</strong>: tanggal close terkunci/tidak dapat diisi.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:12px;">
                                Tanggal Close
                                <span id="formDueDateBadge" class="badge bg-secondary ms-1" style="font-size:10px;">Dinonaktifkan (Open)</span>
                            </label>
                            <input type="date" name="due_date" id="formDueDate" class="form-control form-control-sm ifu-control-disabled" disabled readonly>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:12px;">Unggah File Dokumentasi (Bisa Pilih Banyak Sekaligus, Maks 15 MB/file)</label>
                            <input type="file" name="documentation_files[]" id="formDocumentationFiles" class="form-control form-control-sm" multiple accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.xls,.xlsx" onchange="handleSelectedDocumentationFiles(this)">
                            <div class="form-text small text-muted">Format didukung: JPG, PNG, WEBP, PDF, DOC, DOCX, XLS, XLSX (Maks. 15 MB per file).</div>
                            
                            {{-- Container Preview File yang Dipilih --}}
                            <div id="selectedFilesPreviewContainer" class="mt-2 d-none">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold small text-dark" style="font-size:11.5px;">File Dipilih (<span id="selectedFilesCount">0</span>):</span>
                                    <button type="button" class="btn btn-link btn-sm text-danger text-decoration-none p-0" style="font-size:11px;" onclick="clearSelectedDocumentationFiles()">
                                        <i class="bi bi-trash me-1"></i>Reset Semua File
                                    </button>
                                </div>
                                <div class="d-flex flex-column gap-1" id="selectedFilesList" style="max-height: 180px; overflow-y: auto;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-action-primary btn-sm px-4" id="btnSubmitIssue">
                        <i class="bi bi-save me-1"></i> Simpan Temuan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 3: DETAIL TEMUAN & DOKUMENTASI LENGKAP -->
<div class="modal fade" id="issueDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="header-icon-box green" style="width:34px; height:34px; font-size:16px;">
                        <i class="bi bi-clipboard-check-fill"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold" id="detailModalTitle">Detail Temuan &amp; Dokumentasi</h5>
                        <div class="text-muted" style="font-size:11px;" id="detailModalSubtitle">Informasi lengkap dan file pendukung</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="issueDetailModalBody">
                <div class="text-center py-4 text-muted">Memuat detail temuan...</div>
            </div>
            <div class="modal-footer border-top bg-light justify-content-between">
                <a href="#" id="detailFullPageLink" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Buka Halaman Penuh
                </a>
                <div class="d-flex align-items-center gap-2">
                    <span id="detailStatusActionContainer"></span>
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 4: MANAJEMEN MASTER DATA SUMBER TEMUAN -->
<div class="modal fade" id="modalManageSources" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="header-icon-box green" style="width:34px; height:34px; font-size:16px;">
                        <i class="bi bi-tags-fill"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Kelola Sumber Temuan</h5>
                        <div class="text-muted" style="font-size:11px;">Tambah atau hapus master opsi Sumber Temuan</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Form Tambah Cepat --}}
                <div class="mb-3 p-3 bg-light rounded border">
                    <label class="form-label fw-bold mb-2" style="font-size:12px;">+ Tambah Sumber Temuan Baru</label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="inputNewSourceName" class="form-control" placeholder="Ketik nama sumber temuan...">
                        <button type="button" class="btn btn-success" onclick="submitAddNewSource()">
                            <i class="bi bi-plus-lg me-1"></i> Simpan
                        </button>
                    </div>
                </div>

                <div class="fw-bold text-dark small mb-2 d-flex justify-content-between align-items-center">
                    <span>Daftar Sumber Temuan Saat Ini</span>
                    <span class="badge bg-secondary-subtle text-secondary" id="countActiveSources">{{ count($sources) }} Opsi</span>
                </div>

                <div class="list-group" id="listManageSources" style="max-height: 280px; overflow-y: auto;">
                    @foreach($sources as $src)
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3" data-source-item="{{ $src }}">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-tag text-success"></i>
                                <span class="fw-semibold text-dark" style="font-size: 12.5px;">{{ $src }}</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 11px;" onclick="deleteSourceItem('{{ addslashes($src) }}')" title="Hapus sumber temuan ini">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer border-top bg-light">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 5: EKSPOR PDF BERDASARKAN SAFEGUARD (MULTI-SELECT) -->
<div class="modal fade" id="modalExportPdfSafeguard" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formExportPdfSafeguard" method="GET" action="{{ route('issue-follow-up.export') }}">
                @foreach(request()->except(['hsse_functions', 'hsse_function', 'page']) as $k => $v)
                    @if(!is_array($v))
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endif
                @endforeach
                <div class="modal-header border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="header-icon-box text-dark bg-secondary-subtle border border-secondary-subtle" style="width:34px; height:34px; font-size:16px;">
                            <i class="bi bi-file-earmark-pdf-fill"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0">Export PDF Berdasarkan Safeguard</h5>
                            <div class="text-muted" style="font-size:11px;">Pilih satu atau lebih fungsi safeguard yang ingin diekspor</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
                        <label class="form-check-label fw-bold text-dark small" for="cbExportSelectAll">
                            Pilih Semua Fungsi Safeguard
                        </label>
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" id="cbExportSelectAll" onchange="toggleExportSelectAll(this.checked)" checked>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-2" id="exportSafeguardCheckboxGroup">
                        <div class="form-check p-2 rounded border bg-light d-flex align-items-center gap-2">
                            <input class="form-check-input ms-1 cb-export-func" type="checkbox" name="hsse_functions[]" value="Health & Safety" id="exportFuncHS" checked onchange="checkExportSelectAllState()">
                            <label class="form-check-label fw-semibold text-primary small d-flex align-items-center gap-2 mb-0" for="exportFuncHS">
                                <span class="badge bg-primary text-white" style="width:20px;height:20px;display:inline-grid;place-items:center;">H</span> Health &amp; Safety
                            </label>
                        </div>
                        <div class="form-check p-2 rounded border bg-light d-flex align-items-center gap-2">
                            <input class="form-check-input ms-1 cb-export-func" type="checkbox" name="hsse_functions[]" value="Security" id="exportFuncSec" checked onchange="checkExportSelectAllState()">
                            <label class="form-check-label fw-semibold text-warning-emphasis small d-flex align-items-center gap-2 mb-0" for="exportFuncSec">
                                <span class="badge bg-warning text-dark" style="width:20px;height:20px;display:inline-grid;place-items:center;">S</span> Security
                            </label>
                        </div>
                        <div class="form-check p-2 rounded border bg-light d-flex align-items-center gap-2">
                            <input class="form-check-input ms-1 cb-export-func" type="checkbox" name="hsse_functions[]" value="Environment" id="exportFuncEnv" checked onchange="checkExportSelectAllState()">
                            <label class="form-check-label fw-semibold text-success small d-flex align-items-center gap-2 mb-0" for="exportFuncEnv">
                                <span class="badge bg-success text-white" style="width:20px;height:20px;display:inline-grid;place-items:center;">E</span> Environment
                            </label>
                        </div>
                        <div class="form-check p-2 rounded border bg-light d-flex align-items-center gap-2">
                            <input class="form-check-input ms-1 cb-export-func" type="checkbox" name="hsse_functions[]" value="Social" id="exportFuncSoc" checked onchange="checkExportSelectAllState()">
                            <label class="form-check-label fw-semibold text-danger small d-flex align-items-center gap-2 mb-0" for="exportFuncSoc">
                                <span class="badge bg-danger text-white" style="width:20px;height:20px;display:inline-grid;place-items:center;">S</span> Social
                            </label>
                        </div>
                    </div>
                    <div class="form-text small text-muted mt-3">
                        <i class="bi bi-info-circle me-1"></i> File PDF akan menyertakan foto dokumentasi dan mengikuti filter pencarian / tanggal yang sedang aktif.
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-dark btn-sm px-4">
                        <i class="bi bi-file-earmark-pdf-fill me-1"></i> Unduh PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const IFU_BASE_URL = "{{ url('/issue-follow-up') }}";

    // Data grafik untuk 4 fungsi HSSE (Requirement 18)
    const functionChartData = @json($functionCharts);

    document.addEventListener('DOMContentLoaded', function () {
        initFunctionCharts();
    });

    function initFunctionCharts() {
        if (typeof Chart === 'undefined') return;

        const funcConfigs = [
            { key: 'Health & Safety', canvasId: 'chartFunctionHealthSafety' },
            { key: 'Security', canvasId: 'chartFunctionSecurity' },
            { key: 'Environment', canvasId: 'chartFunctionEnvironment' },
            { key: 'Social', canvasId: 'chartFunctionSocial' },
        ];

        funcConfigs.forEach(item => {
            const canvas = document.getElementById(item.canvasId);
            if (!canvas) return;

            const fData = functionChartData[item.key] || { open: 0, close: 0, total: 0 };
            const hasData = (fData.open > 0 || fData.close > 0);

            const chartData = hasData
                ? [fData.open, fData.close]
                : [1]; // placeholder kosong jika 0 temuan

            const bgColors = hasData
                ? ['#ef4444', '#10b981'] // Merah (Open), Hijau (Close)
                : ['#e2e8f0']; // Abu-abu muda jika belum ada data

            const borderColors = hasData
                ? ['#ffffff', '#ffffff']
                : ['#cbd5e1'];

            new Chart(canvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: hasData ? ['Open', 'Close'] : ['Belum Ada Data'],
                    datasets: [{
                        data: chartData,
                        backgroundColor: bgColors,
                        borderColor: borderColors,
                        borderWidth: 2,
                        hoverOffset: hasData ? 4 : 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                boxWidth: 10,
                                boxHeight: 10,
                                padding: 8,
                                font: { size: 10.5, weight: '600' }
                            }
                        },
                        tooltip: {
                            enabled: hasData,
                            callbacks: {
                                label: (context) => ` ${context.label}: ${context.raw} temuan`
                            }
                        }
                    }
                }
            });
        });
    }

    function handleModalStatusChange(status) {
        const dueInput = document.getElementById('formDueDate');
        const dueBadge = document.getElementById('formDueDateBadge');
        const hint = document.getElementById('formStatusHint');

        if (status === 'Close') {
            dueInput.disabled = false;
            dueInput.readOnly = false;
            dueInput.classList.remove('ifu-control-disabled');
            dueBadge.className = 'badge bg-success ms-1';
            dueBadge.textContent = 'Dapat Diisi (Close)';
            hint.innerHTML = 'Status <strong>Close</strong>: silakan tentukan Tanggal Close.';
            if (!dueInput.value) {
                dueInput.value = "{{ date('Y-m-d') }}";
            }
        } else {
            // Status Open: tidak dapat diedit atau diisi
            dueInput.value = '';
            dueInput.disabled = true;
            dueInput.readOnly = true;
            dueInput.classList.add('ifu-control-disabled');
            dueBadge.className = 'badge bg-secondary ms-1';
            dueBadge.textContent = 'Dinonaktifkan (Open)';
            hint.innerHTML = 'Status <strong>Open</strong>: field Tanggal Close terkunci/tidak dapat diisi.';
        }
    }

    // Array penampung file dokumentasi yang dipilih (Multi-files upload)
    let selectedDocumentationFiles = [];

    function handleSelectedDocumentationFiles(input) {
        if (!input.files || input.files.length === 0) return;

        const allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
        const maxBytes = 15 * 1024 * 1024; // 15 MB per file
        const newFiles = Array.from(input.files);
        let errorMessages = [];

        newFiles.forEach(file => {
            const ext = file.name.split('.').pop().toLowerCase();
            if (!allowedExtensions.includes(ext)) {
                errorMessages.push(`File <strong>${escapeHtml(file.name)}</strong>: Format .${ext} tidak diizinkan.`);
                return;
            }
            if (file.size > maxBytes) {
                const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                errorMessages.push(`File <strong>${escapeHtml(file.name)}</strong>: Ukuran ${sizeMB} MB melebihi batas 15 MB.`);
                return;
            }

            // Hindari duplikat file dengan nama dan ukuran yang sama
            const isDuplicate = selectedDocumentationFiles.some(f => f.name === file.name && f.size === file.size);
            if (!isDuplicate) {
                selectedDocumentationFiles.push(file);
            }
        });

        if (errorMessages.length > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Beberapa File Tidak Memenuhi Syarat',
                html: errorMessages.join('<br>'),
                confirmButtonColor: '#059669'
            });
        }

        renderSelectedDocumentationFiles();
        syncFileInput();
    }

    function removeSelectedDocumentationFile(index) {
        if (index >= 0 && index < selectedDocumentationFiles.length) {
            selectedDocumentationFiles.splice(index, 1);
            renderSelectedDocumentationFiles();
            syncFileInput();
        }
    }

    function clearSelectedDocumentationFiles() {
        selectedDocumentationFiles = [];
        renderSelectedDocumentationFiles();
        syncFileInput();
    }

    function syncFileInput() {
        const input = document.getElementById('formDocumentationFiles');
        if (!input) return;

        try {
            const dt = new DataTransfer();
            selectedDocumentationFiles.forEach(file => dt.items.add(file));
            input.files = dt.files;
        } catch (e) {
            // Fallback untuk browser yang tidak mendukung manipulasi DataTransfer
        }
    }

    function renderSelectedDocumentationFiles() {
        const container = document.getElementById('selectedFilesPreviewContainer');
        const list = document.getElementById('selectedFilesList');
        const countSpan = document.getElementById('selectedFilesCount');

        if (!container || !list || !countSpan) return;

        countSpan.textContent = selectedDocumentationFiles.length;

        if (selectedDocumentationFiles.length === 0) {
            container.classList.add('d-none');
            list.innerHTML = '';
            return;
        }

        container.classList.remove('d-none');
        list.innerHTML = selectedDocumentationFiles.map((file, idx) => {
            const ext = file.name.split('.').pop().toLowerCase();
            const isImg = ['jpg', 'jpeg', 'png', 'webp'].includes(ext);
            const iconClass = isImg ? 'bi-file-earmark-image text-primary' : 'bi-file-earmark-text text-secondary';
            const sizeStr = file.size > 1048576 
                ? (file.size / 1048576).toFixed(2) + ' MB' 
                : (file.size / 1024).toFixed(1) + ' KB';

            return `
                <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light border" style="font-size:11.5px;">
                    <div class="d-flex align-items-center gap-2 overflow-hidden text-truncate me-2">
                        <i class="bi ${iconClass} fs-6"></i>
                        <div class="text-truncate">
                            <span class="fw-semibold text-dark text-truncate d-block">${escapeHtml(file.name)}</span>
                            <span class="text-muted" style="font-size:10.5px;">${sizeStr}</span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 flex-shrink-0" onclick="removeSelectedDocumentationFile(${idx})" title="Batalkan file ini">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            `;
        }).join('');
    }

    function openCreateIssueModal() {
        const form = document.getElementById('issueForm');
        if (form) form.reset();

        clearSelectedDocumentationFiles();

        document.getElementById('formIssueId').value = '';
        document.getElementById('formReportedDate').value = "{{ date('Y-m-d') }}";
        
        const activeFunc = "{{ $activeFunction }}";
        if (activeFunc && document.getElementById('formHsseDivision')) {
            document.getElementById('formHsseDivision').value = activeFunc;
        }

        document.getElementById('formStatus').value = 'Open';
        handleModalStatusChange('Open');

        document.getElementById('issueFormModalTitle').textContent = 'Tambah Temuan Baru';
        bootstrap.Modal.getOrCreateInstance(document.getElementById('issueFormModal')).show();
    }

    async function openEditIssueModal(id) {
        clearSelectedDocumentationFiles();
        try {
            const res = await fetch(`${IFU_BASE_URL}/${id}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (!data.success) {
                Swal.fire('Error', data.message || 'Data tidak ditemukan.', 'error');
                return;
            }

            const i = data.issue;
            document.getElementById('formIssueId').value = i.id;
            document.getElementById('formReportedDate').value = i.reported_date ? String(i.reported_date).substring(0, 10) : '';
            
            if (document.getElementById('formHsseDivision')) {
                document.getElementById('formHsseDivision').value = i.function_label || i.hsse_division || 'Health & Safety';
            }
            document.getElementById('formTitle').value = i.title || '';
            document.getElementById('formCategory').value = i.category || '';
            document.getElementById('formLocation').value = i.location || '';
            document.getElementById('formDescription').value = i.description || '';
            
            const currentStat = (i.status === 'Close' || i.status === 'Closed') ? 'Close' : 'Open';
            document.getElementById('formStatus').value = currentStat;
            handleModalStatusChange(currentStat);

            if (currentStat === 'Close' && (i.closed_date || i.due_date)) {
                const dateVal = i.closed_date || i.due_date;
                document.getElementById('formDueDate').value = String(dateVal).substring(0, 10);
            }

            document.getElementById('issueFormModalTitle').textContent = `Edit Temuan [${i.issue_code}]`;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('issueFormModal')).show();
        } catch (err) {
            Swal.fire('Error', 'Gagal memuat data temuan.', 'error');
        }
    }

    async function saveIssueForm(e) {
        e.preventDefault();
        const id = document.getElementById('formIssueId').value;
        const isEdit = Boolean(id);
        const url = isEdit ? `${IFU_BASE_URL}/${id}` : IFU_BASE_URL;

        const form = document.getElementById('issueForm');
        const formData = new FormData(form);
        if (isEdit) {
            formData.append('_method', 'PUT');
        }

        // Pastikan file yang berada di selectedDocumentationFiles ter-append ke documentation_files[]
        formData.delete('documentation_files[]');
        selectedDocumentationFiles.forEach(file => {
            formData.append('documentation_files[]', file);
        });

        const btn = document.getElementById('btnSubmitIssue');
        btn.disabled = true;

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const body = await res.json();
            btn.disabled = false;

            if (!res.ok || !body.success) {
                let msg = body.message || 'Gagal menyimpan data temuan.';
                if (body.errors) {
                    msg = Object.values(body.errors).flat().join('<br>');
                }
                Swal.fire({ icon: 'error', title: 'Validasi Gagal', html: msg });
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('issueFormModal'))?.hide();
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: body.message,
                timer: 1300,
                showConfirmButton: false
            }).then(() => window.location.reload());
        } catch (err) {
            btn.disabled = false;
            Swal.fire('Error', 'Terjadi kesalahan saat menyimpan temuan.', 'error');
        }
    }

    function closeIssuePrompt(id, title) {
        const todayStr = new Date().toISOString().split('T')[0];
        Swal.fire({
            title: 'Tutup Temuan (Tandai Close)',
            html: `
                <div class="text-start mb-2" style="font-size:13px;">
                    Tandai temuan <strong>${escapeHtml(title)}</strong> sebagai <strong>Close</strong>.
                </div>
                <div class="text-start mb-3">
                    <label class="form-label fw-bold small mb-1">Tanggal Close <span class="text-danger">*</span></label>
                    <input type="date" id="swalCloseDate" class="form-control form-control-sm" value="${todayStr}">
                </div>
                <div class="text-start mb-1">
                    <label class="form-label fw-bold small mb-1">Catatan Penyelesaian / Penutupan</label>
                    <textarea id="swalCloseNote" class="form-control form-control-sm" rows="3" placeholder="Catatan penyelesaian / penutupan temuan...">Temuan telah diverifikasi dan diselesaikan (Close).</textarea>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Simpan Status Close',
            cancelButtonText: 'Batal',
            focusConfirm: false,
            preConfirm: () => {
                const dateVal = document.getElementById('swalCloseDate')?.value;
                const noteVal = document.getElementById('swalCloseNote')?.value;
                if (!dateVal) {
                    Swal.showValidationMessage('Tanggal close wajib ditentukan.');
                    return false;
                }
                return {
                    closed_date: dateVal,
                    note: noteVal || 'Temuan diselesaikan dan ditutup (Close).'
                };
            }
        }).then(async result => {
            if (!result.isConfirmed) return;

            const res = await fetch(`${IFU_BASE_URL}/${id}/close`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    closed_date: result.value.closed_date,
                    note: result.value.note
                })
            });
            const body = await res.json();
            if (res.ok && body.success) {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: body.message, timer: 1200, showConfirmButton: false })
                    .then(() => window.location.reload());
            } else {
                Swal.fire('Error', body.message || 'Gagal menutup temuan.', 'error');
            }
        });
    }

    function reopenIssuePrompt(id, title) {
        Swal.fire({
            title: 'Buka Kembali Temuan (Open)?',
            html: `
                <div class="text-start mb-2" style="font-size:13px;">
                    Buka kembali temuan <strong>${escapeHtml(title)}</strong> menjadi status <strong>Open</strong>?
                </div>
                <div class="text-start mb-1">
                    <label class="form-label fw-bold small mb-1">Alasan Membuka Kembali <span class="text-danger">*</span></label>
                    <textarea id="swalReopenNote" class="form-control form-control-sm" rows="3" placeholder="Tuliskan alasan membuka kembali temuan ini (wajib)..."></textarea>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d97706',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-arrow-counterclockwise me-1"></i> Ya, Buka Kembali (Open)',
            cancelButtonText: 'Batal',
            focusConfirm: false,
            preConfirm: () => {
                const noteVal = document.getElementById('swalReopenNote')?.value?.trim();
                if (!noteVal) {
                    Swal.showValidationMessage('Alasan membuka kembali temuan wajib diisi.');
                    return false;
                }
                return { note: noteVal };
            }
        }).then(async result => {
            if (!result.isConfirmed) return;

            const res = await fetch(`${IFU_BASE_URL}/${id}/reopen`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ note: result.value.note })
            });
            const body = await res.json();
            if (res.ok && body.success) {
                Swal.fire({ icon: 'success', title: 'Dibuka Kembali', text: body.message, timer: 1200, showConfirmButton: false })
                    .then(() => window.location.reload());
            } else {
                Swal.fire('Error', body.message || 'Gagal membuka kembali temuan.', 'error');
            }
        });
    }

    function deleteIssuePrompt(id, title, code) {
        Swal.fire({
            title: 'Hapus Temuan?',
            html: `Apakah Anda yakin ingin menghapus temuan <strong>[${escapeHtml(code)}] ${escapeHtml(title)}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(async result => {
            if (!result.isConfirmed) return;

            const res = await fetch(`${IFU_BASE_URL}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            const body = await res.json();
            if (res.ok && body.success) {
                Swal.fire({ icon: 'success', title: 'Terhapus', text: body.message, timer: 1200, showConfirmButton: false })
                    .then(() => window.location.reload());
            } else {
                Swal.fire('Error', body.message || 'Gagal menghapus temuan.', 'error');
            }
        });
    }

    async function openIssueDetailModal(id) {
        const bodyEl = document.getElementById('issueDetailModalBody');
        bodyEl.innerHTML = '<div class="text-center py-4 text-muted"><i class="spinner-border spinner-border-sm me-2"></i>Memuat detail temuan...</div>';
        
        document.getElementById('detailFullPageLink').href = `${IFU_BASE_URL}/${id}`;

        bootstrap.Modal.getOrCreateInstance(document.getElementById('issueDetailModal')).show();

        try {
            const res = await fetch(`${IFU_BASE_URL}/${id}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (!data.success) {
                bodyEl.innerHTML = '<div class="alert alert-danger">Gagal memuat detail temuan.</div>';
                return;
            }

            const i = data.issue;
            const isClose = (i.status === 'Close' || i.status === 'Closed');
            const statBadgeClass = isClose ? 'bg-success' : 'bg-warning text-dark';
            const statLabel = isClose ? 'Close' : 'Open';
            const docs = data.documentations || [];

            let docsHtml = '';
            if (docs.length > 0) {
                docsHtml = docs.map(d => `
                    <div class="d-flex align-items-center justify-content-between p-2 mb-2 bg-light rounded border">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-check-fill text-success fs-5"></i>
                            <div>
                                <div class="fw-semibold text-dark small">${escapeHtml(d.original_name)}</div>
                                <div class="text-muted" style="font-size:10.5px;">${d.formatted_size || ''} &bull; Diupload: ${d.created_at ? d.created_at.substring(0, 10) : '-'}</div>
                            </div>
                        </div>
                        <div class="d-flex gap-1">
                            <a href="${IFU_BASE_URL}/documentation/${d.id}/view" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:11px;">
                                <i class="bi bi-eye me-1"></i>Lihat
                            </a>
                            <a href="${IFU_BASE_URL}/documentation/${d.id}/download" class="btn btn-sm btn-outline-success py-0 px-2" style="font-size:11px;">
                                <i class="bi bi-download me-1"></i>Unduh
                            </a>
                        </div>
                    </div>
                `).join('');
            } else {
                docsHtml = '<div class="text-muted small fst-italic py-2">Belum ada file dokumentasi.</div>';
            }

            const closeDateStr = (isClose && (i.closed_date || i.due_date)) 
                ? String(i.closed_date || i.due_date).substring(0, 10) 
                : '-';

            // Tombol & Box Aksi Status (Tandai Close / Buka Kembali)
            const statusActionBox = isClose
                ? `
                    <div class="mt-3 p-3 bg-warning-subtle rounded border border-warning-subtle d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <div class="fw-bold text-warning-emphasis small"><i class="bi bi-info-circle me-1"></i>Status Temuan: Close</div>
                            <div class="text-muted" style="font-size: 11px;">Temuan ini telah ditutup pada ${closeDateStr}. Ingin membuka kembali investigasi/tindak lanjut?</div>
                        </div>
                        <button type="button" class="btn btn-warning btn-sm text-dark fw-bold px-3" onclick="reopenIssuePrompt(${i.id}, '${escapeHtml(i.title).replace(/'/g, "\\'")}')">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Buka Kembali (Open)
                        </button>
                    </div>
                `
                : `
                    <div class="mt-3 p-3 bg-success-subtle rounded border border-success-subtle d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <div class="fw-bold text-success small"><i class="bi bi-check2-circle me-1"></i>Status Temuan: Open (Aktif)</div>
                            <div class="text-muted" style="font-size: 11px;">Temuan masih terbuka. Klik tombol di samping untuk menetapkan tanggal close dan menyelesaikan temuan.</div>
                        </div>
                        <button type="button" class="btn btn-success btn-sm fw-bold px-3" onclick="closeIssuePrompt(${i.id}, '${escapeHtml(i.title).replace(/'/g, "\\'")}')">
                            <i class="bi bi-check2-circle me-1"></i> Tandai Selesai / Close Temuan
                        </button>
                    </div>
                `;

            const footerActionBtn = isClose
                ? `<button type="button" class="btn btn-warning btn-sm text-dark fw-semibold" onclick="reopenIssuePrompt(${i.id}, '${escapeHtml(i.title).replace(/'/g, "\\'")}')"><i class="bi bi-arrow-counterclockwise me-1"></i> Buka Kembali (Open)</button>`
                : `<button type="button" class="btn btn-success btn-sm fw-semibold" onclick="closeIssuePrompt(${i.id}, '${escapeHtml(i.title).replace(/'/g, "\\'")}')"><i class="bi bi-check2-circle me-1"></i> Tandai Selesai (Close)</button>`;

            const actionContainer = document.getElementById('detailStatusActionContainer');
            if (actionContainer) {
                actionContainer.innerHTML = footerActionBtn;
            }

            bodyEl.innerHTML = `
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="ifu-code-badge">${escapeHtml(i.issue_code)}</span>
                        <span class="badge bg-primary-subtle text-primary border ms-1">${escapeHtml(i.function_label || i.hsse_division || 'Safeguard')}</span>
                    </div>
                    <span class="badge ${statBadgeClass} px-3 py-1 fs-6">${statLabel}</span>
                </div>
                <h5 class="fw-bold text-dark mb-2">${escapeHtml(i.title)}</h5>
                <div class="p-3 bg-light rounded border mb-3 small text-secondary">
                    ${escapeHtml(i.description || '-')}
                </div>
                <div class="row g-2 mb-3 small">
                    <div class="col-6">
                        <div class="text-muted">Sumber Temuan:</div>
                        <div class="fw-bold text-dark">${escapeHtml(i.source_label || i.category || '-')}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted">Tanggal Temuan:</div>
                        <div class="fw-bold text-dark">${i.reported_date ? String(i.reported_date).substring(0, 10) : '-'}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted">Tanggal Close:</div>
                        <div class="fw-bold ${isClose ? 'text-success' : 'text-muted'}">
                            ${closeDateStr}
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted">Lokasi Spesifik:</div>
                        <div class="fw-bold text-dark">${escapeHtml(i.location || '-')}</div>
                    </div>
                </div>

                <div class="border-top pt-3">
                    <div class="fw-bold small text-dark mb-2">
                        <i class="bi bi-paperclip me-1"></i> Dokumentasi Temuan (${docs.length} File)
                    </div>
                    ${docsHtml}
                </div>

                ${statusActionBox}
            `;
        } catch (e) {
            bodyEl.innerHTML = '<div class="alert alert-danger">Terjadi kesalahan saat memuat data.</div>';
        }
    }

    // Modal Manajemen Master Data Sumber Temuan (Tambah & Hapus)
    function openManageSourcesModal() {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalManageSources')).show();
    }

    async function submitAddNewSource() {
        const input = document.getElementById('inputNewSourceName');
        const name = input?.value?.trim();
        if (!name) {
            Swal.fire('Peringatan', 'Silakan ketikkan nama sumber temuan baru.', 'warning');
            return;
        }

        try {
            const res = await fetch("{{ route('issue-follow-up.sources.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name: name })
            });
            const data = await res.json();
            if (data.success && data.source) {
                input.value = '';
                // Append ke list group modal
                const list = document.getElementById('listManageSources');
                if (list) {
                    const item = document.createElement('div');
                    item.className = 'list-group-item d-flex justify-content-between align-items-center py-2 px-3';
                    item.setAttribute('data-source-item', data.source.name);
                    item.innerHTML = `
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-tag text-success"></i>
                            <span class="fw-semibold text-dark" style="font-size: 12.5px;">${escapeHtml(data.source.name)}</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 11px;" onclick="deleteSourceItem('${escapeHtml(data.source.name)}')" title="Hapus sumber temuan ini">
                            <i class="bi bi-trash"></i>
                        </button>
                    `;
                    list.appendChild(item);
                }

                // Tambahkan opsi ke select dropdown
                ['formCategory', 'filterSource'].forEach(selId => {
                    const sel = document.getElementById(selId);
                    if (sel) {
                        const opt = document.createElement('option');
                        opt.value = data.source.name;
                        opt.textContent = data.source.name;
                        sel.appendChild(opt);
                    }
                });

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: data.message,
                    timer: 1200,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('Error', data.message || 'Gagal menyimpan sumber temuan.', 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
        }
    }

    async function deleteSourceItem(name) {
        Swal.fire({
            title: 'Hapus Sumber Temuan?',
            html: `Apakah Anda yakin ingin menghapus opsi <strong>"${escapeHtml(name)}"</strong> dari master Sumber Temuan?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(async result => {
            if (!result.isConfirmed) return;

            try {
                const res = await fetch(`${IFU_BASE_URL}/sources/${encodeURIComponent(name)}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    // Hapus dari DOM
                    document.querySelectorAll(`[data-source-item="${name}"]`).forEach(el => el.remove());
                    // Hapus opsi dari dropdowns
                    ['formCategory', 'filterSource'].forEach(selId => {
                        const sel = document.getElementById(selId);
                        if (sel) {
                            Array.from(sel.options).forEach(opt => {
                                if (opt.value === name) opt.remove();
                            });
                        }
                    });

                    Swal.fire({ icon: 'success', title: 'Terhapus', text: data.message, timer: 1200, showConfirmButton: false });
                } else {
                    Swal.fire('Error', data.message || 'Gagal menghapus sumber temuan.', 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'Terjadi kesalahan saat menghapus.', 'error');
            }
        });
    }

    // Modal Export PDF Safeguard
    function openExportSafeguardModal() {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalExportPdfSafeguard')).show();
    }

    function toggleExportSelectAll(checked) {
        document.querySelectorAll('.cb-export-func').forEach(cb => {
            cb.checked = checked;
        });
    }

    function checkExportSelectAllState() {
        const cbs = Array.from(document.querySelectorAll('.cb-export-func'));
        const allChecked = cbs.every(cb => cb.checked);
        const masterCb = document.getElementById('cbExportSelectAll');
        if (masterCb) masterCb.checked = allChecked;
    }

    function updateDateRangeState() {
        const start = document.getElementById('filterStartDate')?.value;
        const end = document.getElementById('filterEndDate')?.value;
        const box = document.getElementById('dateRangeBox');
        if (box) {
            if (start || end) {
                box.classList.add('has-date');
            } else {
                box.classList.remove('has-date');
            }
        }
    }

    function clearDateRange() {
        const start = document.getElementById('filterStartDate');
        const end = document.getElementById('filterEndDate');
        if (start) start.value = '';
        if (end) end.value = '';
        updateDateRangeState();
    }

    document.getElementById('filterStartDate')?.addEventListener('change', updateDateRangeState);
    document.getElementById('filterEndDate')?.addEventListener('change', updateDateRangeState);

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
@endpush
