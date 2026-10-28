<?php

namespace App\Models;

use App\Enums\BeaconModem;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['serial_number', 'model', 'manufacturer', 'supplier', 'modem', 'subscription'])]
class Beacon extends Model
{
    use Audited, HasFactory;

    protected function casts(): array
    {
        return [
            'modem' => BeaconModem::class,
        ];
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(BeaconAssignment::class);
    }

    public function currentAssignment(): HasOne
    {
        return $this->hasOne(BeaconAssignment::class)->whereNull('ended_on');
    }
}
