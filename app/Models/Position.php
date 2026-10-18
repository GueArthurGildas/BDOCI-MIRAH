<?php

namespace App\Models;

use App\Enums\TrackingSystem;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
