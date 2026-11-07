<?php

namespace Modules\Tracking\Support;

use App\Models\Vessel;
use App\Support\Vms\ThemisLink;
use Illuminate\Http\Request;

final class ThermisPanel
{
    public const MAX_VESSELS = 50;

    /** @return array<string, mixed> */
    public static function for(Request $request): array
    {
        $filters = $request->validate([
            'vessels' => ['nullable', 'array', 'max:'.self::MAX_VESSELS],
            'vessels.*' => ['integer', 'exists:vessels,id'],
        ]);

        $links = app(ThemisLink::class);
        $selected = Vessel::whereIn('id', $filters['vessels'] ?? [])->orderBy('name')->get();
        $targets = $selected->map(fn (Vessel $vessel) => ['vessel' => $vessel, 'url' => $links->url($vessel)]);
        $baseUrl = config('surveillance.vms.themis_url');

        return [
            'baseUrl' => $baseUrl,
            'configured' => $baseUrl || config('surveillance.vms.themis_vessel_url'),
            'options' => Vessel::orderBy('name')->get(['id', 'name', 'registration_number'])->mapWithKeys(fn (Vessel $vessel) => [$vessel->id => $vessel->name.' ('.$vessel->registration_number.')']),
            'selectedIds' => $selected->pluck('id')->all(),
            'targets' => $targets,
            'embedUrl' => $targets->pluck('url')->filter()->first() ?? ($selected->isEmpty() ? $baseUrl : null),
        ];
    }
}
