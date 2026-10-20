<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FindingSource extends Model
{
    use HasFactory;

    protected $table = 'finding_sources';

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Dapatkan semua sumber temuan aktif.
     */
    public static function getActiveSources(): array
    {
        return static::where('is_active', true)
            ->orderBy('name', 'asc')
            ->pluck('name')
            ->all();
    }
}

