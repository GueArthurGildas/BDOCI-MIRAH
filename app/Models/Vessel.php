<?php

namespace App\Models;

use App\Enums\VesselCategory;
use App\Enums\VesselStatus;
use App\Enums\VesselType;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Locale;

#[Fillable([
    'name', 'registration_number', 'imo_number', 'call_sign', 'flag', 'type',
    'category', 'owner', 'length_meters', 'gross_tonnage', 'status',
])]
class Vessel extends Model
{
    use Audited, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'type' => VesselType::class,
            'category' => VesselCategory::class,
            'status' => VesselStatus::class,
            'length_meters' => 'float',
            'gross_tonnage' => 'float',
        ];
    }

    public function flagName(): string
    {
        return Locale::getDisplayRegion("-{$this->flag}", app()->getLocale());
    }

    public function scopeSearch(Builder $query, ?string $term): void
    {
        $query->when($term, fn (Builder $query) => $query->where(fn (Builder $query) => $query
            ->where('name', 'like', "%{$term}%")
            ->orWhere('registration_number', 'like', "%{$term}%")
            ->orWhere('imo_number', 'like', "%{$term}%")
            ->orWhere('call_sign', 'like', "%{$term}%")));
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class)->latest('valid_until');
    }

    public function currentLicense(): HasOne
    {
        return $this->hasOne(License::class)->ofMany('valid_until', 'max');
    }

    public function trackingDevices(): HasMany
    {
        return $this->hasMany(TrackingDevice::class)->latest('installed_at');
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function latestPosition(): HasOne
    {
        return $this->hasOne(Position::class)->latestOfMany('recorded_at');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class)->latest('occurred_at');
    }

    public function observations(): HasMany
    {
        return $this->hasMany(Observation::class)->latest('observed_at');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class)->latest('inspected_at');
    }

    public function infractions(): HasMany
    {
        return $this->hasMany(Infraction::class)->latest('detected_at');
    }

    public function catchDeclarations(): HasMany
    {
        return $this->hasMany(CatchDeclaration::class)->latest('period_start');
    }

    public function missions(): BelongsToMany
    {
        return $this->belongsToMany(Mission::class)->latest('starts_at');
    }
}
