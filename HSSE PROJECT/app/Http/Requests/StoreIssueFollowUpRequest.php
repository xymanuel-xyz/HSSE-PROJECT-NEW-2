<?php

namespace App\Http\Requests;

use App\Models\IssueFollowUp;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIssueFollowUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Normalisasi Fungsi HSSE
        if ($this->has('hsse_division') || $this->has('hsse_function') || $this->has('function')) {
            $inputVal = $this->input('hsse_function', $this->input('function', $this->input('hsse_division')));
            $raw = strtoupper(trim((string) $inputVal));
            $normalized = match ($raw) {
                'H', 'HSSE', 'HEALTH', 'HEALTH & SAFETY', 'HEALTH & SAVETY', 'HEALTH AND SAFETY', 'H — HEALTH & SAVETY', 'H — HEALTH & SAFETY' => 'Health & Safety',
                'S_SOCIAL', 'S', 'SOSIAL', 'SOCIAL', 'S — SOSIAL', 'S — SOCIAL' => 'Social',
                'S_SECURITY', 'SCURITY', 'SECURITY', 'S — SCURITY', 'S — SECURITY' => 'Security',
                'E', 'EMVIRONMENT', 'ENVIRONMENT', 'E — EMVIRONMENT', 'E — ENVIRONMENT' => 'Environment',
                default => $raw,
            };
            $this->merge([
                'hsse_division' => $normalized,
                'hsse_function' => $normalized,
            ]);
        }

        // Normalisasi Sumber Temuan (category / source)
        if ($this->has('source') && !$this->filled('category')) {
            $this->merge(['category' => $this->input('source')]);
        }

        // Normalisasi Status ('Open' vs 'Close')
        $rawStatus = trim((string) $this->input('status', 'Open'));
        $normStatus = (strcasecmp($rawStatus, 'Close') === 0 || strcasecmp($rawStatus, 'Closed') === 0) ? 'Close' : 'Open';
        $this->merge(['status' => $normStatus]);

        // Aturan Tanggal Tenggat: Jika status Open, tenggat dikosongkan (tidak dapat diisi)
        if ($normStatus === 'Open') {
            $this->merge(['due_date' => null]);
        }

        // Jika attachment kosong atau bukan file yang valid yang diunggah, jangan biarkan empty string memicu validasi upload
        if (!$this->hasFile('attachment')) {
            $this->request->remove('attachment');
        }
    }

    public function rules(): array
    {
        return [
            'hsse_division' => ['required', 'string', Rule::in(['Health & Safety', 'HSSE', 'Social', 'Security', 'Environment'])],
            'reported_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:150'], // Sumber Temuan
            'location' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'status' => ['required', 'string', Rule::in(['Open', 'Close', 'Closed'])],
            'due_date' => [
                'nullable',
                'date',
                'after_or_equal:reported_date',
            ],
            // Field opsional / historis (sudah dihapus dari form utama)
            'unit' => ['nullable', 'string', 'max:50'],
            'pic' => ['nullable', 'string', 'max:255'],
            'priority' => ['nullable', 'string'],
            'action_plan' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx', 'max:15360'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx', 'max:15360'],
            'documentation_files' => ['nullable', 'array'],
            'documentation_files.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx', 'max:15360'],
        ];
    }

    public function messages(): array
    {
        return [
            'hsse_division.required' => 'Fungsi Safeguard (Health & Safety, Security, Environment, atau Social) wajib dipilih.',
            'hsse_division.in' => 'Pilihan Fungsi Safeguard tidak valid. Pilih salah satu dari Health & Safety, Security, Environment, atau Social.',
            'reported_date.required' => 'Tanggal temuan wajib diisi.',
            'reported_date.date' => 'Format tanggal temuan tidak valid.',
            'title.required' => 'Judul temuan wajib diisi.',
            'title.max' => 'Judul temuan maksimal 255 karakter.',
            'category.required' => 'Sumber temuan wajib dipilih.',
            'location.required' => 'Lokasi spesifik wajib diisi.',
            'location.max' => 'Lokasi spesifik maksimal 255 karakter.',
            'description.required' => 'Deskripsi temuan wajib diisi.',
            'status.required' => 'Status temuan wajib dipilih.',
            'status.in' => 'Status temuan harus Open atau Close.',
            'due_date.date' => 'Format tanggal close tidak valid.',
            'due_date.after_or_equal' => 'Tanggal close tidak boleh lebih awal dari tanggal temuan.',
            'attachment.mimes' => 'Lampiran harus berupa file gambar atau dokumen yang valid.',
            'attachment.max' => 'Ukuran file lampiran maksimal 15 MB per file.',
            'attachments.*.mimes' => 'Lampiran harus berupa file gambar atau dokumen yang valid.',
            'attachments.*.max' => 'Ukuran file lampiran maksimal 15 MB per file.',
            'documentation_files.*.mimes' => 'File dokumentasi harus berupa file gambar atau dokumen yang valid (JPG, JPEG, PNG, WEBP, PDF, DOC, DOCX, XLS, XLSX).',
            'documentation_files.*.max' => 'Ukuran tiap file dokumentasi maksimal 15 MB.',
        ];
    }
}
