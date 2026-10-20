@extends('layouts.app', [
    'title' => 'Events',
    'activeModule' => 'events'
])

@push('styles')
<style>
    /* Minimalist Stat Cards Styling for Events */
    .events-stat-overview-card {
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 20px;
        padding: 24px 28px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .events-stat-overview-card:hover {
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.05);
    }

    .events-chart-wrapper {
        width: 100%;
        height: 220px;
        position: relative;
        margin-top: 14px;
    }

    .stats-subgrid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }

    @media (max-width: 768px) {
        .stats-subgrid {
            grid-template-columns: 1fr;
        }
    }

    .stats-subgrid .stat-card {
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 18px;
        padding: 22px 24px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .stats-subgrid .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06);
    }

    .stat-title {
        font-size: 16px;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.015em;
        margin-bottom: 0;
        line-height: 1.2;
    }

    .stat-number {
        font-size: 44px;
        font-weight: 900;
        line-height: 1;
        letter-spacing: -0.03em;
        font-family: inherit;
    }



    /* Thumbnail Tabel Dokumentasi */
    .event-doc-thumb {
        width: 58px;
        height: 44px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        transition: transform 0.18s, box-shadow 0.18s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }

    .event-doc-thumb:hover {
        transform: scale(1.08);
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }

    .event-doc-placeholder {
        padding: 4px 10px;
        border-radius: 6px;
        background: #f8fafc;
        color: #94a3b8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 500;
        border: 1px dashed #cbd5e1;
    }

    .event-doc-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 8px;
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #e2e8f0;
        font-size: 11.5px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.15s ease;
        cursor: pointer;
    }

    .event-doc-badge:hover {
        background: #e2e8f0;
        color: #0f172a;
        transform: translateY(-1px);
    }


    .company-pill {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 600;
        background: #f5f3ff;
        color: #6d28d9;
        border: 1px solid #ddd6fe;
    }

    .detail-info-row {
        display: grid;
        grid-template-columns: 120px 10px 1fr;
        gap: 6px;
        margin-bottom: 8px;
        font-size: 12px;
    }

    .detail-info-label {
        color: #64748b;
        font-weight: 500;
    }

    .detail-info-sep {
        color: #94a3b8;
    }

    .detail-info-val {
        color: #0f172a;
        font-weight: 600;
    }

    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }

    .gallery-item-card {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
        background: #ffffff;
        text-align: center;
        transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .gallery-item-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        border-color: #cbd5e1;
    }

    .gallery-thumb-img {
        width: 100%;
        height: 85px;
        object-fit: cover;
        display: block;
        cursor: pointer;
    }

    .gallery-doc-placeholder {
        width: 100%;
        height: 85px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
    }

    .gallery-label {
        padding: 6px 8px;
        font-size: 11px;
        color: #334155;
        font-weight: 500;
        background: #ffffff;
    }


    .gallery-upload-card {
        border: 2px dashed #cbd5e1;
        border-radius: 6px;
        height: 106px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        background: #f8fafc;
        color: #64748b;
        transition: all 0.2s;
    }

    .gallery-upload-card:hover {
        border-color: var(--green-primary);
        color: var(--green-primary);
        background: #ecfdf5;
    }

    .upload-drag-area {
        border: 2px dashed #cbd5e1;
        border-radius: 8px;
        padding: 16px;
        text-align: center;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.2s;
    }

    .upload-drag-area:hover {
        border-color: var(--green-primary);
        background: #ecfdf5;
    }

    .filter-grid-events-new {
        display: grid;
        grid-template-columns: 1fr 140px minmax(260px, 310px) auto;
        gap: 10px;
        align-items: center;
    }

    /* Date Range Container in Filter Events */
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

    .date-range-separator {
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
        padding: 0 6px;
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

    /* Lightbox Modal Custom Styling */
    #modalImagePreview {
        z-index: 1070 !important;
        background: rgba(15, 23, 42, 0.85);
        backdrop-filter: blur(6px);
    }
    #modalImagePreview .modal-dialog {
        max-width: 92vw;
        margin: 1.5rem auto;
    }
    #modalImagePreview .modal-content {
        background: transparent;
        border: none;
        box-shadow: none;
    }
    .lightbox-card {
        background: rgba(30, 41, 59, 0.7);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 12px;
        padding: 16px;
        position: relative;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        display: inline-block;
        max-width: 100%;
    }
    .lightbox-img-element {
        max-height: 80vh;
        max-width: 88vw;
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.35);
        display: none;
        margin: 0 auto;
    }
    .lightbox-close-btn {
        position: absolute;
        top: -14px;
        right: -14px;
        width: 34px;
        height: 34px;
        background: #ef4444;
        color: #ffffff;
        border: 2px solid #ffffff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        z-index: 10;
    }
    .lightbox-close-btn:hover {
        background: #dc2626;
        transform: scale(1.1);
    }

    @media (max-width: 992px) {
        .filter-grid-events-new {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 576px) {
        .filter-grid-events-new {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<!-- HEADER & ACTIONS -->
<div class="page-header">
    <div class="header-title-wrapper">
        <div class="header-icon-box purple">
            <i class="bi bi-calendar-event"></i>
        </div>
        <div>
            <h1 class="page-heading">Events</h1>
        </div>
    </div>

    <div class="header-actions">
        @adminManagement
            <button class="btn-action-primary" data-bs-toggle="modal" data-bs-target="#modalTambahEvent">
                <i class="bi bi-plus-lg"></i> Tambah Event
            </button>
            <button type="button" class="btn-action-outline text-success" onclick="openManageCompaniesModal()" title="Kelola Perusahaan / Instansi">
                <i class="bi bi-gear-fill me-1"></i> Kelola Instansi
            </button>
        @endadminManagement
        @canExportPdf
        <a href="{{ route('events.export', request()->query()) }}" class="btn-action-outline text-dark border-dark" id="btnExportPdf" style="color: #0f172a !important; border-color: #0f172a !important;">
            <i class="bi bi-file-earmark-pdf-fill me-1" style="color: #0f172a !important;"></i> Export PDF
        </a>
        @endcanExportPdf
    </div>
</div>

<!-- AREA STATISTIK & TREN KEGIATAN (2-TIER LAYOUT) -->
<div class="mb-4">
    <!-- Baris Atas (Full Width): Card Grafik "Total Pekerjaan / Kegiatan" -->
    <div class="events-stat-overview-card mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom pb-3">
            <div>
                <h3 class="stat-title mb-1" style="font-size: 18px;">Total Kegiatan</h3>
                <div class="text-muted" style="font-size: 12px; font-weight: 500;">
                    Visualisasi tren akumulasi kegiatan dalam 6 bulan terakhir
                </div>
            </div>
            <div class="d-flex align-items-baseline gap-2">
                <span class="text-muted small fw-semibold">Total Terdata:</span>
                <span class="stat-number text-dark" id="cardTotalKegiatan" style="font-size: 38px;">{{ $totalKegiatan }}</span>
            </div>
        </div>

        <!-- Area Grafik Tren (Bar / Area Chart Membentang Penuh) -->
        <div class="events-chart-wrapper">
            <canvas id="miniTrendChart"></canvas>
        </div>
    </div>

    <!-- Baris Bawah (3 Kolom Sejajar): Stat Cards Selesai, Tahun Ini, Bulan Ini -->
    <div class="stats-subgrid">
        <!-- Card 1: Selesai -->
        <div class="stat-card">
            <div class="stat-title">Selesai</div>
            <div class="stat-number text-success" id="cardSelesai">{{ $selesai }}</div>
        </div>

        <!-- Card 2: Tahun Ini -->
        <div class="stat-card">
            <div class="stat-title">Tahun Ini ({{ date('Y') }})</div>
            <div class="stat-number text-primary" id="cardTahunIni">{{ $tahunIni }}</div>
        </div>

        <!-- Card 3: Bulan Ini -->
        <div class="stat-card">
            <div class="stat-title">Bulan Ini ({{ now()->translatedFormat('F') }})</div>
            <div class="stat-number text-warning" id="cardBulanIni">{{ $bulanIni }}</div>
        </div>
    </div>
</div>

<!-- FILTER BAR SEDERHANA -->
<div class="filter-card">
    <form id="filterForm" method="GET" action="{{ route('events.index') }}">
        <div class="filter-grid-events-new">
            <!-- 1. Input Pencarian Global: Judul Kegiatan & Deskripsi -->
            <div class="filter-input-wrap">
                <i class="bi bi-search filter-icon-addon"></i>
                <input type="text"
                       name="search"
                       id="filterSearch"
                       class="form-control form-control-filter"
                       placeholder="Cari judul kegiatan atau deskripsi..."
                       value="{{ request('search') }}">
            </div>

            <!-- 2. Dropdown Status (All / Selesai / dll) -->
            <div>
                <select name="status" id="filterStatus" class="form-select form-select-filter">
                    <option value="All" {{ in_array(request('status', 'All'), ['All', 'Semua', '']) ? 'selected' : '' }}>Semua Status (All)</option>
                    <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="Berlangsung" {{ request('status') == 'Berlangsung' ? 'selected' : '' }}>Berlangsung</option>
                    <option value="Direncanakan" {{ request('status') == 'Direncanakan' ? 'selected' : '' }}>Direncanakan</option>
                    <option value="Dibatalkan" {{ request('status') == 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>

            <!-- 3. Rentang Tanggal (Date Range Container Menyatu dengan pemisah s/d) -->
            <div class="date-range-container @if(request('start_date') || request('end_date')) has-date @endif" id="dateRangeBox">
                <i class="bi bi-calendar-event date-range-icon"></i>
                <div class="date-range-inputs">
                    <input type="date"
                           name="start_date"
                           id="filterStartDate"
                           class="date-range-input"
                           placeholder="dd/mm/yyyy"
                           value="{{ request('start_date') }}"
                           title="Tanggal Mulai"
                           onclick="try{this.showPicker()}catch(e){}">
                    <span class="date-range-separator">s/d</span>
                    <input type="date"
                           name="end_date"
                           id="filterEndDate"
                           class="date-range-input"
                           placeholder="dd/mm/yyyy"
                           value="{{ request('end_date') }}"
                           title="Tanggal Selesai"
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

            <!-- Hidden per_page -->
            <input type="hidden" name="per_page" id="filterPerPage" value="{{ $perPage }}">

            <!-- 5. Tombol Filter & Reset -->
            <div class="d-flex align-items-center gap-1">
                <button type="submit" class="btn btn-sm btn-primary px-3" style="background:#059669; border-color:#059669;">
                    <i class="bi bi-funnel"></i> Terapkan
                </button>
                <a href="{{ route('events.index') }}" class="btn btn-sm btn-outline-secondary">
                    Reset
                </a>
            </div>
        </div>
    </form>
</div>

<!-- TABEL DAFTAR KEGIATAN -->
<div class="table-card">
    <div class="table-topbar">
        <div>
            <h2 class="table-section-title mb-0">
                Daftar Kegiatan
                <span class="badge bg-secondary-subtle text-secondary ms-2" style="font-size:10.5px;">{{ $events->total() }} Data</span>
            </h2>
        </div>

        <div class="table-controls">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted" style="font-size:11.5px">Tampilkan</span>
                <select id="perPageSelect" class="form-select form-select-sm" style="width: 70px; font-size:11.5px">
                    <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                </select>
                <span class="text-muted" style="font-size:11.5px">per halaman</span>
            </div>
        </div>
    </div>

    <div class="table-responsive-custom">
        <table class="table-custom">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">No</th>
                    <th>Judul Kegiatan</th>
                    <th style="width: 110px; text-align: center;">Tanggal</th>
                    <th style="width: 190px;">Nama Perusahaan / Instansi</th>
                    <th style="width: 130px; text-align: center;">Dokumentasi</th>
                    <th style="width: 110px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody id="eventsTableBody">
                @forelse($events as $index => $event)
                    @php
                        $docs = $event->documents;
                        $docCount = $docs->count();
                        
                        // Cari file gambar pertama dari relasi documents atau legacy column documentation
                        $firstImageDoc = $docs->first(function($doc) {
                            $ext = strtolower(pathinfo($doc->file_name ?: $doc->file_path, PATHINFO_EXTENSION));
                            return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']) 
                                || str_starts_with($doc->mime_type ?? '', 'image/');
                        });

                        $imageThumbUrl = null;
                        if ($firstImageDoc && $firstImageDoc->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($firstImageDoc->file_path)) {
                            $imageThumbUrl = asset('storage/' . ltrim($firstImageDoc->file_path, '/'));
                        } elseif (!empty($event->documentation) && \Illuminate\Support\Facades\Storage::disk('public')->exists($event->documentation)) {
                            $legacyExt = strtolower(pathinfo($event->documentation, PATHINFO_EXTENSION));
                            if (in_array($legacyExt, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                                $imageThumbUrl = asset('storage/' . ltrim($event->documentation, '/'));
                            }
                        }

                        // Berkas non-gambar pertama (jika tidak ada gambar)
                        $firstNonImageDoc = $docs->first(function($doc) {
                            $ext = strtolower(pathinfo($doc->file_name ?: $doc->file_path, PATHINFO_EXTENSION));
                            return !in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']) 
                                && !str_starts_with($doc->mime_type ?? '', 'image/');
                        });
                    @endphp
                    <tr>
                        <td style="text-align: center; color: #64748b;">{{ $events->firstItem() + $index }}</td>
                        <td>
                            <div class="fw-bold text-dark fs-6">{{ $event->title }}</div>
                            @if($event->description)
                                <div class="text-muted small mt-1" style="font-size:11.5px; line-height:1.4;">
                                    {{ \Illuminate\Support\Str::limit($event->description, 110) }}
                                </div>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <div class="fw-semibold text-dark">{{ $event->event_date ? $event->event_date->format('d/m/Y') : '-' }}</div>
                            <span class="badge mt-1 {{ $event->status === 'Selesai' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border' }}" style="font-size:10px;">
                                {{ $event->status }}
                            </span>
                        </td>
                        <td>
                            <span class="company-pill">
                                <i class="bi bi-building me-1"></i>{{ $event->company ?: 'EBTKE' }}
                            </span>
                        </td>
                        <!-- Kolom Dokumentasi Dinamis -->
                        <td style="text-align: center; vertical-align: middle;">
                            @if($imageThumbUrl)
                                {{-- 1. Jika ada berkas gambar nyata --}}
                                <div class="d-inline-flex flex-column align-items-center">
                                    <img src="{{ $imageThumbUrl }}"
                                         alt="{{ $event->title }}"
                                         class="event-doc-thumb"
                                         title="Klik untuk pratinjau foto: {{ $firstImageDoc ? $firstImageDoc->file_name : $event->title }}"
                                         onclick="previewImage('{{ $imageThumbUrl }}', '{{ addslashes($firstImageDoc ? $firstImageDoc->file_name : $event->title) }}')"
                                         loading="lazy">
                                    @if($docCount > 1)
                                        <a href="javascript:void(0)" onclick="openDetailModal({{ $event->id }}, 'docs')" class="text-decoration-none badge bg-light text-secondary border mt-1" style="font-size: 10px;">
                                            +{{ $docCount - 1 }} Berkas
                                        </a>
                                    @endif
                                </div>
                            @elseif($docCount > 0)
                                {{-- 2. Jika ada berkas dokumen non-gambar (PDF, DOCX, XLS, dll) --}}
                                @php
                                    $firstExt = strtoupper(pathinfo($firstNonImageDoc ? ($firstNonImageDoc->file_name ?: $firstNonImageDoc->file_path) : '', PATHINFO_EXTENSION)) ?: 'FILE';
                                @endphp
                                <a href="javascript:void(0)" class="event-doc-badge" onclick="openDetailModal({{ $event->id }}, 'docs')" title="Buka detail dokumentasi">
                                    @if($firstExt === 'PDF')
                                        <i class="bi bi-file-earmark-pdf-fill text-danger fs-6"></i>
                                    @elseif(in_array($firstExt, ['DOC', 'DOCX']))
                                        <i class="bi bi-file-earmark-word-fill text-primary fs-6"></i>
                                    @elseif(in_array($firstExt, ['XLS', 'XLSX']))
                                        <i class="bi bi-file-earmark-excel-fill text-success fs-6"></i>
                                    @else
                                        <i class="bi bi-file-earmark-text-fill text-secondary fs-6"></i>
                                    @endif
                                    <span>{{ $docCount }} {{ $docCount === 1 ? 'Berkas (' . $firstExt . ')' : 'Berkas' }}</span>
                                </a>
                            @else
                                {{-- 3. Jika tidak ada file dokumentasi --}}
                                <span class="event-doc-placeholder" title="Tidak ada file dokumentasi yang diunggah">
                                    <span class="text-muted">-</span>
                                </span>
                            @endif
                        </td>
                        <!-- Kolom Aksi (Maksimal 3 Tombol Utama Sejajar Horizontal) -->
                        <td style="text-align: center;">
                            <div class="d-inline-flex align-items-center justify-content-center gap-1 flex-nowrap">
                                <!-- Detail (sekaligus akses download lampiran dokumentasi) -->
                                <button class="btn-action-icon btn-action-view" title="Detail &amp; Berkas Dokumentasi" onclick="openDetailModal({{ $event->id }})">
                                    <i class="bi bi-eye"></i>
                                </button>

                                @adminManagement
                                    <!-- Edit -->
                                    <button class="btn-action-icon btn-action-edit" title="Edit Kegiatan" onclick="openEditModal({{ $event->id }})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <!-- Hapus -->
                                    <button class="btn-action-icon btn-action-delete" title="Hapus Kegiatan" onclick="confirmDeleteEvent({{ $event->id }}, '{{ addslashes($event->title) }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endadminManagement
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                            Tidak ada data kegiatan yang sesuai dengan filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Table Footer & Pagination -->
    <div class="table-bottom-bar">
        <div>
            Menampilkan {{ $events->firstItem() ?? 0 }} - {{ $events->lastItem() ?? 0 }} dari {{ $events->total() }} data
        </div>
        <div>
            {{ $events->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

<!-- MODAL TAMBAH EVENT (FORM RINGKAS) -->
<div class="modal fade" id="modalTambahEvent" tabindex="-1" aria-labelledby="modalTambahEventLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-custom-content">
            <div class="modal-header modal-header-themed purple">
                <h5 class="modal-title" id="modalTambahEventLabel">
                    <i class="bi bi-calendar-plus me-1"></i> Tambah Event
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTambahEvent" action="{{ route('events.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Judul Kegiatan -->
                        <div class="col-md-7">
                            <label class="form-label fw-semibold" style="font-size:12px">Judul Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-sm" placeholder="Contoh: Audit Keselamatan Kerja EBTKE" required>
                        </div>

                        <!-- Tanggal Kegiatan -->
                        <div class="col-md-5">
                            <label class="form-label fw-semibold" style="font-size:12px">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="event_date" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}">
                        </div>

                        <!-- Dropdown Nama Perusahaan / Instansi + Tombol Kelola Instansi -->
                        <div class="col-md-7">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0" style="font-size:12px">Nama Perusahaan / Instansi <span class="text-danger">*</span></label>
                                <button type="button" class="btn btn-link btn-sm p-0 text-success text-decoration-none fw-semibold" onclick="openManageCompaniesModal()" style="font-size:11.5px;">
                                    <i class="bi bi-gear-fill me-1"></i>Kelola Instansi
                                </button>
                            </div>
                            <select name="company" id="selectCompanyTambah" class="form-select form-select-sm" required>
                                <option value="">-- Pilih Instansi / Perusahaan --</option>
                                @foreach($allCompanies as $comp)
                                    <option value="{{ $comp }}" {{ $comp === 'EBTKE' ? 'selected' : '' }}>{{ $comp }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Kegiatan -->
                        <div class="col-md-5">
                            <label class="form-label fw-semibold" style="font-size:12px">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select form-select-sm" required>
                                <option value="Selesai" selected>Selesai</option>
                                <option value="Berlangsung">Berlangsung</option>
                                <option value="Direncanakan">Direncanakan</option>
                                <option value="Dibatalkan">Dibatalkan</option>
                            </select>
                        </div>

                        <!-- Deskripsi Kegiatan -->
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:12px">Deskripsi Kegiatan</label>
                            <textarea name="description" class="form-control form-control-sm" rows="3" placeholder="Uraikan ringkasan atau hasil kegiatan..."></textarea>
                        </div>

                        <!-- Upload Dokumentasi (Foto Kegiatan) -->
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:12px">Dokumentasi (Foto Kegiatan)</label>
                            <div class="upload-drag-area" onclick="document.getElementById('inputDocsTambah').click()">
                                <i class="bi bi-cloud-arrow-up fs-3 text-secondary d-block"></i>
                                <span class="text-muted" style="font-size:11.5px">Klik untuk upload foto atau dokumen dokumentasi (Bisa pilih banyak sekaligus, maks 15 MB/file)</span>
                                <input type="file" name="documentation_files[]" id="inputDocsTambah" class="d-none" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" onchange="handleFilesSelected(this, 'tambah')">
                            </div>
                            <div id="previewFilesTambahContainer" class="d-flex flex-wrap gap-2 mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-2 border-top">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-action-primary px-4">
                        <i class="bi bi-check-lg me-1"></i> Simpan Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDIT EVENT -->
<div class="modal fade" id="modalEditEvent" tabindex="-1" aria-labelledby="modalEditEventLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-custom-content">
            <div class="modal-header modal-header-themed purple">
                <h5 class="modal-title" id="modalEditEventLabel">
                    <i class="bi bi-pencil-square me-1"></i> Edit Event
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditEvent" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Judul Kegiatan -->
                        <div class="col-md-7">
                            <label class="form-label fw-semibold" style="font-size:12px">Judul Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="editTitle" class="form-control form-control-sm" required>
                        </div>

                        <!-- Tanggal Kegiatan -->
                        <div class="col-md-5">
                            <label class="form-label fw-semibold" style="font-size:12px">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="event_date" id="editEventDate" class="form-control form-control-sm" required>
                        </div>

                        <!-- Nama Perusahaan / Instansi + Tombol Kelola Instansi -->
                        <div class="col-md-7">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0" style="font-size:12px">Nama Perusahaan / Instansi <span class="text-danger">*</span></label>
                                <button type="button" class="btn btn-link btn-sm p-0 text-success text-decoration-none fw-semibold" onclick="openManageCompaniesModal()" style="font-size:11.5px;">
                                    <i class="bi bi-gear-fill me-1"></i>Kelola Instansi
                                </button>
                            </div>
                            <select name="company" id="selectCompanyEdit" class="form-select form-select-sm" required>
                                <option value="">-- Pilih Instansi / Perusahaan --</option>
                                @foreach($allCompanies as $comp)
                                    <option value="{{ $comp }}">{{ $comp }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Kegiatan -->
                        <div class="col-md-5">
                            <label class="form-label fw-semibold" style="font-size:12px">Status <span class="text-danger">*</span></label>
                            <select name="status" id="editStatus" class="form-select form-select-sm" required>
                                <option value="Selesai">Selesai</option>
                                <option value="Berlangsung">Berlangsung</option>
                                <option value="Direncanakan">Direncanakan</option>
                                <option value="Dibatalkan">Dibatalkan</option>
                            </select>
                        </div>

                        <!-- Deskripsi Kegiatan -->
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:12px">Deskripsi Kegiatan</label>
                            <textarea name="description" id="editDescription" class="form-control form-control-sm" rows="3"></textarea>
                        </div>

                        <!-- Upload Dokumentasi Tambahan -->
                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:12px">Tambah Dokumentasi Baru (Opsional)</label>
                            <div class="upload-drag-area" onclick="document.getElementById('inputDocsEdit').click()">
                                <i class="bi bi-cloud-arrow-up fs-3 text-secondary d-block"></i>
                                <span class="text-muted" style="font-size:11.5px">Klik untuk upload foto atau dokumen dokumentasi (Bisa pilih banyak sekaligus, maks 15 MB/file)</span>
                                <input type="file" name="documentation_files[]" id="inputDocsEdit" class="d-none" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" onchange="handleFilesSelected(this, 'edit')">
                            </div>
                            <div id="previewFilesEditContainer" class="d-flex flex-wrap gap-2 mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-2 border-top">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-action-primary px-4">
                        <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL DETAIL EVENT -->
<div class="modal fade" id="modalDetailEvent" tabindex="-1" aria-labelledby="modalDetailEventLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content modal-custom-content">
            <div class="modal-header modal-header-themed purple">
                <h5 class="modal-title" id="modalDetailEventLabel">
                    <i class="bi bi-info-circle me-1"></i> Detail Event
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-md-5 border-end">
                        <h6 class="fw-bold mb-3 text-dark" style="font-size:13px">Informasi Kegiatan</h6>

                        <div class="detail-info-row">
                            <div class="detail-info-label">Judul</div>
                            <div class="detail-info-sep">:</div>
                            <div class="detail-info-val" id="detailJudul">-</div>
                        </div>

                        <div class="detail-info-row">
                            <div class="detail-info-label">Instansi/Perusahaan</div>
                            <div class="detail-info-sep">:</div>
                            <div class="detail-info-val" id="detailCompany">-</div>
                        </div>

                        <div class="detail-info-row">
                            <div class="detail-info-label">Tanggal</div>
                            <div class="detail-info-sep">:</div>
                            <div class="detail-info-val" id="detailTanggal">-</div>
                        </div>

                        <div class="detail-info-row">
                            <div class="detail-info-label">Status</div>
                            <div class="detail-info-sep">:</div>
                            <div class="detail-info-val">
                                <span id="detailStatusBadge" class="badge-status status-selesai">Selesai</span>
                            </div>
                        </div>

                        <div class="detail-info-row">
                            <div class="detail-info-label">Deskripsi</div>
                            <div class="detail-info-sep">:</div>
                            <div class="detail-info-val fw-normal text-muted" id="detailDeskripsi">-</div>
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <div class="detail-info-row">
                                <div class="detail-info-label">Dibuat oleh</div>
                                <div class="detail-info-sep">:</div>
                                <div class="detail-info-val fw-normal text-muted" id="detailDibuat">-</div>
                            </div>
                            <div class="detail-info-row">
                                <div class="detail-info-label">Diubah oleh</div>
                                <div class="detail-info-sep">:</div>
                                <div class="detail-info-val fw-normal text-muted" id="detailDiubah">-</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <ul class="nav nav-tabs nav-tabs-detail mb-0 border-bottom-0" id="detailTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="tabDocsBtn" data-bs-toggle="tab" data-bs-target="#tabDocs" type="button" role="tab">
                                        Dokumentasi (<span id="detailDocsCount">0</span>)
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tabLogsBtn" data-bs-toggle="tab" data-bs-target="#tabLogs" type="button" role="tab">
                                        Riwayat Kegiatan
                                    </button>
                                </li>
                            </ul>
                            <a href="#" id="btnDownloadAllDetailDocs" class="btn btn-sm btn-outline-success py-1 px-2 d-none" style="font-size: 11.5px;" title="Unduh Semua Berkas Dokumentasi">
                                <i class="bi bi-download me-1"></i> Unduh Semua Dokumen
                            </a>
                        </div>

                        <div class="tab-content pt-2 border-top" id="detailTabContent">
                            <div class="tab-pane fade show active" id="tabDocs" role="tabpanel">
                                <div class="gallery-grid" id="detailGalleryGrid"></div>
                                <input type="file" id="directDocUploadInput" class="d-none" multiple accept="image/*,.pdf,.doc,.docx" onchange="uploadDirectDoc(this)">
                            </div>

                            <div class="tab-pane fade" id="tabLogs" role="tabpanel">
                                <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                                    <table class="table table-sm table-hover" style="font-size: 11px;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Waktu</th>
                                                <th>Pengguna</th>
                                                <th>Aksi</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody id="detailLogsTableBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PREVIEW GAMBAR BESAR (LIGHTBOX) -->
<div class="modal fade" id="modalImagePreview" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered justify-content-center">
        <div class="modal-content">
            <div class="lightbox-card text-center">
                <!-- Close Button (X) -->
                <button type="button" class="lightbox-close-btn" data-bs-dismiss="modal" aria-label="Close" title="Tutup Preview">
                    <i class="bi bi-x-lg"></i>
                </button>

                <!-- Loading Spinner -->
                <div id="previewLoadingSpinner" class="py-5 text-white" style="display: none; min-width: 250px;">
                    <div class="spinner-border text-light mb-2" role="status"></div>
                    <div class="small text-white-50">Memuat gambar...</div>
                </div>

                <!-- Fallback / Error Message Container -->
                <div id="previewFallbackMsg" class="py-5 px-4 text-center text-white" style="display: none; min-width: 280px;">
                    <i class="bi bi-exclamation-triangle-fill text-warning fs-1 d-block mb-2"></i>
                    <h6 class="fw-bold mb-1 text-white">Foto Tidak Tersedia</h6>
                    <p class="small text-white-50 mb-3" style="max-width: 320px;">Berkas gambar tidak ditemukan di server atau tautan tidak valid.</p>
                </div>

                <!-- Main Preview Image -->
                <img id="modalPreviewImage"
                     src=""
                     alt="Preview Dokumentasi"
                     class="lightbox-img-element"
                     onload="onPreviewImageLoad()"
                     onerror="onPreviewImageError()">

                <!-- Footer Info & Actions -->
                <div class="d-flex align-items-center justify-content-between mt-3 px-1">
                    <div class="text-start pe-3" style="min-width: 0;">
                        <div id="modalPreviewTitle" class="text-white fw-semibold text-truncate" style="font-size: 13.5px; max-width: 450px;"></div>
                        <div id="modalPreviewSubtitle" class="text-white-50" style="font-size: 11px;">Pratinjau Foto Dokumentasi</div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a id="btnLightboxDownload" href="#" class="btn btn-sm btn-light py-1 px-2 fw-semibold" style="font-size: 11.5px; border-radius: 6px;" download title="Unduh foto">
                            <i class="bi bi-download me-1"></i> Unduh
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-light py-1 px-2" style="font-size: 11.5px; border-radius: 6px;" data-bs-dismiss="modal">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL KELOLA PERUSAHAAN / INSTANSI (TAMBAH & HAPUS) -->
<div class="modal fade" id="modalManageCompanies" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="header-icon-box green" style="width:34px; height:34px; font-size:16px;">
                        <i class="bi bi-buildings-fill"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Kelola Perusahaan / Instansi</h5>
                        <div class="text-muted" style="font-size:11px;">Tambah atau hapus master opsi Instansi/Perusahaan</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Form Tambah Instansi Baru (Bagian Atas) --}}
                <div class="mb-3 p-3 bg-light rounded border">
                    <label class="form-label fw-bold mb-2" style="font-size:12px;">+ Tambah Perusahaan / Instansi Baru</label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="inputNewCompanyName" class="form-control" placeholder="Contoh: PT Medco Power, EBTKE, dsb...">
                        <button type="button" class="btn btn-success" onclick="submitAddNewCompany()">
                            <i class="bi bi-plus-lg me-1"></i> Simpan
                        </button>
                    </div>
                </div>

                {{-- Daftar Instansi & Hapus (Bagian Bawah) --}}
                <div class="fw-bold text-dark small mb-2 d-flex justify-content-between align-items-center">
                    <span>Daftar Perusahaan / Instansi Saat Ini</span>
                    <span class="badge bg-secondary-subtle text-secondary" id="countActiveCompanies">{{ count($allCompanies) }} Opsi</span>
                </div>

                <div class="list-group" id="listManageCompanies" style="max-height: 280px; overflow-y: auto;">
                    @foreach($allCompanies as $comp)
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3" data-company-item="{{ $comp }}">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-building text-success"></i>
                                <span class="fw-semibold text-dark" style="font-size: 12.5px;">{{ $comp }}</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 11px;" onclick="deleteCompanyItem('{{ addslashes($comp) }}')" title="Hapus instansi ini">
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

<!-- Hidden Form for Delete -->
<form id="deleteEventForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let currentDetailEventId = null;

    // Mini Chart Tren 6 Bulan Terakhir di Card "Total Pekerjaan / Kegiatan" (Bar Chart)
    document.addEventListener('DOMContentLoaded', function() {
        const miniCtx = document.getElementById('miniTrendChart');
        if (miniCtx) {
            const trendLabels = {!! json_encode($trendMonths) !!};
            const trendData = {!! json_encode($trendCounts) !!};

            new Chart(miniCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: trendLabels,
                    datasets: [{
                        label: 'Kegiatan',
                        data: trendData,
                        backgroundColor: '#818cf8',
                        hoverBackgroundColor: '#6366f1',
                        borderRadius: 5,
                        borderSkipped: false,
                        barPercentage: 0.65,
                        categoryPercentage: 0.85
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 4,
                            bottom: 0,
                            left: 0,
                            right: 0
                        }
                    },
                    animation: {
                        duration: 700
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            titleColor: '#ffffff',
                            bodyColor: '#cbd5e1',
                            borderColor: '#334155',
                            borderWidth: 1,
                            padding: 8,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                title: (items) => items.length ? `Bulan ${items[0].label}` : '',
                                label: (ctx) => `${ctx.raw} kegiatan`
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    size: 11,
                                    weight: '600'
                                },
                                color: '#64748b',
                                padding: 6
                            }
                        },
                        y: {
                            display: true,
                            beginAtZero: true,
                            grid: {
                                color: '#f1f5f9',
                                drawBorder: false,
                                borderDash: [4, 4]
                            },
                            ticks: {
                                stepSize: 1,
                                precision: 0,
                                font: {
                                    size: 11,
                                    weight: '500'
                                },
                                color: '#94a3b8',
                                padding: 8
                            }
                        }
                    }
                }
            });
        }
    });

    // Otomatis Submit saat Filter Berubah
    ['filterStatus', 'filterStartDate', 'filterEndDate'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', () => {
                const startEl = document.getElementById('filterStartDate');
                const endEl = document.getElementById('filterEndDate');
                const box = document.getElementById('dateRangeBox');
                if (box) {
                    if ((startEl && startEl.value) || (endEl && endEl.value)) {
                        box.classList.add('has-date');
                    } else {
                        box.classList.remove('has-date');
                    }
                }
                document.getElementById('filterForm').submit();
            });
        }
    });

    function clearDateRange() {
        const start = document.getElementById('filterStartDate');
        const end = document.getElementById('filterEndDate');
        const box = document.getElementById('dateRangeBox');
        if (start) start.value = '';
        if (end) end.value = '';
        if (box) box.classList.remove('has-date');
        document.getElementById('filterForm').submit();
    }

    const perPageSelect = document.getElementById('perPageSelect');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            document.getElementById('filterPerPage').value = this.value;
            document.getElementById('filterForm').submit();
        });
    }

    // Modal Manajemen Master Perusahaan / Instansi (Tambah & Hapus)
    function openManageCompaniesModal() {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalManageCompanies')).show();
    }

    async function submitAddNewCompany() {
        const input = document.getElementById('inputNewCompanyName');
        const name = input ? input.value.trim() : '';

        if (!name) {
            Swal.fire('Peringatan', 'Silakan ketikkan nama perusahaan / instansi baru.', 'warning');
            return;
        }

        try {
            const res = await fetch("{{ route('events.companies.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name: name })
            });

            const data = await res.json();
            if (data.success) {
                if (input) input.value = '';

                // Append ke list modal jika belum ada
                const list = document.getElementById('listManageCompanies');
                if (list) {
                    let existingItem = list.querySelector(`[data-company-item="${CSS.escape(name)}"]`);
                    if (!existingItem) {
                        const item = document.createElement('div');
                        item.className = 'list-group-item d-flex justify-content-between align-items-center py-2 px-3';
                        item.setAttribute('data-company-item', name);
                        item.innerHTML = `
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-building text-success"></i>
                                <span class="fw-semibold text-dark" style="font-size: 12.5px;">${escapeHtml(name)}</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 11px;" onclick="deleteCompanyItem('${escapeHtml(name)}')" title="Hapus instansi ini">
                                <i class="bi bi-trash"></i>
                            </button>
                        `;
                        list.appendChild(item);

                        const countBadge = document.getElementById('countActiveCompanies');
                        if (countBadge) {
                            countBadge.textContent = `${list.children.length} Opsi`;
                        }
                    }
                }

                // Update kedua dropdown form (tambah dan edit) dan set selected
                ['selectCompanyTambah', 'selectCompanyEdit'].forEach(selId => {
                    const sel = document.getElementById(selId);
                    if (sel) {
                        let optExists = false;
                        for (let i = 0; i < sel.options.length; i++) {
                            if (sel.options[i].value.toLowerCase() === name.toLowerCase()) {
                                optExists = true;
                                sel.selectedIndex = i;
                                break;
                            }
                        }
                        if (!optExists) {
                            const newOpt = new Option(name, name, true, true);
                            sel.add(newOpt);
                            sel.value = name;
                        }
                    }
                });

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('Gagal', data.message || 'Tidak dapat menambahkan instansi.', 'error');
            }
        } catch (err) {
            console.error(err);
            Swal.fire('Error', 'Terjadi kesalahan sistem saat menyimpan instansi.', 'error');
        }
    }

    async function deleteCompanyItem(companyName) {
        Swal.fire({
            title: 'Hapus Instansi / Perusahaan?',
            html: `Apakah Anda yakin ingin menghapus opsi <strong>"${escapeHtml(companyName)}"</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(async (result) => {
            if (!result.isConfirmed) return;

            try {
                const res = await fetch(`/events/companies/${encodeURIComponent(companyName)}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json();
                if (data.success) {
                    // Hapus dari list modal
                    const list = document.getElementById('listManageCompanies');
                    if (list) {
                        const item = list.querySelector(`[data-company-item="${CSS.escape(companyName)}"]`);
                        if (item) item.remove();

                        const countBadge = document.getElementById('countActiveCompanies');
                        if (countBadge) {
                            countBadge.textContent = `${list.children.length} Opsi`;
                        }
                    }

                    // Hapus opsi dari kedua select
                    ['selectCompanyTambah', 'selectCompanyEdit'].forEach(selId => {
                        const sel = document.getElementById(selId);
                        if (sel) {
                            for (let i = 0; i < sel.options.length; i++) {
                                if (sel.options[i].value.toLowerCase() === companyName.toLowerCase()) {
                                    sel.remove(i);
                                    break;
                                }
                            }
                        }
                    });

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('Gagal Menghapus', data.message || 'Instansi tidak dapat dihapus.', 'error');
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'Terjadi kesalahan sistem saat menghapus instansi.', 'error');
            }
        });
    }

    // Escape helper
    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Modal Detail Kegiatan
    async function openDetailModal(id, activeTab = 'docs') {
        currentDetailEventId = id;
        try {
            const response = await fetch(`/events/${id}`, {
                headers: { 'Accept': 'application/json' }
            });
            const res = await response.json();

            if (res.success) {
                const e = res.event;
                document.getElementById('detailJudul').textContent = e.title;
                document.getElementById('detailCompany').textContent = e.company || '-';

                const dateFormatted = e.event_date ? new Date(e.event_date).toLocaleDateString('id-ID', {
                    day: 'numeric', month: 'long', year: 'numeric'
                }) : '-';
                document.getElementById('detailTanggal').textContent = dateFormatted;

                const badge = document.getElementById('detailStatusBadge');
                badge.textContent = e.status;
                badge.className = 'badge-status ' + (
                    e.status === 'Selesai' ? 'status-selesai' :
                    e.status === 'Berlangsung' ? 'status-berlangsung' :
                    e.status === 'Dibatalkan' ? 'status-dibatalkan' : 'status-direncanakan'
                );

                document.getElementById('detailDeskripsi').textContent = e.description || '-';
                document.getElementById('detailDibuat').textContent = (e.created_by || 'Administrator') +
                    (e.created_at ? ' (' + new Date(e.created_at).toLocaleDateString('id-ID') + ')' : '');
                document.getElementById('detailDiubah').textContent = (e.updated_by || 'Administrator') +
                    (e.updated_at ? ' (' + new Date(e.updated_at).toLocaleDateString('id-ID') + ')' : '');

                document.getElementById('detailDocsCount').textContent = res.documents.length;
                const btnDownloadAll = document.getElementById('btnDownloadAllDetailDocs');
                if (btnDownloadAll) {
                    if (res.documents.length > 0 || e.documentation) {
                        btnDownloadAll.href = `/events/${e.id}/download-doc`;
                        btnDownloadAll.classList.remove('d-none');
                    } else {
                        btnDownloadAll.classList.add('d-none');
                    }
                }

                const gallery = document.getElementById('detailGalleryGrid');
                let galleryHtml = '';

                if (res.documents && res.documents.length > 0) {
                    res.documents.forEach((doc, idx) => {
                        const fileName = doc.file_name || doc.title || `Berkas ${idx + 1}`;
                        const ext = fileName.split('.').pop().toLowerCase();
                        const isImg = (doc.mime_type && doc.mime_type.startsWith('image/')) || ['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(ext);

                        let previewContent = '';
                        let clickAction = '';

                        if (isImg) {
                            previewContent = `<img src="${escapeHtml(doc.file_url)}" alt="${escapeHtml(fileName)}" class="gallery-thumb-img" loading="lazy">`;
                            clickAction = `previewImage('${escapeHtml(doc.file_url)}', '${escapeHtml(fileName)}')`;
                        } else {
                            let iconClass = 'bi-file-earmark-text-fill text-secondary';
                            let iconColor = '#64748b';
                            let extLabel = ext.toUpperCase();

                            if (ext === 'pdf') {
                                iconClass = 'bi-file-earmark-pdf-fill';
                                iconColor = '#ef4444';
                            } else if (['doc', 'docx'].includes(ext)) {
                                iconClass = 'bi-file-earmark-word-fill';
                                iconColor = '#2563eb';
                            } else if (['xls', 'xlsx'].includes(ext)) {
                                iconClass = 'bi-file-earmark-excel-fill';
                                iconColor = '#16a34a';
                            }

                            previewContent = `
                                <div class="gallery-doc-placeholder" title="Unduh berkas: ${escapeHtml(fileName)}">
                                    <i class="bi ${iconClass} fs-1" style="color: ${iconColor};"></i>
                                    <span class="badge bg-secondary-subtle text-secondary mt-1" style="font-size: 9px; font-weight: 700;">${extLabel}</span>
                                </div>
                            `;
                            clickAction = `window.location.href='/events/documentation/${doc.id}/download'`;
                        }

                        const sizeFormatted = doc.file_size
                            ? (doc.file_size > 1024 * 1024 ? (doc.file_size / (1024 * 1024)).toFixed(1) + ' MB' : Math.round(doc.file_size / 1024) + ' KB')
                            : '';

                        galleryHtml += `
                            <div class="gallery-item-card" title="${escapeHtml(fileName)}">
                                <div onclick="${clickAction}" style="cursor: pointer;">
                                    ${previewContent}
                                </div>
                                <div class="gallery-label d-flex justify-content-between align-items-center">
                                    <div class="text-truncate text-start pe-1" style="max-width: 130px;">
                                        <div class="text-truncate fw-semibold" style="font-size: 11px;">${escapeHtml(fileName)}</div>
                                        ${sizeFormatted ? `<div class="text-muted" style="font-size: 9.5px;">${sizeFormatted}</div>` : ''}
                                    </div>
                                    <a href="/events/documentation/${doc.id}/download" class="btn btn-sm btn-light border py-0 px-1 text-primary" title="Unduh File" onclick="event.stopPropagation()">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    galleryHtml += `
                        <div class="col-12 text-center py-4 text-muted small" style="grid-column: 1 / -1;">
                            <i class="bi bi-folder2-open fs-2 d-block text-secondary mb-1"></i>
                            Belum ada berkas dokumentasi yang diunggah.
                        </div>
                    `;
                }

                galleryHtml += `
                    <div class="gallery-upload-card" onclick="document.getElementById('directDocUploadInput').click()" title="Unggah berkas foto atau dokumen">
                        <i class="bi bi-plus-lg fs-4"></i>
                        <span style="font-size:10px; font-weight:600">Upload Berkas</span>
                    </div>
                `;
                gallery.innerHTML = galleryHtml;

                const logTable = document.getElementById('detailLogsTableBody');
                if (res.logs && res.logs.length > 0) {
                    logTable.innerHTML = res.logs.map(log => `
                        <tr>
                            <td>${log.created_at || '-'}</td>
                            <td class="fw-semibold">${log.user_name || 'Admin'}</td>
                            <td><span class="badge bg-secondary">${log.action}</span></td>
                            <td>${log.description}</td>
                        </tr>
                    `).join('');
                } else {
                    logTable.innerHTML = `<tr><td colspan="4" class="text-center text-muted">Belum ada riwayat aktivitas.</td></tr>`;
                }

                new bootstrap.Tab(document.getElementById('tabDocsBtn')).show();
                new bootstrap.Modal(document.getElementById('modalDetailEvent')).show();
            }
        } catch (err) {
            console.error(err);
            Swal.fire('Error', 'Gagal memuat detail kegiatan', 'error');
        }
    }

    let previewModalInstance = null;

    function getPreviewModalInstance() {
        const modalEl = document.getElementById('modalImagePreview');
        if (!previewModalInstance && modalEl) {
            previewModalInstance = new bootstrap.Modal(modalEl, {
                backdrop: true,
                keyboard: true
            });
        }
        return previewModalInstance;
    }

    function previewImage(url, title) {
        const img = document.getElementById('modalPreviewImage');
        const spinner = document.getElementById('previewLoadingSpinner');
        const fallback = document.getElementById('previewFallbackMsg');
        const titleEl = document.getElementById('modalPreviewTitle');
        const btnDownload = document.getElementById('btnLightboxDownload');

        titleEl.textContent = title || 'Dokumentasi Kegiatan';

        if (!url || url.trim() === '' || url === 'null' || url === 'undefined') {
            img.style.display = 'none';
            img.src = '';
            spinner.style.display = 'none';
            fallback.style.display = 'block';
            btnDownload.style.display = 'none';
            getPreviewModalInstance().show();
            return;
        }

        // Format and normalize URL if needed
        let finalUrl = url.trim();
        if (!finalUrl.startsWith('http://') && !finalUrl.startsWith('https://') && !finalUrl.startsWith('/')) {
            finalUrl = '/' + finalUrl;
        }

        // Reset state
        fallback.style.display = 'none';
        img.style.display = 'none';
        spinner.style.display = 'block';

        btnDownload.href = finalUrl;
        btnDownload.style.display = 'inline-flex';

        // Load image
        img.src = finalUrl;
        getPreviewModalInstance().show();
    }

    function onPreviewImageLoad() {
        const img = document.getElementById('modalPreviewImage');
        const spinner = document.getElementById('previewLoadingSpinner');
        const fallback = document.getElementById('previewFallbackMsg');

        spinner.style.display = 'none';
        fallback.style.display = 'none';
        img.style.display = 'block';
    }

    function onPreviewImageError() {
        const img = document.getElementById('modalPreviewImage');
        const spinner = document.getElementById('previewLoadingSpinner');
        const fallback = document.getElementById('previewFallbackMsg');
        const btnDownload = document.getElementById('btnLightboxDownload');

        spinner.style.display = 'none';
        img.style.display = 'none';
        fallback.style.display = 'block';
        btnDownload.style.display = 'none';
    }

    async function uploadDirectDoc(input) {
        if (!input.files || input.files.length === 0 || !currentDetailEventId) return;

        if (!validateSelectedFiles(input)) {
            return;
        }

        const formData = new FormData();
        for (let i = 0; i < input.files.length; i++) {
            formData.append('files[]', input.files[i]);
        }
        formData.append('_token', csrfToken);

        try {
            Swal.fire({
                title: 'Mengunggah...',
                text: 'Sedang menyimpan file dokumentasi',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const res = await fetch(`/events/${currentDetailEventId}/upload`, {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            });

            const data = await res.json();
            if (data.success) {
                Swal.fire('Berhasil', data.message, 'success');
                openDetailModal(currentDetailEventId, 'docs');
            } else {
                Swal.fire('Gagal', data.message || 'Gagal mengunggah foto', 'error');
            }
        } catch (err) {
            Swal.fire('Error', 'Terjadi kesalahan sistem saat upload', 'error');
        }
    }

    async function openEditModal(id) {
        try {
            const response = await fetch(`/events/${id}`, {
                headers: { 'Accept': 'application/json' }
            });
            const res = await response.json();

            if (res.success) {
                const e = res.event;
                const form = document.getElementById('formEditEvent');
                form.action = `/events/${id}`;

                document.getElementById('editTitle').value = e.title;
                document.getElementById('editEventDate').value = e.event_date ? e.event_date.split('T')[0] : '';
                
                const selComp = document.getElementById('selectCompanyEdit');
                if (selComp && e.company) {
                    let found = false;
                    for (let i = 0; i < selComp.options.length; i++) {
                        if (selComp.options[i].value === e.company) {
                            selComp.selectedIndex = i;
                            found = true;
                            break;
                        }
                    }
                    if (!found) {
                        selComp.add(new Option(e.company, e.company, true, true));
                    }
                }

                document.getElementById('editStatus').value = e.status;
                document.getElementById('editDescription').value = e.description || '';

                new bootstrap.Modal(document.getElementById('modalEditEvent')).show();
            }
        } catch (err) {
            Swal.fire('Error', 'Gagal memuat data kegiatan untuk diedit', 'error');
        }
    }

    function confirmDeleteEvent(id, title) {
        Swal.fire({
            title: 'Hapus Kegiatan?',
            html: `Apakah Anda yakin ingin menghapus kegiatan <b>"${title}"</b>?<br><small class="text-danger">Tindakan ini tidak dapat dibatalkan dan seluruh dokumentasi terkait akan terhapus.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('deleteEventForm');
                form.action = `/events/${id}`;
                form.submit();
            }
        });
    }

    // Validasi Ukuran File di Client-Side (Maksimal 15 MB per berkas)
    const MAX_FILE_SIZE_BYTES = 15 * 1024 * 1024; // 15 MB
    const ALLOWED_EXTENSIONS = ['.jpg', '.jpeg', '.png', '.webp', '.gif', '.pdf', '.doc', '.docx', '.xls', '.xlsx'];

    // In-memory DataTransfer containers to support individual file removals
    const selectedFilesMap = {
        tambah: new DataTransfer(),
        edit: new DataTransfer()
    };

    function handleFilesSelected(input, type) {
        if (!input.files || input.files.length === 0) return;

        const dt = selectedFilesMap[type];

        // Validasi dan masukkan file baru ke DataTransfer jika belum ada
        for (let i = 0; i < input.files.length; i++) {
            const file = input.files[i];
            const ext = '.' + file.name.split('.').pop().toLowerCase();

            // Cek ekstensi / format
            if (!ALLOWED_EXTENSIONS.includes(ext)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Format File Tidak Didukung',
                    html: `Berkas <b>"${escapeHtml(file.name)}"</b> memiliki format yang tidak didukung.<br><small class="text-muted">Format yang diizinkan: JPG, JPEG, PNG, WEBP, PDF, DOC, DOCX, XLS, XLSX</small>`,
                });
                continue;
            }

            // Cek ukuran file (> 15 MB)
            if (file.size > MAX_FILE_SIZE_BYTES) {
                const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                Swal.fire({
                    icon: 'error',
                    title: 'Ukuran File Terlalu Besar',
                    html: `Berkas <b>"${escapeHtml(file.name)}"</b> berukuran <b>${fileSizeMB} MB</b>.<br><span class="text-danger">Maksimal ukuran berkas adalah 15 MB per file.</span>`,
                });
                continue;
            }

            // Hindari duplikat file dengan nama dan ukuran sama
            let alreadyExists = false;
            for (let j = 0; j < dt.files.length; j++) {
                if (dt.files[j].name === file.name && dt.files[j].size === file.size) {
                    alreadyExists = true;
                    break;
                }
            }

            if (!alreadyExists) {
                dt.items.add(file);
            }
        }

        // Sinkronkan input file dengan DataTransfer
        input.files = dt.files;
        renderFilePreviewBadges(type);
    }

    function removeSelectedFile(index, type) {
        const dt = selectedFilesMap[type];
        const newDt = new DataTransfer();

        for (let i = 0; i < dt.files.length; i++) {
            if (i !== index) {
                newDt.items.add(dt.files[i]);
            }
        }

        selectedFilesMap[type] = newDt;
        const inputId = type === 'tambah' ? 'inputDocsTambah' : 'inputDocsEdit';
        const input = document.getElementById(inputId);
        if (input) {
            input.files = newDt.files;
        }

        renderFilePreviewBadges(type);
    }

    function renderFilePreviewBadges(type) {
        const containerId = type === 'tambah' ? 'previewFilesTambahContainer' : 'previewFilesEditContainer';
        const container = document.getElementById(containerId);
        if (!container) return;

        container.innerHTML = '';
        const dt = selectedFilesMap[type];

        if (dt.files.length === 0) return;

        Array.from(dt.files).forEach((file, index) => {
            const ext = file.name.split('.').pop().toLowerCase();
            let iconClass = 'bi-file-earmark text-primary';
            if (['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(ext)) {
                iconClass = 'bi-file-earmark-image text-success';
            } else if (ext === 'pdf') {
                iconClass = 'bi-file-earmark-pdf text-danger';
            } else if (['doc', 'docx'].includes(ext)) {
                iconClass = 'bi-file-earmark-word text-primary';
            } else if (['xls', 'xlsx'].includes(ext)) {
                iconClass = 'bi-file-earmark-excel text-success';
            }

            const sizeText = file.size > 1024 * 1024 
                ? (file.size / (1024 * 1024)).toFixed(1) + ' MB'
                : Math.round(file.size / 1024) + ' KB';

            const badge = document.createElement('div');
            badge.className = 'd-inline-flex align-items-center gap-2 py-1 px-2 rounded-2 border bg-white shadow-sm';
            badge.style.fontSize = '12px';
            badge.innerHTML = `
                <i class="bi ${iconClass}"></i>
                <span class="text-truncate fw-medium" style="max-width: 220px;" title="${escapeHtml(file.name)}">${escapeHtml(file.name)}</span>
                <span class="text-muted" style="font-size: 10.5px;">(${sizeText})</span>
                <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-1" style="font-size: 13px; line-height: 1;" onclick="removeSelectedFile(${index}, '${type}')" title="Hapus berkas ini">
                    <i class="bi bi-x-circle-fill"></i>
                </button>
            `;
            container.appendChild(badge);
        });
    }

    // Reset DataTransfer saat modal ditutup
    document.addEventListener('DOMContentLoaded', function() {
        const modalTambah = document.getElementById('modalTambahEvent');
        if (modalTambah) {
            modalTambah.addEventListener('hidden.bs.modal', function() {
                selectedFilesMap.tambah = new DataTransfer();
                const inp = document.getElementById('inputDocsTambah');
                if (inp) inp.value = '';
                const c = document.getElementById('previewFilesTambahContainer');
                if (c) c.innerHTML = '';
            });
        }

        const modalEdit = document.getElementById('modalEditEvent');
        if (modalEdit) {
            modalEdit.addEventListener('hidden.bs.modal', function() {
                selectedFilesMap.edit = new DataTransfer();
                const inp = document.getElementById('inputDocsEdit');
                if (inp) inp.value = '';
                const c = document.getElementById('previewFilesEditContainer');
                if (c) c.innerHTML = '';
            });
        }
    });

    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            timer: 3000,
            showConfirmButton: false
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: "{{ session('error') }}",
        });
    @endif
</script>
@endpush
