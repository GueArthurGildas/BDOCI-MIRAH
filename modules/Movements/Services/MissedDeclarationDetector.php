<?php

namespace Modules\Movements\Services;

use App\Enums\EezDeclarationType;
use App\Enums\ZoneType;
use App\Models\MissedDeclaration;
use App\Models\Position;
use App\Models\Vessel;
use Illuminate\Support\Collection;

class MissedDeclarationDetector
{
    private const ZONE_TYPES_INSIDE_EEZ = [ZoneType::ExclusiveEconomicZone, ZoneType::TerritorialSea, ZoneType::Restricted];

    /** @return Collection<int, MissedDeclaration> */
    public function detect(): Collection
    {
        return $this->undeclaredForeignVessels()->map(fn (Vessel $vessel) => MissedDeclaration::create([
            'vessel_id' => $vessel->id,
            'detected_at' => now(),
        ]));
    }

    /** @return Collection<int, Vessel> */
    public function undeclaredForeignVessels(): Collection
    {
        $since = now()->subHours(config('surveillance.eez_position_window_hours'));

        return Vessel::query()
            ->where('flag', '!=', 'CI')
            ->whereIn('id', Position::query()
                ->latestPerVessel(now())
                ->where('positions.recorded_at', '>=', $since)
                ->whereHas('zone', fn ($query) => $query->whereIn('type', array_map(fn (ZoneType $type) => $type->value, self::ZONE_TYPES_INSIDE_EEZ)))
                ->select('positions.vessel_id'))
            ->get()
            ->reject(fn (Vessel $vessel) => $this->isCurrentlyDeclared($vessel) || $this->hasUnresolvedMiss($vessel))
            ->values();
    }

    private function isCurrentlyDeclared(Vessel $vessel): bool
    {
        $latest = $vessel->eezDeclarations()->latest('received_at')->latest('id')->first();

        return $latest?->type === EezDeclarationType::Entry;
    }

    private function hasUnresolvedMiss(Vessel $vessel): bool
    {
        return MissedDeclaration::unresolved()->where('vessel_id', $vessel->id)->exists();
    }
}
