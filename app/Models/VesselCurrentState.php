<?php

namespace App\Models;

use App\Enums\TrafficLight;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'vessel_id', 'license_status', 'license_expires_on', 'beacon_fault_open', 'last_position_at',
    'enhanced_monitoring', 'open_infractions', 'verification_count', 'traffic_light',
])]
class VesselCurrentState extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'vessel_id';

    public const CREATED_AT = null;

    protected function casts(): array
    {
        return [
            'license_expires_on' => 'date',
            'beacon_fault_open' => 'boolean',
            'last_position_at' => 'datetime',
            'enhanced_monitoring' => 'boolean',
            'open_infractions' => 'integer',
            'verification_count' => 'integer',
            'traffic_light' => TrafficLight::class,
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }
}
