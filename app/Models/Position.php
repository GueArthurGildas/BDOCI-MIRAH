<?php

namespace App\Models;

use App\Enums\TrackingSystem;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Carbon;

#[Fillable([
    'vessel_id', 'tracking_device_id', 'zone_id', 'system',
    'recorded_at', 'latitude', 'longitude', 'speed_knots', 'course',
])]
class Position extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'system' => TrackingSystem::class,
            'recorded_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
            'speed_knots' => 'float',
        ];
    }

    public function scopeLatestPerVessel(Builder $query, Carbon $at): void
    {
        $latestRecords = self::query()
            ->selectRaw('vessel_id, max(recorded_at) as latest_recorded_at')
            ->where('recorded_at', '<=', $at)
            ->groupBy('vessel_id');

        $query->joinSub($latestRecords, 'latest_records', fn (JoinClause $join) => $join
            ->on('positions.vessel_id', '=', 'latest_records.vessel_id')
            ->on('positions.recorded_at', '=', 'latest_records.latest_recorded_at'))
            ->select('positions.*');
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function trackingDevice(): BelongsTo
    {
        return $this->belongsTo(TrackingDevice::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
}
