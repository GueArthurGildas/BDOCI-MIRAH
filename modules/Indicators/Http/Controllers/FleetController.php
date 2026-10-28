<?php

namespace Modules\Indicators\Http\Controllers;

use App\Enums\FleetSegment;
use App\Enums\VesselStatusLight;
use App\Http\Controllers\Controller;
use App\Models\Vessel;
use App\Models\VesselCurrentState;
use App\Models\Zone;
use App\Support\Vessels\VesselIndicators;
use App\Support\Vessels\VigilanceScore;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Modules\Indicators\Support\IndicatorCatalog;
use Modules\Indicators\Support\IndicatorFilters;

class FleetController extends Controller
{
    private const RANKING_SIZE = 10;

    public function __invoke(Request $request, IndicatorCatalog $catalog): View
    {
        $filters = IndicatorFilters::fromRequest($request);

        return view('indicators::fleet', [
            'filters' => $filters,
            'query' => $filters->toQuery(),
            'tiles' => collect(IndicatorCatalog::KEYS)->except('status_compliant')->map(fn (array $style, string $key) => [
                ...$style,
                'key' => $key,
                'count' => $catalog->count($key, $filters),
            ])->values(),
            'distribution' => collect(VesselStatusLight::cases())->mapWithKeys(fn (VesselStatusLight $light) => [
                $light->value => VesselCurrentState::where('status_light', $light->value)->count(),
            ]),
            'ranking' => $this->ranking(),
            'zones' => Zone::orderBy('name')->pluck('name', 'id'),
            'segments' => FleetSegment::options(),
        ]);
    }

    private function ranking(): Collection
    {
        return Vessel::query()
            ->whereHas('currentState', fn ($states) => $states->where('status_light', '!=', VesselStatusLight::Compliant->value))
            ->with(['currentState', 'currentLicense', 'latestPosition'])
            ->get()
            ->map(fn (Vessel $vessel) => ['vessel' => $vessel, 'score' => VigilanceScore::compute(VesselIndicators::for($vessel))])
            ->sortByDesc(fn (array $row) => $row['score']['total'])
            ->take(self::RANKING_SIZE)
            ->values();
    }
}
