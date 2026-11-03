<?php

namespace Modules\Indicators\Http\Controllers;

use App\Enums\FleetSegment;
use App\Enums\VesselStatusLight;
use App\Http\Controllers\Controller;
use App\Models\Vessel;
use App\Models\VesselCurrentState;
use App\Models\Zone;
use App\Queries\SilentBeacons;
use App\Support\Indicators\IndicatorCatalog;
use App\Support\Indicators\IndicatorFilters;
use App\Support\Vessels\VesselIndicators;
use App\Support\Vessels\VigilanceScore;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class FleetController extends Controller
{
    private const RANKING_SIZE = 10;

    private const RANKING_CACHE_KEY = 'indicators.fleet.ranking';

    private const RANKING_CACHE_SECONDS = 60;

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
        $top = Cache::remember(self::RANKING_CACHE_KEY, self::RANKING_CACHE_SECONDS, fn () => $this->computeRanking());
        $vessels = Vessel::query()->whereIn('id', array_column($top, 'id'))->with(['currentState', 'currentLicense', 'latestPosition'])->get()->keyBy('id');

        return collect($top)
            ->filter(fn (array $row) => $vessels->has($row['id']))
            ->map(fn (array $row) => ['vessel' => $vessels[$row['id']], 'score' => $row['score']])
            ->values();
    }

    /** @return array<int, array{id: int, score: array<string, mixed>}> */
    private function computeRanking(): array
    {
        $silentVesselIds = SilentBeacons::vessels()->pluck('id');

        return Vessel::query()
            ->whereHas('currentState', fn ($states) => $states->where('status_light', '!=', VesselStatusLight::Compliant->value))
            ->with(['currentState', 'currentLicense', 'latestPosition'])
            ->get()
            ->map(fn (Vessel $vessel) => ['id' => $vessel->id, 'score' => VigilanceScore::compute(VesselIndicators::for($vessel, $silentVesselIds))])
            ->sortByDesc(fn (array $row) => $row['score']['total'])
            ->take(self::RANKING_SIZE)
            ->values()
            ->all();
    }
}
