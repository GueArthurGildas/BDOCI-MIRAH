<?php

namespace Modules\Tracking\Http\Controllers;

use App\Enums\BeaconStatus;
use App\Enums\VesselCategory;
use App\Enums\ZoneType;
use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\Zone;
use App\Queries\BeaconStates;
use App\Queries\ZoneSituation;
use App\Support\Presentation\VesselLink;
use App\Support\Tables\PerPage;
use App\Support\Vms\SilencePresets;
use App\Support\Vms\SilenceThreshold;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Tracking\Support\ThermisPanel;

class TrackingController extends Controller
{
    public const MARKER_COLORS = [
        'emitting' => '#0ac282',
        'silent' => '#fe5d70',
        'patrol' => '#4099ff',
    ];

    private const ZONE_COLORS = [
        'port_area' => '#fe9365',
        'territorial_sea' => '#01a9ac',
        'exclusive_economic_zone' => '#404e67',
        'restricted' => '#fe5d70',
    ];

    private const BEACONS_PER_PAGE = 10;

    private const ZONE_PERIODS = ['24h' => 24, '7d' => 168, '30d' => 720];

    public function map(Request $request): View
    {
        $filters = $request->validate([
            'category' => ['nullable', Rule::enum(VesselCategory::class)],
        ]);

        $positions = Position::latestPerVessel(now())
            ->with('vessel')
            ->whereHas('vessel', fn ($query) => $query->when(
                $filters['category'] ?? null,
                fn ($query, $category) => $query->where('category', $category),
            ))
            ->get();

        return view('tracking::map', [
            'filters' => $filters,
            'positionsCount' => $positions->count(),
            'markers' => $positions->map(fn (Position $position) => [
                'latitude' => $position->latitude,
                'longitude' => $position->longitude,
                'label' => $position->vessel->name.' — '.$position->recorded_at->isoFormat('L LT'),
                'url' => VesselLink::url($position->vessel),
                'color' => self::MARKER_COLORS[$this->markerKind($position)],
            ])->all(),
            'zones' => $this->zonesForMap(),
            'panel' => ThermisPanel::for($request),
            'thermisTabActive' => $request->query('tab') === 'thermis' || $request->has('vessels'),
        ]);
    }

    public function zones(Request $request): View
    {
        $filters = $request->validate([
            'at' => ['nullable', 'date'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'last' => ['nullable', Rule::in(array_keys(self::ZONE_PERIODS))],
        ]);
        $at = Carbon::parse($filters['at'] ?? now());
        $from = isset($filters['last']) ? now()->subHours(self::ZONE_PERIODS[$filters['last']]) : (isset($filters['from']) ? Carbon::parse($filters['from']) : null);
        $to = isset($filters['last']) ? now() : (isset($filters['to']) ? Carbon::parse($filters['to']) : ($from ? now() : null));

        $vesselCountByZone = $from && $to ? ZoneSituation::vesselCountByZoneBetween($from, $to) : ZoneSituation::vesselCountByZoneAt($at);

        $zones = Zone::where('is_active', true)->orderBy('type')->orderBy('name')->get();
        $portAreas = $zones
            ->where('type', ZoneType::PortArea)
            ->sortByDesc(fn (Zone $zone) => $vesselCountByZone->get($zone->id, 0));

        return view('tracking::zones', [
            'at' => $at,
            'from' => $from,
            'to' => $to,
            'periods' => collect(self::ZONE_PERIODS)->mapWithKeys(fn (int $hours, string $key) => [$key => __('tracking::tracking.zones.periods.'.$key)])->all(),
            'filters' => $filters,
            'zones' => $zones,
            'vesselCountByZone' => $vesselCountByZone,
            'freshnessHours' => ZoneSituation::POSITION_FRESHNESS_HOURS,
            'chart' => [
                'type' => 'bar',
                'data' => [
                    'labels' => $portAreas->pluck('name')->values(),
                    'datasets' => [[
                        'data' => $portAreas->map(fn (Zone $zone) => $vesselCountByZone->get($zone->id, 0))->values(),
                        'backgroundColor' => '#404e67',
                        'borderRadius' => 4,
                    ]],
                ],
                'options' => [
                    'indexAxis' => 'y',
                    'plugins' => ['legend' => ['display' => false]],
                    'scales' => ['x' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
                ],
            ],
        ]);
    }

    public function beacons(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(BeaconStatus::class)],
            'search' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'silent_for' => ['nullable', Rule::in(array_keys(SilencePresets::HOURS))],
        ]);

        $scope = BeaconStates::query()
            ->when($filters['search'] ?? null, fn (Builder $query, string $term) => $query->where(fn (Builder $query) => $query
                ->whereLike('tracking_devices.identifier', "%{$term}%")
                ->orWhereHas('vessel', fn (Builder $vessels) => $vessels->search($term))))
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->where('last_position.recorded_at', '>=', Carbon::parse($from)->startOfDay()))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->where('last_position.recorded_at', '<=', Carbon::parse($to)->endOfDay()))
            ->when($filters['silent_for'] ?? null, fn (Builder $query, string $preset) => BeaconStates::whereSilentFor($query, SilencePresets::hours($preset)));

        $devices = BeaconStates::orderByUrgency(
            (clone $scope)
                ->when($filters['status'] ?? null, fn (Builder $query, string $status) => BeaconStates::whereState($query, BeaconStatus::from($status)))
                ->with('vessel'),
        )->paginate(PerPage::from($request, self::BEACONS_PER_PAGE))->withQueryString();

        return view('tracking::beacons', [
            'filters' => $filters,
            'countsByStatus' => BeaconStates::countByState($scope),
            'devices' => $devices,
            'zoneNames' => Zone::whereIn('id', $devices->pluck('last_zone_id')->filter())->pluck('name', 'id'),
            'thresholdHours' => app(SilenceThreshold::class)->hours(),
            'presets' => SilencePresets::options(),
        ]);
    }

    private function markerKind(Position $position): string
    {
        return match (true) {
            $position->vessel->category === VesselCategory::Patrol => 'patrol',
            $position->vessel->setRelation('latestPosition', $position)->beaconStatus() === BeaconStatus::Silent => 'silent',
            default => 'emitting',
        };
    }

    private function zonesForMap(): array
    {
        return Zone::where('is_active', true)->get()->map(fn (Zone $zone) => [
            'label' => $zone->name,
            'geometry' => $zone->geometry,
            'color' => self::ZONE_COLORS[$zone->type->value],
        ])->all();
    }
}
