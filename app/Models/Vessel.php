<?php

namespace App\Models;

use App\Enums\ActorRole;
use App\Enums\BeaconStatus;
use App\Enums\FleetSegment;
use App\Enums\FleetType;
use App\Enums\IdentifierType;
use App\Enums\LegalStatus;
use App\Enums\RecordStatus;
use App\Enums\SourceSystem;
use App\Enums\VesselCategory;
use App\Enums\VesselStatus;
use App\Enums\VesselType;
use App\Models\Concerns\Audited;
use App\Support\Licenses\CurrentLicense;
use App\Support\Localization\Countries;
use App\Support\Vessels\VesselRelationsSynchronizer;
use App\Support\Vms\SilenceThreshold;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name', 'registration_number', 'imo_number', 'call_sign', 'flag', 'type',
    'category', 'owner', 'length_meters', 'gross_tonnage', 'status',
    'record_status', 'creation_origin', 'fleet_segment', 'vessel_type_id', 'fleet_type', 'is_tuna_vessel',
    'legal_status', 'flag_country_id', 'home_port_id', 'construction_year', 'length_m', 'beam_m',
    'depth_over_keel_m', 'net_tonnage', 'propulsion', 'hold_capacity_m3', 'registration_certificate',
    'fao_identifier', 'license_not_provided',
])]
class Vessel extends Model
{
    use Audited, HasFactory, SoftDeletes;

    public ?string $pendingOwner = null;

    protected static function booted(): void
    {
        static::updating(fn (Vessel $vessel) => VesselRelationsSynchronizer::recordHistory($vessel));
        static::saved(fn (Vessel $vessel) => VesselRelationsSynchronizer::sync($vessel));
    }

    protected function casts(): array
    {
        return [
            'type' => VesselType::class,
            'category' => VesselCategory::class,
            'status' => VesselStatus::class,
            'record_status' => RecordStatus::class,
            'creation_origin' => SourceSystem::class,
            'fleet_segment' => FleetSegment::class,
            'fleet_type' => FleetType::class,
            'legal_status' => LegalStatus::class,
            'is_tuna_vessel' => 'boolean',
            'license_not_provided' => 'boolean',
            'length_m' => 'float',
            'beam_m' => 'float',
            'depth_over_keel_m' => 'float',
            'net_tonnage' => 'float',
            'gross_tonnage' => 'float',
            'hold_capacity_m3' => 'float',
        ];
    }

    protected function flag(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->flagCountry?->iso2_code,
            set: function (?string $code) {
                $this->unsetRelation('flagCountry');

                return ['flag_country_id' => Country::forIso2($code)?->id];
            },
        );
    }

    protected function lengthMeters(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->length_m,
            set: fn ($value) => ['length_m' => $value],
        );
    }

    protected function owner(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->pendingOwner ?? $this->currentOwner?->holderName(),
            set: function (?string $name) {
                $this->pendingOwner = $name;

                return [];
            },
        );
    }

    public function flagName(): string
    {
        return $this->flag ? Countries::name($this->flag) : '';
    }

    public function beaconStatus(): BeaconStatus
    {
        $lastEmission = $this->latestPosition?->recorded_at;

        return match (true) {
            $lastEmission === null => BeaconStatus::NoData,
            $lastEmission->lt(now()->subMinutes((int) round(app(SilenceThreshold::class)->hours() * 60))) => BeaconStatus::Silent,
            default => BeaconStatus::Emitting,
        };
    }

    public function scopeSearch(Builder $query, ?string $term): void
    {
        $query->when($term, fn (Builder $query) => $query->where(fn (Builder $query) => $query
            ->whereLike('name', "%{$term}%")
            ->orWhereLike('registration_number', "%{$term}%")
            ->orWhereLike('imo_number', "%{$term}%")
            ->orWhereLike('call_sign', "%{$term}%")
            ->orWhereHas('identifiers', fn (Builder $identifiers) => $identifiers->whereLike('value', "%{$term}%"))));
    }

    public function flagCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'flag_country_id');
    }

    public function homePort(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'home_port_id');
    }

    public function vesselType(): BelongsTo
    {
        return $this->belongsTo(VesselTypeRecord::class, 'vessel_type_id');
    }

    public function identifiers(): HasMany
    {
        return $this->hasMany(VesselIdentifier::class)->orderBy('type')->orderByDesc('valid_from');
    }

    public function activeIdentifiers(): HasMany
    {
        return $this->hasMany(VesselIdentifier::class)->whereNull('valid_to');
    }

    public function history(): HasMany
    {
        return $this->hasMany(VesselHistory::class)->latest('changed_on')->latest('id');
    }

    public function sources(): HasMany
    {
        return $this->hasMany(VesselSource::class);
    }

    public function fieldProvenance(): HasMany
    {
        return $this->hasMany(VesselFieldProvenance::class, 'vessel_id');
    }

    public function actors(): HasMany
    {
        return $this->hasMany(VesselActor::class)->orderByDesc('started_on')->orderByDesc('id');
    }

    public function currentOwner(): HasOne
    {
        return $this->hasOne(VesselActor::class)->where('role', ActorRole::Owner->value)->whereNull('ended_on')->latestOfMany();
    }

    public function externalLists(): BelongsToMany
    {
        return $this->belongsToMany(ExternalList::class)->withPivot(['listed_from', 'listed_until']);
    }

    public function identifierValue(IdentifierType $type): ?string
    {
        return $this->activeIdentifiers->firstWhere('type', $type)?->value;
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class)->latest('expires_on');
    }

    public function currentLicense(): HasOne
    {
        return $this->hasOne(License::class)->ofMany(CurrentLicense::ORDER_COLUMN, CurrentLicense::ORDER_AGGREGATE);
    }

    public function fnpStatus(): HasOne
    {
        return $this->hasOne(FnpStatus::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(VesselEvent::class)->latest('occurred_at');
    }

    public function currentState(): HasOne
    {
        return $this->hasOne(VesselCurrentState::class);
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

    public function eezDeclarations(): HasMany
    {
        return $this->hasMany(EezDeclaration::class)->latest('received_at');
    }

    public function observations(): HasMany
    {
        return $this->hasMany(Observation::class)->latest('observed_at');
    }

    public function externalInspections(): HasMany
    {
        return $this->hasMany(ExternalInspection::class)->latest('started_at');
    }

    public function infractionCases(): HasMany
    {
        return $this->hasMany(InfractionCase::class)->latest('opened_at');
    }

    public function catchDeclarations(): HasMany
    {
        return $this->hasMany(CatchDeclaration::class)->latest('period_start');
    }

    public function observedUnits(): HasMany
    {
        return $this->hasMany(ObservedUnit::class)->latest('observed_at');
    }

    public function missions(): BelongsToMany
    {
        return $this->belongsToMany(Mission::class, 'observed_units')->distinct()->latest('missions.planned_start_at');
    }

    public function watchEntries(): HasMany
    {
        return $this->hasMany(WatchEntry::class)->latest('started_at');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class)->latest('triggered_at');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(SentMessage::class)->latest('sent_at');
    }

    public function internalNotes(): HasMany
    {
        return $this->hasMany(InternalNote::class)->latest('id');
    }

    public function licenseDocuments(): HasMany
    {
        return $this->hasMany(LicenseDocument::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'object')->latest('created_at');
    }
}
