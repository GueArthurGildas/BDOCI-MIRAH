<?php

namespace Modules\Indicators\Support;

use App\Enums\LicenseStatus;
use App\Enums\OperationStatus;
use App\Enums\VesselStatus;
use App\Enums\VesselStatusLight;
use App\Enums\ZoneType;
use App\Models\Alert;
use App\Models\License;
use App\Models\Operation;
use App\Models\Vessel;
use App\Models\VesselCurrentState;
use App\Queries\OpenInfractionCases;
use App\Queries\SilentBeacons;
use App\Queries\VesselMissions;
use App\Support\Vessels\StatusLight;
use App\Support\Vessels\StatusLightFacts;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class IndicatorCatalog
{
    public const KEYS = [
        'vessels_in_eez' => ['icon' => 'icon-anchor', 'color' => 'success'],
        'silent_beacons' => ['icon' => 'icon-radio', 'color' => 'danger'],
        'expiring_licenses' => ['icon' => 'icon-file-text', 'color' => 'warning'],
        'open_infractions' => ['icon' => 'icon-alert-triangle', 'color' => 'danger'],
        'open_operations' => ['icon' => 'icon-clipboard', 'color' => 'info'],
        'ongoing_missions' => ['icon' => 'icon-flag', 'color' => 'primary'],
        'status_compliant' => ['icon' => 'icon-check-circle', 'color' => 'success'],
        'status_to_watch' => ['icon' => 'icon-eye', 'color' => 'warning'],
        'status_suspect' => ['icon' => 'icon-help-circle', 'color' => 'inverse'],
        'status_in_violation' => ['icon' => 'icon-x-octagon', 'color' => 'danger'],
    ];

    public static function exists(string $key): bool
    {
        return array_key_exists($key, self::KEYS);
    }

    public function count(string $key, IndicatorFilters $filters): int
    {
        return $this->rows($key, $filters)->count();
    }

    /** @return Collection<int, IndicatorRow> */
    public function rows(string $key, IndicatorFilters $filters): Collection
    {
        $allowed = $this->allowedVessels($filters);

        return $this->raw($key)
            ->filter(fn (IndicatorRow $row) => $allowed->has($row->vessel->id))
            ->filter(fn (IndicatorRow $row) => ! $filters->from || ($row->date && $row->date->gte($filters->from)))
            ->filter(fn (IndicatorRow $row) => ! $filters->to || ($row->date && $row->date->lte($filters->to)))
            ->filter(fn (IndicatorRow $row) => ! $filters->zoneId || $allowed[$row->vessel->id]->latestPosition?->zone_id === $filters->zoneId)
            ->sortBy(fn (IndicatorRow $row) => $row->date?->timestamp ?? 0)
            ->values();
    }

    /** @return Collection<int, Vessel> */
    private function allowedVessels(IndicatorFilters $filters): Collection
    {
        return Vessel::query()
            ->with(['latestPosition', 'flagCountry', 'currentState'])
            ->search($filters->search)
            ->when($filters->flag, fn (Builder $query, string $flag) => $query->whereHas('flagCountry', fn (Builder $countries) => $countries->where('iso2_code', $flag)))
            ->when($filters->segment, fn (Builder $query, string $segment) => $query->where('fleet_segment', $segment))
            ->when($filters->status, fn (Builder $query, string $status) => $query->whereHas('currentState', fn (Builder $states) => $states->where('status_light', $status)))
            ->get()
            ->keyBy('id');
    }

    /** @return Collection<int, IndicatorRow> */
    private function raw(string $key): Collection
    {
        return match ($key) {
            'vessels_in_eez' => $this->vesselsInEez(),
            'silent_beacons' => $this->silentBeacons(),
            'expiring_licenses' => $this->expiringLicenses(),
            'open_infractions' => $this->openInfractions(),
            'open_operations' => $this->openOperations(),
            'ongoing_missions' => $this->ongoingMissions(),
            'status_compliant' => $this->byStatusLight(VesselStatusLight::Compliant),
            'status_to_watch' => $this->byStatusLight(VesselStatusLight::ToWatch),
            'status_suspect' => $this->byStatusLight(VesselStatusLight::Suspect),
            'status_in_violation' => $this->byStatusLight(VesselStatusLight::InViolation),
        };
    }

    private function vesselsInEez(): Collection
    {
        return Vessel::query()
            ->where('status', VesselStatus::Active->value)
            ->with('latestPosition.zone')
            ->get()
            ->filter(fn (Vessel $vessel) => $vessel->latestPosition?->zone?->type === ZoneType::ExclusiveEconomicZone)
            ->map(fn (Vessel $vessel) => new IndicatorRow($vessel, $vessel->latestPosition->recorded_at, $vessel->latestPosition->zone->name, null, 'tracking'))
            ->values();
    }

    private function silentBeacons(): Collection
    {
        return SilentBeacons::vessels()->map(function (Vessel $vessel) {
            $alert = Alert::where('vessel_id', $vessel->id)->where('type', 'silence')->latest('triggered_at')->first();

            return new IndicatorRow(
                $vessel,
                $vessel->latestPosition?->recorded_at,
                $vessel->latestPosition ? __('indicators::indicators.detail.silent_for', ['hours' => round($vessel->latestPosition->recorded_at->diffInMinutes(now(), absolute: true) / 60, 1)]) : null,
                $alert ? __($alert->status->label()) : null,
                'tracking',
            );
        });
    }

    private function expiringLicenses(): Collection
    {
        return License::withStatus(LicenseStatus::Expiring)->with('vessel')->get()
            ->filter(fn (License $license) => $license->vessel !== null)
            ->map(fn (License $license) => new IndicatorRow(
                $license->vessel,
                $license->expires_on->startOfDay(),
                __('indicators::indicators.detail.license', ['number' => $license->number, 'days' => $license->daysUntilExpiry()]),
                null,
                'licenses',
            ));
    }

    private function openInfractions(): Collection
    {
        return OpenInfractionCases::all()
            ->filter(fn ($infraction) => $infraction->vessel !== null)
            ->map(fn ($infraction) => new IndicatorRow(
                $infraction->vessel,
                $infraction->detected_at,
                $infraction->reference.' · '.$infraction->type?->name,
                __($infraction->status->label()),
                'infractions',
            ));
    }

    private function openOperations(): Collection
    {
        return Operation::query()
            ->whereIn('status', [OperationStatus::Open->value, OperationStatus::InProgress->value])
            ->with('vessels')
            ->get()
            ->flatMap(fn (Operation $operation) => $operation->vessels->map(fn (Vessel $vessel) => new IndicatorRow(
                $vessel, $operation->started_at, $operation->code, __($operation->status->label()), 'operations',
            )));
    }

    private function ongoingMissions(): Collection
    {
        return VesselMissions::ongoing()->with('observedUnits.vessel')->get()
            ->flatMap(fn ($mission) => $mission->observedUnits->pluck('vessel')->filter()->unique('id')->map(fn (Vessel $vessel) => new IndicatorRow(
                $vessel, $mission->startsAt(), $mission->code.' · '.$mission->objectives, __($mission->status->label()), 'missions',
            )));
    }

    private function byStatusLight(VesselStatusLight $light): Collection
    {
        return VesselCurrentState::query()
            ->where('status_light', $light->value)
            ->with('vessel')
            ->get()
            ->filter(fn (VesselCurrentState $state) => $state->vessel !== null)
            ->map(fn (VesselCurrentState $state) => new IndicatorRow(
                $state->vessel,
                $state->updated_at,
                collect(StatusLight::assess(StatusLightFacts::gather($state->vessel))->reasons)
                    ->map(fn (array $reason) => __('vigilance.reasons.'.$reason['key'], $reason['params']))
                    ->implode(' ; '),
                null,
                'vessels',
            ));
    }
}
