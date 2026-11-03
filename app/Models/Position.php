<?php

namespace App\Models;

use App\Enums\TrackingSystem;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable([
    'vessel_id', 'tracking_device_id', 'zone_id', 'system',
    'recorded_at', 'received_at', 'latitude', 'longitude', 'speed_knots', 'course',
    'activity', 'navigation_status', 'accuracy_m', 'retention_reason',
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
            'received_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
            'speed_knots' => 'float',
        ];
    }

    public function scopeLatestPerVessel(Builder $query, Carbon $at): void
    {
        $latestPositions = Vessel::query()
            ->select('latest_position.*')
            ->joinLateral(
                self::query()
                    ->whereColumn('positions.vessel_id', 'vessels.id')
                    ->where('positions.recorded_at', '<=', $at)
                    ->orderByDesc('positions.recorded_at')
                    ->limit(1),
                'latest_position',
            );

        $query->fromSub($latestPositions, 'positions');
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
