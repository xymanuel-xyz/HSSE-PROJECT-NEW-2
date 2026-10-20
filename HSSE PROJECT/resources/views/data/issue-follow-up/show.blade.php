@extends('layouts.app', [
    'title' => 'Detail Temuan ' . $issue->issue_code . ' - Issue & Follow Up',
    'activeModule' => 'issue-follow-up'
])

@push('styles')
<style>
    .ifu-detail-page {
        color: #1e293b;
        padding-bottom: 36px;
    }

    .ifu-header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        margin-bottom: 22px;
    }

    .ifu-title-group {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .ifu-icon-box {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: linear-gradient(135deg, #047857 0%, #059669 100%);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        box-shadow: 0 6px 16px rgba(4, 120, 87, 0.22);
    }

    .ifu-page-title {
        font-size: 21px;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 2px 0;
    }

    .ifu-breadcrumb {
        font-size: 12.5px;
        color: #64748b;
        margin: 0;
    }

    .ifu-breadcrumb a {
        color: #047857;
        text-decoration: none;
        font-weight: 600;
    }

    .ifu-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 22px 24px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
        margin-bottom: 20px;
    }

    .ifu-card-title {
        font-size: 15px;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 16px 0;
        padding-bottom: 12px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .ifu-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .ifu-badge.status-open {
        background: #fef3c7;
        color: #b45309;
        border: 1px solid #fde68a;
    }

    .ifu-badge.status-closed {
        background: #d1fae5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }

    .ifu-badge.status-overdue {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .ifu-meta-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    @media (max-width: 992px) {
        .ifu-meta-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 576px) {
        .ifu-meta-grid {
            grid-template-columns: 1fr;
        }
    }

    .ifu-meta-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 14px;
    }

    .ifu-meta-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #64748b;
        margin-bottom: 4px;
    }

    .ifu-meta-val {
        font-size: 13.5px;
        font-weight: 700;
        color: #0f172a;
    }

    .ifu-timeline {
        position: relative;
        padding-left: 24px;
    }

    .ifu-timeline::before {
        content: "";
        position: absolute;
        left: 7px;
        top: 6px;
        bottom: 6px;
        width: 2px;
        background: #cbd5e1;
    }

    .ifu-timeline-item {
        position: relative;
        margin-bottom: 16px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 13px 16px;
    }

    .ifu-timeline-item::before {
        content: "";
        position: absolute;
        left: -21px;
        top: 15px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #059669;
        border: 2px solid #ffffff;
        box-shadow: 0 0 0 2px #059669;
    }

    .doc-item-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        margin-bottom: 10px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .doc-item-box:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
    }

    .btn-ifu {
        border-radius: 10px;
        padding: 8px 15px;
        font-size: 12.5px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        text-decoration: none;
        border: 1px solid transparent;
    }

    .btn-ifu-primary {
        background: linear-gradient(135deg, #047857 0%, #059669 100%);
        color: #ffffff;
    }

    .btn-ifu-primary:hover {
        color: #ffffff;
        background: linear-gradient(135deg, #065f46 0%, #047857 100%);
    }

    .btn-ifu-outline {
        background: #ffffff;
        color: #334155;
        border-color: #cbd5e1;
    }

    .btn-ifu-outline:hover {
        background: #f8fafc;
        color: #0f172a;
    }
</style>
@endpush

@section('content')
<div class="ifu-detail-page">
    <div class="ifu-header">
        <div class="ifu-title-group">
            <div class="ifu-icon-box">
                <i class="bi bi-clipboard-check-fill"></i>
            </div>
            <div>
                <h1 class="ifu-page-title mb-0">Detail Temuan {{ $issue->issue_code }}</h1>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('issue-follow-up.index') }}" class="btn-ifu btn-ifu-outline">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar
            </a>
            @adminManagement
                <a href="{{ route('issue-follow-up.edit', $issue->id) }}" class="btn-ifu btn-ifu-outline">
                    <i class="bi bi-pencil"></i> Edit Temuan
                </a>
                <form action="{{ route('issue-follow-up.destroy', $issue->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus temuan {{ $issue->issue_code }} beserta seluruh riwayat tindak lanjutnya?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-ifu btn-danger text-white">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </form>
            @endadminManagement
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 border-0 shadow-sm mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- KOLOM KIRI: INFORMASI UTAMA, DOKUMENTASI & RIWAYAT TINDAK LANJUT --}}
        <div class="col-12 col-xl-8">
            <div class="ifu-card">
                <div class="ifu-card-title">
                    <div>
                        <span class="text-muted small d-block fw-normal">Judul Temuan</span>
                        <span class="fs-5">{{ $issue->title }}</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @php
                            $isClose = in_array($issue->status, ['Close', 'Closed'], true);
                            $badgeClass = $isClose ? 'status-closed' : 'status-open';
                            $statusDisplay = $isClose ? 'Close' : 'Open';
                        @endphp
                        <span class="ifu-badge {{ $badgeClass }}">
                            {{ $statusDisplay }}
                        </span>
                        @if($issue->isOverdue())
                            <span class="ifu-badge status-overdue">
                                <i class="bi bi-alarm-fill"></i> Overdue
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Metadata Grid (Fungsi HSSE, Sumber Temuan, Tanggal, Tenggat) --}}
                <div class="ifu-meta-grid">
                    <div class="ifu-meta-box">
                        <div class="ifu-meta-label">Fungsi HSSE</div>
                        <div class="ifu-meta-val">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $issue->function_label }}</span>
                        </div>
                    </div>
                    <div class="ifu-meta-box">
                        <div class="ifu-meta-label">Sumber Temuan</div>
                        <div class="ifu-meta-val text-dark">{{ $issue->source_label }}</div>
                    </div>
                    <div class="ifu-meta-box">
                        <div class="ifu-meta-label">Tanggal Temuan</div>
                        <div class="ifu-meta-val">{{ $issue->reported_date ? $issue->reported_date->format('d F Y') : '-' }}</div>
                    </div>
                    <div class="ifu-meta-box">
                        <div class="ifu-meta-label">Tanggal Tenggat Follow Up</div>
                        <div class="ifu-meta-val {{ $issue->isOverdue() ? 'text-danger' : '' }}">
                            @if($isClose && $issue->due_date)
                                {{ $issue->due_date->format('d F Y') }}
                                @if($issue->isOverdue())
                                    <span class="badge bg-danger ms-1">Terlambat</span>
                                @endif
                            @elseif(!$isClose)
                                <span class="text-muted fst-italic">Dinonaktifkan (Open)</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if($issue->location)
                    <div class="mb-3">
                        <div class="ifu-meta-label mb-1">Lokasi Spesifik</div>
                        <div class="text-dark small"><i class="bi bi-geo-alt text-danger me-1"></i>{{ $issue->location }}</div>
                    </div>
                @endif

                <div class="mb-3">
                    <div class="ifu-meta-label mb-1">Deskripsi Temuan</div>
                    <div class="p-3 rounded-3 bg-light border text-dark" style="font-size: 13.5px; line-height: 1.6;">
                        {{ $issue->description }}
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-between text-muted small pt-3 border-top">
                    <span>Dibuat pada: <strong>{{ $issue->created_at ? $issue->created_at->format('d/m/Y H:i') : '-' }}</strong></span>
                    <span>Diperbarui terakhir: <strong>{{ $issue->updated_at ? $issue->updated_at->format('d/m/Y H:i') : '-' }}</strong></span>
                    @if($isClose && $issue->closed_date)
                        <span class="text-success fw-semibold">
                            Ditutup pada: {{ $issue->closed_date->format('d/m/Y') }}
                            {{ $issue->closed_by ? 'oleh ' . $issue->closed_by : '' }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- SECTION: ADD DOCUMENTATION (FITUR DOKUMENTASI LENGKAP) --}}
            <div class="ifu-card">
                <div class="ifu-card-title">
                    <div>
                        <i class="bi bi-file-earmark-arrow-up-fill text-success me-2"></i>
                        <span>Dokumentasi Temuan</span>
                        <span class="badge bg-secondary rounded-pill ms-1">{{ $issue->documentations->count() }} File</span>
                    </div>
                    @adminManagement
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="openAddDocModal()">
                            <i class="bi bi-plus-lg me-1"></i> Add Documentation
                        </button>
                    @endadminManagement
                </div>

                <div id="documentationList">
                    @forelse($issue->documentations as $doc)
                        <div class="doc-item-box">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 p-2 bg-light text-primary border" style="font-size: 20px;">
                                    @if($doc->is_image)
                                        <i class="bi bi-file-earmark-image-fill text-success"></i>
                                    @elseif(str_contains(strtolower($doc->mime_type ?? ''), 'pdf'))
                                        <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                                    @else
                                        <i class="bi bi-file-earmark-text-fill text-primary"></i>
                                    @endif
                                </div>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 13px;">{{ $doc->original_name }}</div>
                                    <div class="text-muted" style="font-size: 11px;">
                                        <span>{{ $doc->formatted_size }}</span> &bull;
                                        <span>Diupload: {{ $doc->created_at ? $doc->created_at->format('d/m/Y H:i') : '-' }}</span>
                                        @if($doc->uploaded_by)
                                            &bull; <span>Oleh: <strong>{{ $doc->uploaded_by }}</strong></span>
                                        @endif
                                    </div>
                                    @if($doc->caption && $doc->caption !== $doc->original_name)
                                        <div class="text-secondary small fst-italic mt-1">{{ $doc->caption }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ route('issue-follow-up.documentation.view', $doc->id) }}" target="_blank" class="btn btn-sm btn-light border" title="Lihat / Pratinjau File">
                                    <i class="bi bi-eye text-primary"></i>
                                </a>
                                <a href="{{ route('issue-follow-up.documentation.download', $doc->id) }}" class="btn btn-sm btn-light border" title="Unduh File">
                                    <i class="bi bi-download text-success"></i>
                                </a>
                                @adminManagement
                                    <form action="{{ route('issue-follow-up.documentation.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumentasi ini?');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light border text-danger" title="Hapus Dokumentasi">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endadminManagement
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            <i class="bi bi-folder-x d-block fs-2 mb-2 text-secondary"></i>
                            Belum ada file dokumentasi yang ditambahkan untuk temuan ini.
                            @adminManagement
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-success" onclick="openAddDocModal()">
                                        <i class="bi bi-plus-lg me-1"></i> Tambah Dokumentasi Pertama
                                    </button>
                                </div>
                            @endadminManagement
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- RIWAYAT TINDAK LANJUT KRONOLOGIS --}}
            <div class="ifu-card">
                <div class="ifu-card-title">
                    <span><i class="bi bi-clock-history text-primary me-2"></i>Riwayat Tindak Lanjut &amp; Status</span>
                    <span class="badge bg-secondary rounded-pill">{{ $issue->chronologicalLogs->count() }} Catatan</span>
                </div>

                @if($issue->chronologicalLogs->isEmpty())
                    <div class="text-center py-4 text-muted small">
                        Belum ada catatan riwayat tindak lanjut.
                    </div>
                @else
                    <div class="ifu-timeline">
                        @foreach($issue->chronologicalLogs as $log)
                            <div class="ifu-timeline-item">
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark" style="font-size: 13px;">
                                        <i class="bi bi-person-fill text-success me-1"></i>
                                        {{ $log->user_name ?: 'Petugas HSSE' }}
                                    </span>
                                    <span class="text-muted" style="font-size: 11.5px;">
                                        {{ $log->followed_up_at ? $log->followed_up_at->format('d/m/Y H:i') : ($log->created_at ? $log->created_at->format('d/m/Y H:i') : '-') }}
                                    </span>
                                </div>
                                <div class="mb-2">
                                    @php
                                        $displayLogStat = in_array($log->new_status, ['Close', 'Closed'], true) ? 'Close' : 'Open';
                                    @endphp
                                    <span class="badge bg-light text-dark border" style="font-size: 11px;">
                                        Status: <strong>{{ $displayLogStat }}</strong>
                                    </span>
                                </div>
                                <div class="text-dark" style="font-size: 13px; line-height: 1.5;">
                                    {{ $log->note }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- KOLOM KANAN: STATUS TEMUAN --}}
        <div class="col-12 col-xl-4">
            @adminManagement
            @if(!$isClose)
                <div class="ifu-card border-success">
                    <div class="ifu-card-title text-success">
                        <span><i class="bi bi-check2-circle me-2"></i>Tutup Temuan (Mark as Close)</span>
                    </div>
                    <form action="{{ route('issue-follow-up.close', $issue->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Catatan Penutupan</label>
                            <textarea name="note" rows="2" class="form-control form-control-sm" placeholder="Catatan verifikasi penutupan...">Temuan telah diselesaikan dan diverifikasi (Close).</textarea>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm w-100 fw-bold">
                            <i class="bi bi-check2-circle me-1"></i> Tandai Selesai (Close)
                        </button>
                    </form>
                </div>
            @else
                <div class="ifu-card border-warning">
                    <div class="ifu-card-title text-warning">
                        <span><i class="bi bi-arrow-counterclockwise me-2"></i>Buka Kembali Temuan (Reopen)</span>
                    </div>
                    <form action="{{ route('issue-follow-up.reopen', $issue->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Alasan Membuka Kembali <span class="text-danger">*</span></label>
                            <textarea name="note" rows="2" class="form-control form-control-sm" placeholder="Jelaskan alasan temuan dibuka kembali menjadi Open..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning btn-sm w-100 fw-bold text-dark">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Buka Kembali Temuan (Open)
                        </button>
                    </form>
                </div>
            @endif
            @endadminManagement
        </div>
    </div>
</div>

{{-- MODAL ADD DOCUMENTATION --}}
<div class="modal fade" id="addDocModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('issue-follow-up.documentation.store', $issue->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-cloud-arrow-up-fill text-success me-1"></i> Add Documentation
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Pilih File Dokumentasi <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control form-control-sm" required accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.xls,.xlsx">
                        <div class="form-text small">Mendukung format gambar (JPG, PNG, WEBP) atau dokumen (PDF, Word, Excel). Maksimal 10 MB.</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold small">Keterangan / Keterangan Foto (Opsional)</label>
                        <input type="text" name="caption" class="form-control form-control-sm" placeholder="Contoh: Foto kondisi setelah perbaikan">
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm px-3">
                        <i class="bi bi-upload me-1"></i> Unggah Dokumentasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openAddDocModal() {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('addDocModal')).show();
    }
</script>
@endpush
