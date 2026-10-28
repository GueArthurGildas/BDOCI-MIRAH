<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vessel_id', 'occurred_at', 'type', 'module_id', 'object_type', 'object_id', 'summary_key', 'summary_params', 'zone_id', 'user_id', 'source'])]
class VesselEvent extends Model
{
    protected function casts(): array
    {
        return ['occurred_at' => 'datetime', 'summary_params' => 'array'];
    }

    public function summary(): string
    {
        return __($this->summary_key, $this->summary_params ?? []);
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(RegisteredModule::class, 'module_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
