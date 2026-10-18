<?php

namespace Modules\Vessels\Support;

use App\Models\Vessel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class VesselTimeline
{
    /** @return Collection<int, array{date: Carbon, icon: string, color: string, title: string, description: ?string}> */
    public static function for(Vessel $vessel): Collection
    {
        return collect()
            ->concat($vessel->licenses->map(fn ($license) => self::entry(
                $license->valid_from, 'icon-file-text', 'primary',
                __('vessels::vessels.timeline.license', ['number' => $license->number]),
            )))
            ->concat($vessel->movements->map(fn ($movement) => self::entry(
                $movement->occurred_at, 'icon-navigation', 'info',
                __('vessels::vessels.timeline.movement', ['direction' => __($movement->direction->label()), 'port' => $movement->port->name]),
                $movement->reason,
            )))
            ->concat($vessel->observations->map(fn ($observation) => self::entry(
                $observation->observed_at, 'icon-eye', $observation->level->color(),
                __('vessels::vessels.timeline.observation', ['level' => __($observation->level->label())]),
                $observation->description,
            )))
            ->concat($vessel->inspections->map(fn ($inspection) => self::entry(
                $inspection->inspected_at, 'icon-check-square', $inspection->result->color(),
                __('vessels::vessels.timeline.inspection', ['result' => __($inspection->result->label())]),
                $inspection->findings,
            )))
            ->concat($vessel->infractions->map(fn ($infraction) => self::entry(
                $infraction->detected_at, 'icon-alert-triangle', 'danger',
                __('vessels::vessels.timeline.infraction', ['reference' => $infraction->reference]),
                $infraction->type->name,
            )))
            ->concat($vessel->missions->map(fn ($mission) => self::entry(
                $mission->starts_at, 'icon-flag', 'success',
                __('vessels::vessels.timeline.mission', ['reference' => $mission->reference]),
                $mission->objective,
            )))
            ->sortByDesc('date')
            ->values();
    }

    private static function entry(Carbon $date, string $icon, string $color, string $title, ?string $description = null): array
    {
        return compact('date', 'icon', 'color', 'title', 'description');
    }
}
