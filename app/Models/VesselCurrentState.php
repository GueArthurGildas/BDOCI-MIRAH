<?php

namespace App\Models;

use App\Enums\CurrentLicenseStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'vessel_id', 'license_status', 'license_expires_on', 'vms_failure_ongoing', 'last_position_at', 'vms_silence_hours',
    'is_enhanced_monitoring', 'open_infractions_count', 'checks_count', 'status_light',
])]
class VesselCurrentState extends Model
{
    protected $primaryKey = 'vessel_id';

    public $incrementing = false;

    const CREATED_AT = null;

    protected function casts(): array
    {
        return [
            'license_status' => CurrentLicenseStatus::class,
            'license_expires_on' => 'date',
            'vms_failure_ongoing' => 'boolean',
            'last_position_at' => 'datetime',
            'vms_silence_hours' => 'float',
            'is_enhanced_monitoring' => 'boolean',
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }
}
