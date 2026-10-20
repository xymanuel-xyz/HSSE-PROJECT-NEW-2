<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class IssueDocumentation extends Model
{
    use HasFactory;

    protected $table = 'issue_documentations';

    protected $fillable = [
        'issue_follow_up_id',
        'file_name',
        'original_name',
        'file_path',
        'file_size',
        'mime_type',
        'uploaded_by',
        'caption',
    ];

    protected $appends = [
        'file_url',
        'formatted_size',
        'is_image',
    ];

    public function issueFollowUp(): BelongsTo
    {
        return $this->belongsTo(IssueFollowUp::class, 'issue_follow_up_id');
    }

    public function getFileUrlAttribute(): string
    {
        if (empty($this->file_path)) {
            return '';
        }

        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }

        return Storage::disk('public')->url($this->file_path);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }

    public function getIsImageAttribute(): bool
    {
        if (!empty($this->mime_type) && str_starts_with($this->mime_type, 'image/')) {
            return true;
        }

        $ext = strtolower(pathinfo($this->file_name ?? $this->file_path, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true);
    }
}

