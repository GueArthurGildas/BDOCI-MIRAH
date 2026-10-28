<?php

namespace App\Models;

use App\Enums\BeaconRequestStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['vessel_id', 'requested_on', 'status'])]
class NewBeaconRequest extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'requested_on' => 'date',
            'status' => BeaconRequestStatus::class,
        ];
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function installations(): HasMany
    {
        return $this->hasMany(BeaconInstallation::class, 'new_beacon_request_id');
    }
}
