<?php

namespace App\Models;

use App\Enums\PositionLoadingSource;
use App\Enums\SlotResult;
use App\Enums\SlotTestResult;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['control_slot_id', 'vessel_id', 'latitude', 'longitude', 'speed', 'zone_id', 'locality_id', 'last_emission_at', 'emission_test', 'speed_activity_test', 'trajectory_test', 'result', 'confirmed_by', 'loading_source'])]
class SlotPosition extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'speed' => 'float',
            'last_emission_at' => 'datetime',
            'emission_test' => SlotTestResult::class,
            'speed_activity_test' => SlotTestResult::class,
            'trajectory_test' => SlotTestResult::class,
            'result' => SlotResult::class,
            'loading_source' => PositionLoadingSource::class,
        ];
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(ControlSlot::class, 'control_slot_id');
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function locality(): BelongsTo
    {
        return $this->belongsTo(ReferenceListValue::class, 'locality_id');
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
