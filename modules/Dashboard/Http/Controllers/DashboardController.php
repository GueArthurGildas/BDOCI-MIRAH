<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Enums\InfractionStatus;
use App\Enums\LicenseStatus;
use App\Enums\VesselStatus;
use App\Enums\ZoneType;
use App\Http\Controllers\Controller;
use App\Models\DataSource;
use App\Models\Infraction;
use App\Models\Inspection;
use App\Models\License;
use App\Models\Observation;
use App\Models\Vessel;
use App\Models\Zone;
use App\Queries\SilentBeacons;
use App\Queries\ZoneSituation;
use App\Support\Presentation\ThemeColor;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const INSPECTION_HISTORY_MONTHS = 6;

    private const LATEST_OBSERVATIONS_LIMIT = 5;

    public function __invoke(): View
    {
        $now = now();

        return view('dashboard::index', [
            'now' => $now,
            'stats' => $this->keyFigures(),
            'zonesChart' => $this->zonesChart($now),
            'infractionsChart' => $this->infractionsChart(),
            'inspectionsChart' => $this->inspectionsChart($now),
            'dataSources' => DataSource::orderBy('name')->get(),
            'pendingObservations' => Observation::pending()->with('vessel')->latest('observed_at')->limit(self::LATEST_OBSERVATIONS_LIMIT)->get(),
        ]);
    }

    private function keyFigures(): array
    {
        return [
            ['label' => 'active_vessels', 'value' => Vessel::where('status', VesselStatus::Active)->count(), 'icon' => 'icon-anchor', 'color' => 'success'],
            ['label' => 'expiring_licenses', 'value' => License::withStatus(LicenseStatus::Expiring)->count(), 'icon' => 'icon-file-text', 'color' => 'warning'],
            ['label' => 'pending_observations', 'value' => Observation::pending()->count(), 'icon' => 'icon-book', 'color' => 'info'],
            ['label' => 'silent_beacons', 'value' => SilentBeacons::vessels()->count(), 'icon' => 'icon-radio', 'color' => 'danger'],
        ];
    }

    private function zonesChart(Carbon $at): array
    {
        $counts = ZoneSituation::vesselCountByZoneAt($at);
        $portAreas = Zone::where('type', ZoneType::PortArea)->get()->sortByDesc(fn (Zone $zone) => $counts->get($zone->id, 0));

        return $this->barChart(
            $portAreas->pluck('name')->values()->all(),
            $portAreas->map(fn (Zone $zone) => $counts->get($zone->id, 0))->values()->all(),
            ThemeColor::hex('inverse'),
            horizontal: true,
        );
    }

    private function infractionsChart(): array
    {
        $counts = Infraction::toBase()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $statuses = InfractionStatus::cases();

        return [
            'type' => 'doughnut',
            'data' => [
                'labels' => array_map(fn (InfractionStatus $status) => __($status->label()), $statuses),
                'datasets' => [[
                    'data' => array_map(fn (InfractionStatus $status) => (int) $counts->get($status->value, 0), $statuses),
                    'backgroundColor' => array_map(fn (InfractionStatus $status) => ThemeColor::hex($status->color()), $statuses),
                ]],
            ],
            'options' => ['plugins' => ['legend' => ['position' => 'right']]],
        ];
    }

    private function inspectionsChart(Carbon $now): array
    {
        $months = collect(range(self::INSPECTION_HISTORY_MONTHS - 1, 0))->map(fn (int $monthsAgo) => $now->copy()->startOfMonth()->subMonths($monthsAgo));
        $countsByMonth = Inspection::where('inspected_at', '>=', $months->first())
            ->pluck('inspected_at')
            ->countBy(fn (Carbon $date) => $date->format('Y-m'));

        return $this->barChart(
            $months->map(fn (Carbon $month) => $month->isoFormat('MMM YYYY'))->all(),
            $months->map(fn (Carbon $month) => $countsByMonth->get($month->format('Y-m'), 0))->all(),
            ThemeColor::hex('primary'),
        );
    }

    private function barChart(array $labels, array $values, string $color, bool $horizontal = false): array
    {
        $valueAxis = $horizontal ? 'x' : 'y';

        return [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [['data' => $values, 'backgroundColor' => $color, 'borderRadius' => 4]],
            ],
            'options' => [
                'indexAxis' => $horizontal ? 'y' : 'x',
                'plugins' => ['legend' => ['display' => false]],
                'scales' => [$valueAxis => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
            ],
        ];
    }
}
