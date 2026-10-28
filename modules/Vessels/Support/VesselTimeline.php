<?php

namespace Modules\Vessels\Support;

use App\Models\Vessel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class VesselTimeline
{
    private const LIMIT = 200;

    private const ICONS = [
        'vessels' => ['icon-anchor', 'primary'],
        'licenses' => ['icon-file-text', 'primary'],
        'tracking' => ['icon-radio', 'warning'],
        'vms' => ['icon-radio', 'warning'],
        'logbook' => ['icon-book', 'info'],
        'watch' => ['icon-eye', 'info'],
        'operations' => ['icon-clipboard', 'info'],
        'movements' => ['icon-navigation', 'info'],
        'inspections' => ['icon-check-square', 'success'],
        'infractions' => ['icon-alert-triangle', 'danger'],
        'missions' => ['icon-flag', 'success'],
        'communications' => ['icon-mail', 'default'],
        'reports' => ['icon-bar-chart-2', 'default'],
    ];

    /** @return Collection<int, array{date: Carbon, module: string, icon: string, color: string, title: string}> */
    public static function for(Vessel $vessel, ?string $module = null): Collection
    {
        return DB::table('v_vessel_timeline')
            ->where('vessel_id', $vessel->id)
            ->when($module, fn ($query, $module) => $query->where('module', $module))
            ->orderByDesc('occurred_at')
            ->limit(self::LIMIT)
            ->get()
            ->map(function (object $event) {
                [$icon, $color] = self::ICONS[$event->module] ?? ['icon-activity', 'default'];

                return [
                    'date' => Carbon::parse($event->occurred_at),
                    'module' => $event->module,
                    'icon' => $icon,
                    'color' => $color,
                    'title' => __($event->summary_key, json_decode($event->summary_params ?? '[]', true) ?: []),
                ];
            });
    }

    /** @return Collection<int, string> */
    public static function modulesOf(Vessel $vessel): Collection
    {
        return DB::table('v_vessel_timeline')->where('vessel_id', $vessel->id)->distinct()->orderBy('module')->pluck('module');
    }
}
