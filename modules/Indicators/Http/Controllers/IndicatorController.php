<?php

namespace Modules\Indicators\Http\Controllers;

use App\Enums\FleetSegment;
use App\Enums\VesselStatusLight;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Modules\Indicators\Support\IndicatorCatalog;
use Modules\Indicators\Support\IndicatorFilters;

class IndicatorController extends Controller
{
    private const PER_PAGE = 25;

    public function __invoke(Request $request, string $indicator, IndicatorCatalog $catalog): View
    {
        abort_unless(IndicatorCatalog::exists($indicator), 404);

        $filters = IndicatorFilters::fromRequest($request);
        $rows = $catalog->rows($indicator, $filters);
        $page = LengthAwarePaginator::resolveCurrentPage();

        return view('indicators::show', [
            'indicator' => $indicator,
            'style' => IndicatorCatalog::KEYS[$indicator],
            'filters' => $filters,
            'rows' => new LengthAwarePaginator(
                $rows->forPage($page, self::PER_PAGE)->values(),
                $rows->count(),
                self::PER_PAGE,
                $page,
                ['path' => $request->url(), 'query' => $filters->toQuery()],
            ),
            'zones' => Zone::orderBy('name')->pluck('name', 'id'),
            'segments' => FleetSegment::options(),
            'statuses' => VesselStatusLight::options(),
            'flags' => Country::whereNotNull('iso2_code')->orderBy('iso2_code')->pluck('iso2_code', 'iso2_code'),
        ]);
    }
}
