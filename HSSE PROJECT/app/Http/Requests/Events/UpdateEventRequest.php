<?php

namespace App\Http\Requests\Events;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:150'],
            'event_date' => ['required', 'date'],
            'status' => ['required', 'in:Direncanakan,Berlangsung,Selesai,Dibatalkan'],
            'description' => ['nullable', 'string'],
            'pic' => ['nullable', 'string', 'max:255'],
            'documentation_files'   => ['nullable', 'array'],
            'documentation_files.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx', 'max:15360'],
            // Field opsional / backward compatibility
            'category' => ['nullable', 'string', 'max:100'],
            'unit' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:255'],
            'participants' => ['nullable', 'integer', 'min:0'],
            'start_time' => ['nullable'],
            'end_time' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul kegiatan wajib diisi.',
            'company.required' => 'Nama perusahaan / instansi wajib dipilih atau diisi.',
            'event_date.required' => 'Tanggal kegiatan wajib diisi.',
            'event_date.date' => 'Format tanggal kegiatan tidak valid.',
            'status.required' => 'Status kegiatan wajib dipilih.',
            'documentation_files.*.max' => 'Ukuran file dokumentasi maksimal 15 MB per file.',
            'documentation_files.*.mimes' => 'Format file yang didukung: JPG, JPEG, PNG, WEBP, PDF, DOC, DOCX, XLS, XLSX.',
        ];
    }
}

