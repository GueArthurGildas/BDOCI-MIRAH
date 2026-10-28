<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vessel_id', 'requires_vms', 'requires_ais', 'requires_ers', 'requires_eez_declaration', 'requires_fishing_log'])]
class VesselReportingObligation extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'requires_vms' => 'boolean',
            'requires_ais' => 'boolean',
            'requires_ers' => 'boolean',
            'requires_eez_declaration' => 'boolean',
            'requires_fishing_log' => 'boolean',
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }
}
