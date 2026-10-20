<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GasMonitoring extends Model
{
    use HasFactory;

    protected $table = 'gas_monitorings';

    public const NAB_CO = 25.0;       // ppm
    public const NAB_H2S = 10.0;      // ppm
    public const NAB_PRESSURE = 20.0; // barg

    public const WELL_PADS = [
        'Well Pad 7',
        'Well Pad 28',
        'Well Pad 30',
        'Well Pad 31',
    ];

    protected $fillable = [
        'measurement_date',
        'well_pad',
        'co_level',
        'h2s_level',
        'pressure',
        'co_status',
        'h2s_status',
        'pressure_status',
        'notes',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'measurement_date' => 'date:Y-m-d',
            'co_level' => 'float',
            'h2s_level' => 'float',
            'pressure' => 'float',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (GasMonitoring $item) {
            $item->co_level = (float) $item->co_level;
            $item->h2s_level = (float) $item->h2s_level;
            $item->pressure = (float) $item->pressure;

            $item->co_status = $item->co_level <= self::NAB_CO ? 'Sesuai' : 'Tidak Sesuai';
            $item->h2s_status = $item->h2s_level <= self::NAB_H2S ? 'Sesuai' : 'Tidak Sesuai';
            $item->pressure_status = $item->pressure <= self::NAB_PRESSURE ? 'Sesuai' : 'Tidak Sesuai';
        });
    }

    public function scopeByMonthYear(Builder $query, int $month, int $year): Builder
    {
        return $query->whereMonth('measurement_date', $month)
            ->whereYear('measurement_date', $year);
    }

    public function scopeByWellPad(Builder $query, ?string $wellPad): Builder
    {
        if (!empty($wellPad) && $wellPad !== 'all') {
            return $query->where('well_pad', $wellPad);
        }
        return $query;
    }
}

