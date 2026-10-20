<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'document_type',
        'module',
        'record_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'uploaded_by',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'record_id' => 'integer',
        ];
    }

    public function getFileUrlAttribute(): string
    {
        if (empty($this->file_path)) {
            return '';
        }

        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }

        return asset('storage/' . ltrim($this->file_path, '/'));
    }
}

