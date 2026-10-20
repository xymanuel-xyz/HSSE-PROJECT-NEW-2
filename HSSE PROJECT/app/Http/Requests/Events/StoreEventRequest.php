<?php

namespace App\Http\Requests\Events;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (!$this->filled('status')) {
            $this->merge([
                'status' => 'Selesai',
            ]);
        }
        if (!$this->filled('category')) {
            $this->merge([
                'category' => 'Kegiatan',
            ]);
        }
        if (!$this->filled('unit')) {
            $this->merge([
                'unit' => 'PMU',
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:150'], // Nama Perusahaan / Instansi
            'event_date' => ['required', 'date'],
            'status' => ['required', 'in:Direncanakan,Berlangsung,Selesai,Dibatalkan'],
            'description' => ['nullable', 'string'],
            'documentation_files'   => ['nullable', 'array'],
            'documentation_files.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx', 'max:15360'],
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
            'documentation_files.*.max' => 'Ukuran file dokumentasi maksimal 15 MB per file.',
            'documentation_files.*.mimes' => 'Format file yang didukung: JPG, JPEG, PNG, WEBP, PDF, DOC, DOCX, XLS, XLSX.',
        ];
    }
}

