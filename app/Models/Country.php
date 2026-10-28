<?php

namespace App\Models;

use App\Support\Localization\Countries;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['iso3_code', 'iso2_code', 'label'])]
class Country extends Model
{
    use HasFactory;

    public static function forIso2(?string $iso2): ?self
    {
        if (blank($iso2)) {
            return null;
        }

        $iso2 = strtoupper($iso2);

        return self::firstOrCreate(
            ['iso2_code' => $iso2],
            ['iso3_code' => Countries::iso3($iso2), 'label' => Countries::name($iso2)],
        );
    }

    public function vessels(): HasMany
    {
        return $this->hasMany(Vessel::class, 'flag_country_id');
    }
}
