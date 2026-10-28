<?php

namespace Modules\Tracking\Http\Controllers;

use App\Enums\BeaconStatus;
use App\Enums\VesselCategory;
use App\Enums\ZoneType;
use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\TrackingDevice;
use App\Models\Zone;
use App\Queries\ZoneSituation;
use App\Support\Presentation\VesselLink;
use App\Support\Settings\SettingKey;
use App\Support\Settings\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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

    private const BEACON_STATUS_PRIORITY = ['silent', 'no_data', 'emitting'];

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
        ]);
    }

    public function zones(Request $request): View
    {
        $filters = $request->validate(['at' => ['nullable', 'date']]);
        $at = Carbon::parse($filters['at'] ?? now());

        $vesselCountByZone = ZoneSituation::vesselCountByZoneAt($at);

        $zones = Zone::where('is_active', true)->orderBy('type')->orderBy('name')->get();
        $portAreas = $zones
            ->where('type', ZoneType::PortArea)
            ->sortByDesc(fn (Zone $zone) => $vesselCountByZone->get($zone->id, 0));

        return view('tracking::zones', [
            'at' => $at,
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
        ]);

        $devices = TrackingDevice::whereNull('removed_at')
            ->with('vessel.latestPosition.zone')
            ->get()
            ->sortBy(fn (TrackingDevice $device) => [
                array_search($device->vessel->beaconStatus()->value, self::BEACON_STATUS_PRIORITY),
                $device->vessel->latestPosition?->recorded_at,
            ]);

        return view('tracking::beacons', [
            'filters' => $filters,
            'countsByStatus' => $devices->countBy(fn (TrackingDevice $device) => $device->vessel->beaconStatus()->value),
            'devices' => $devices->when(
                $filters['status'] ?? null,
                fn (Collection $devices, string $status) => $devices->filter(fn (TrackingDevice $device) => $device->vessel->beaconStatus()->value === $status),
            ),
            'thresholdHours' => app(Settings::class)->int(SettingKey::BeaconSilenceThresholdHours),
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
