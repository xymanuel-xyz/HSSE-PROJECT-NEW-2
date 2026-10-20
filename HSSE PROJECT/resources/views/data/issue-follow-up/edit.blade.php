@extends('layouts.app', [
    'title' => 'Edit Temuan ' . $issue->issue_code . ' - Issue & Follow Up',
    'activeModule' => 'issue-follow-up'
])

@push('styles')
<style>
    .ifu-form-page {
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
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        box-shadow: 0 6px 16px rgba(2, 132, 199, 0.22);
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
        color: #0369a1;
        text-decoration: none;
        font-weight: 600;
    }

    .ifu-form-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 24px 28px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
    }

    .ifu-form-section-title {
        font-size: 14px;
        font-weight: 800;
        color: #0f172a;
        padding-bottom: 10px;
        margin-bottom: 16px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .ifu-form-label {
        font-size: 12px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 6px;
    }

    .ifu-control {
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        padding: 9px 12px;
        font-size: 13px;
        color: #1e293b;
    }

    .ifu-control:focus {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
    }

    .ifu-control-disabled {
        background-color: #f1f5f9 !important;
        color: #94a3b8 !important;
        cursor: not-allowed !important;
        border-color: #e2e8f0 !important;
    }

    .btn-ifu-primary {
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        color: #ffffff;
        border: none;
        border-radius: 10px;
        padding: 10px 20px;
        font-size: 13px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-ifu-primary:hover {
        color: #ffffff;
        background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
    }

    .btn-ifu-secondary {
        background: #ffffff;
        color: #334155;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 9px 16px;
        font-size: 13px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-ifu-secondary:hover {
        background: #f8fafc;
        color: #0f172a;
    }
</style>
@endpush

@section('content')
<div class="ifu-form-page">
    <div class="ifu-header">
        <div class="ifu-title-group">
            <div class="ifu-icon-box">
                <i class="bi bi-pencil-square"></i>
            </div>
            <div>
                <h1 class="ifu-page-title mb-0">Edit Temuan</h1>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('issue-follow-up.show', $issue->id) }}" class="btn-ifu-secondary">
                <i class="bi bi-eye"></i> Lihat Detail
            </a>
            <a href="{{ route('issue-follow-up.index') }}" class="btn-ifu-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger rounded-3 border-0 shadow-sm mb-4">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Periksa kembali isian formulir:</div>
            <ul class="mb-0 ps-3 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="ifu-form-card">
        <form action="{{ route('issue-follow-up.update', $issue->id) }}" method="POST" enctype="multipart/form-data" id="formEditTemuan">
            @csrf
            @method('PUT')

            <div class="ifu-form-section-title">
                <i class="bi bi-clipboard-check text-primary"></i> Informasi Utama Temuan
            </div>

            <div class="row g-3 mb-4">
                {{-- Tanggal Temuan --}}
                <div class="col-md-4">
                    <label class="form-label ifu-form-label">Tanggal Temuan <span class="text-danger">*</span></label>
                    <input type="date" name="reported_date" class="form-control ifu-control @error('reported_date') is-invalid @enderror" value="{{ old('reported_date', $issue->reported_date ? $issue->reported_date->format('Y-m-d') : '') }}" required>
                    @error('reported_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Fungsi Safeguard (Health & Safety, Security, Environment, Social) --}}
                <div class="col-md-4">
                    <label class="form-label ifu-form-label">Fungsi Safeguard <span class="text-danger">*</span></label>
                    <select name="hsse_division" class="form-select ifu-control @error('hsse_division') is-invalid @enderror" required>
                        <option value="">-- Pilih Fungsi Safeguard --</option>
                        @foreach($functions as $funcCode => $funcLabel)
                            <option value="{{ $funcCode }}" @selected(old('hsse_division', $issue->function_label) === $funcCode)>{{ $funcLabel }}</option>
                        @endforeach
                    </select>
                    @error('hsse_division')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Sumber Temuan --}}
                <div class="col-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label ifu-form-label mb-0">Sumber Temuan <span class="text-danger">*</span></label>
                        <button type="button" class="btn btn-link btn-sm p-0 text-success text-decoration-none" onclick="promptAddSource()" style="font-size: 11px;">
                            <i class="bi bi-plus-circle me-1"></i>Tambah Sumber
                        </button>
                    </div>
                    <select name="category" id="categorySourceSelect" class="form-select ifu-control @error('category') is-invalid @enderror" required>
                        <option value="">-- Pilih Sumber Temuan --</option>
                        @foreach($sources as $src)
                            <option value="{{ $src }}" @selected(old('category', $issue->category) === $src)>{{ $src }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Judul Temuan --}}
                <div class="col-md-8">
                    <label class="form-label ifu-form-label">Judul Temuan <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control ifu-control @error('title') is-invalid @enderror" value="{{ old('title', $issue->title) }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Lokasi Spesifik --}}
                <div class="col-md-4">
                    <label class="form-label ifu-form-label">Lokasi Spesifik <span class="text-danger">*</span></label>
                    <input type="text" name="location" class="form-control ifu-control @error('location') is-invalid @enderror" value="{{ old('location', $issue->location) }}" placeholder="Contoh: Area Turbin Unit 2" required>
                    @error('location')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Deskripsi Temuan --}}
                <div class="col-12">
                    <label class="form-label ifu-form-label">Deskripsi Temuan <span class="text-danger">*</span></label>
                    <textarea name="description" rows="3" class="form-control ifu-control @error('description') is-invalid @enderror" required>{{ old('description', $issue->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="ifu-form-section-title">
                <i class="bi bi-gear-wide-connected text-primary"></i> Status, Tanggal Close &amp; Dokumentasi
            </div>

            <div class="row g-3 mb-4">
                {{-- Status Temuan: Open vs Close --}}
                <div class="col-md-4">
                    <label class="form-label ifu-form-label">Status Temuan <span class="text-danger">*</span></label>
                    @php
                        $currentStat = in_array($issue->status, ['Close', 'Closed'], true) ? 'Close' : 'Open';
                    @endphp
                    <select name="status" id="issueStatusSelect" class="form-select ifu-control @error('status') is-invalid @enderror" required onchange="handleStatusChange(this.value)">
                        <option value="Open" @selected(old('status', $currentStat) === 'Open')>Open</option>
                        <option value="Close" @selected(old('status', $currentStat) === 'Close')>Close</option>
                    </select>
                    <div class="form-text small" id="statusHelpText">
                        Status <strong>Open</strong>: tanggal close terkunci/tidak dapat diisi.
                    </div>
                </div>

                {{-- Tanggal Close --}}
                <div class="col-md-4">
                    <label class="form-label ifu-form-label" id="dueDateLabel">
                        Tanggal Close
                        <span id="dueDateStatusBadge" class="badge bg-secondary ms-1" style="font-size: 10px;">Dinonaktifkan (Open)</span>
                    </label>
                    <input type="date" name="due_date" id="dueDateInput"
                           class="form-control ifu-control @error('due_date') is-invalid @enderror"
                           value="{{ old('due_date', $issue->due_date ? $issue->due_date->format('Y-m-d') : '') }}">
                    <div class="form-text small text-muted" id="dueDateHint">
                        Field Tanggal Close hanya dapat diedit jika status dipilih <strong>Close</strong>.
                    </div>
                    @error('due_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Tambah File Dokumentasi Baru (Multiple) --}}
                <div class="col-md-4">
                    <label class="form-label ifu-form-label">Tambah Dokumentasi Baru (Bisa Pilih Banyak Sekaligus)</label>
                    <input type="file" name="documentation_files[]" id="editDocumentationFiles" multiple class="form-control ifu-control @error('documentation_files') is-invalid @enderror @error('documentation_files.*') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.xls,.xlsx" onchange="handleEditFiles(this)">
                    <div class="form-text small text-muted">Format: JPG, PNG, WEBP, PDF, DOC, DOCX, XLS, XLSX (Maks. 15 MB/file).</div>
                    @error('documentation_files')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @error('documentation_files.*')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <div id="editPreviewBox" class="mt-2 d-none">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold small text-dark" style="font-size:11.5px;">File Baru Dipilih (<span id="editCount">0</span>):</span>
                            <button type="button" class="btn btn-link btn-sm text-danger text-decoration-none p-0" style="font-size:11px;" onclick="clearEditFiles()">
                                Reset
                            </button>
                        </div>
                        <div class="d-flex flex-column gap-1" id="editList" style="max-height: 150px; overflow-y: auto;"></div>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label ifu-form-label">Catatan Perubahan / Tindak Lanjut Tambahan</label>
                    <input type="text" name="notes" class="form-control ifu-control @error('notes') is-invalid @enderror" value="" placeholder="Isi catatan jika ada pembaruan...">
                    @if($issue->notes)
                        <div class="form-text small text-muted">Catatan terakhir: "{{ \Illuminate\Support\Str::limit($issue->notes, 100) }}"</div>
                    @endif
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="{{ route('issue-follow-up.show', $issue->id) }}" class="btn-ifu-secondary">Batal</a>
                <button type="submit" class="btn-ifu-primary">
                    <i class="bi bi-save me-1"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function handleStatusChange(status) {
        const dueInput = document.getElementById('dueDateInput');
        const dueBadge = document.getElementById('dueDateStatusBadge');
        const dueHint = document.getElementById('dueDateHint');
        const statusHelp = document.getElementById('statusHelpText');

        if (status === 'Close') {
            dueInput.disabled = false;
            dueInput.readOnly = false;
            dueInput.classList.remove('ifu-control-disabled');
            dueBadge.className = 'badge bg-success ms-1';
            dueBadge.textContent = 'Dapat Diedit (Close)';
            dueHint.innerHTML = 'Silakan perbarui atau tetapkan tanggal tenggat penyelesaian.';
            statusHelp.innerHTML = 'Status <strong>Close</strong>: temuan diselesaikan dan tanggal tenggat dapat diisi.';
            if (!dueInput.value) {
                dueInput.value = "{{ date('Y-m-d') }}";
            }
        } else {
            // Status Open: tanggal tenggat tidak dapat diedit atau diisi
            dueInput.value = '';
            dueInput.disabled = true;
            dueInput.readOnly = true;
            dueInput.classList.add('ifu-control-disabled');
            dueBadge.className = 'badge bg-secondary ms-1';
            dueBadge.textContent = 'Dinonaktifkan (Open)';
            dueHint.innerHTML = 'Field tanggal tenggat tidak dapat diisi saat status <strong>Open</strong>.';
            statusHelp.innerHTML = 'Status <strong>Open</strong>: temuan aktif (tenggat tidak dapat diisi).';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const initialStatus = document.getElementById('issueStatusSelect')?.value || 'Open';
        handleStatusChange(initialStatus);
    });

    function promptAddSource() {
        Swal.fire({
            title: 'Tambah Sumber Temuan Baru',
            input: 'text',
            inputLabel: 'Nama Sumber Temuan',
            inputPlaceholder: 'Contoh: Audit Eksternal ISO 45001',
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#059669',
            cancelButtonColor: '#64748b',
            inputValidator: (val) => {
                if (!val || !val.trim()) {
                    return 'Nama sumber temuan wajib diisi.';
                }
            }
        }).then(async (result) => {
            if (!result.isConfirmed) return;
            try {
                const res = await fetch("{{ route('issue-follow-up.sources.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name: result.value.trim() })
                });
                const data = await res.json();
                if (data.success && data.source) {
                    const select = document.getElementById('categorySourceSelect');
                    const opt = document.createElement('option');
                    opt.value = data.source.name;
                    opt.textContent = data.source.name;
                    opt.selected = true;
                    select.appendChild(opt);
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('Error', data.message || 'Gagal menyimpan sumber temuan.', 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
            }
        });
    }

    let editSelectedFiles = [];

    function handleEditFiles(input) {
        if (!input.files || input.files.length === 0) return;

        const allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
        const maxBytes = 15 * 1024 * 1024;
        let errors = [];

        Array.from(input.files).forEach(f => {
            const ext = f.name.split('.').pop().toLowerCase();
            if (!allowed.includes(ext)) {
                errors.push(`File ${f.name}: Format tidak didukung.`);
                return;
            }
            if (f.size > maxBytes) {
                errors.push(`File ${f.name}: Ukuran melebihi 15 MB.`);
                return;
            }
            if (!editSelectedFiles.some(item => item.name === f.name && item.size === f.size)) {
                editSelectedFiles.push(f);
            }
        });

        if (errors.length > 0) {
            Swal.fire({ icon: 'warning', title: 'File Ditolak', html: errors.join('<br>') });
        }

        renderEditFiles();
        syncEditInput();
    }

    function removeEditFile(index) {
        if (index >= 0 && index < editSelectedFiles.length) {
            editSelectedFiles.splice(index, 1);
            renderEditFiles();
            syncEditInput();
        }
    }

    function clearEditFiles() {
        editSelectedFiles = [];
        renderEditFiles();
        syncEditInput();
    }

    function syncEditInput() {
        const input = document.getElementById('editDocumentationFiles');
        if (!input) return;
        try {
            const dt = new DataTransfer();
            editSelectedFiles.forEach(f => dt.items.add(f));
            input.files = dt.files;
        } catch (e) {}
    }

    function renderEditFiles() {
        const box = document.getElementById('editPreviewBox');
        const list = document.getElementById('editList');
        const count = document.getElementById('editCount');
        if (!box || !list || !count) return;

        count.textContent = editSelectedFiles.length;
        if (editSelectedFiles.length === 0) {
            box.classList.add('d-none');
            list.innerHTML = '';
            return;
        }

        box.classList.remove('d-none');
        list.innerHTML = editSelectedFiles.map((f, i) => `
            <div class="d-flex align-items-center justify-content-between p-1 px-2 rounded bg-light border small">
                <span class="text-truncate me-2" style="max-width: 200px;">${f.name}</span>
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="removeEditFile(${i})">&times;</button>
            </div>
        `).join('');
    }
</script>
@endpush
