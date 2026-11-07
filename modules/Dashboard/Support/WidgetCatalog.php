<?php

namespace Modules\Dashboard\Support;

use App\Enums\AlertStatus;
use App\Enums\FleetSegment;
use App\Enums\InfractionCaseStatus;
use App\Enums\LicenseStatus;
use App\Enums\MissionStatus;
use App\Enums\ObservationLevel;
use App\Enums\OperationStatus;
use App\Enums\VesselStatus;
use App\Enums\VesselStatusLight;
use App\Enums\ZoneType;
use App\Models\Alert;
use App\Models\Country;
use App\Models\DataSource;
use App\Models\ExternalInspection;
use App\Models\InfractionCase;
use App\Models\License;
use App\Models\Observation;
use App\Models\Operation;
use App\Models\User;
use App\Models\Vessel;
use App\Models\VesselCurrentState;
use App\Models\Zone;
use App\Queries\VesselMissions;
use App\Queries\ZoneSituation;
use App\Support\Authorization\Permission;
use App\Support\Indicators\IndicatorCatalog;
use App\Support\Indicators\IndicatorFilters;
use App\Support\Indicators\IndicatorRow;
use App\Support\Presentation\HasBadge;
use App\Support\Presentation\ThemeColor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class WidgetCatalog
{
    public const DEFAULT_KEYS = [
        'active_vessels',
        'expiring_licenses',
        'pending_observations',
        'silent_beacons',
        'zones',
        'infractions_by_status',
        'inspections_monthly',
        'data_sources',
        'pending_observation_list',
    ];

    private const PALETTE = ['primary', 'warning', 'info', 'success', 'danger', 'inverse'];

    private const HISTORY_MONTHS = 6;

    private const SAMPLE_SIZE = 5;

    private const TOP_FLAGS = 8;

    private const LATEST_OBSERVATIONS = 5;

    public function __construct(private readonly IndicatorCatalog $indicators) {}

    /** @return Collection<string, Widget> */
    public function all(): Collection
    {
        return collect([
            new Widget('active_vessels', Widget::KPI, 'icon-anchor', 'success', Permission::VesselsView, 'vessels.index'),
            new Widget('pending_observations', Widget::KPI, 'icon-book', 'info', Permission::LogbookView, 'logbook.index'),
            ...collect(IndicatorCatalog::KEYS)->map(fn (array $style, string $key) => new Widget(
                $key, Widget::KPI, $style['icon'], $style['color'], Permission::IndicatorsView, 'indicators.show', $key,
            ))->values(),
            new Widget('zones', Widget::CHART, 'icon-map-pin', 'inverse', Permission::TrackingView, 'tracking.map'),
            new Widget('infractions_by_status', Widget::CHART, 'icon-alert-triangle', 'danger', Permission::InfractionsView, 'infractions.index'),
            new Widget('inspections_monthly', Widget::CHART, 'icon-clipboard', 'primary', Permission::InspectionsView, 'inspections.index'),
            new Widget('fleet_status_lights', Widget::CHART, 'icon-activity', 'success', Permission::IndicatorsView, 'fleet.index'),
            new Widget('vessels_by_segment', Widget::CHART, 'icon-layers', 'info', Permission::VesselsView, 'vessels.index'),
            new Widget('vessels_by_flag', Widget::CHART, 'icon-flag', 'primary', Permission::VesselsView, 'vessels.index'),
            new Widget('licenses_by_status', Widget::CHART, 'icon-file-text', 'warning', Permission::LicensesView, 'licenses.index'),
            new Widget('licenses_expiring_by_month', Widget::CHART, 'icon-calendar', 'warning', Permission::LicensesView, 'licenses.index'),
            new Widget('alerts_by_status', Widget::CHART, 'icon-bell', 'danger', Permission::TrackingView, 'tracking.alerts.index'),
            new Widget('observations_by_level', Widget::CHART, 'icon-eye', 'info', Permission::LogbookView, 'logbook.index'),
            new Widget('operations_by_status', Widget::CHART, 'icon-clipboard', 'info', Permission::OperationsView, 'operations.index'),
            new Widget('missions_by_status', Widget::CHART, 'icon-flag', 'primary', Permission::MissionsView, 'missions.index'),
            new Widget('data_sources', Widget::TABLE, 'icon-database', 'inverse', Permission::DashboardView),
            new Widget('pending_observation_list', Widget::TABLE, 'icon-book', 'info', Permission::LogbookView, 'logbook.index'),
        ])->keyBy('key');
    }

    /** @return Collection<string, Widget> */
    public function availableFor(User $user): Collection
    {
        return $this->all()->filter(fn (Widget $widget) => $user->can($widget->permission->value));
    }

    /** @return Collection<string, Widget> */
    public function selectedFor(User $user): Collection
    {
        $available = $this->availableFor($user);
        $keys = $user->dashboard_widgets ?? self::DEFAULT_KEYS;

        return $available->only($keys)->sortBy(fn (Widget $widget) => array_search($widget->key, $keys, true));
    }

    public function data(Widget $widget): WidgetData
    {
        return match ($widget->kind) {
            Widget::KPI => new WidgetData($widget, value: $this->kpiValue($widget)),
            Widget::TABLE => $this->table($widget),
            default => $this->chart($widget),
        };
    }

    /** @return Collection<int, IndicatorRow> */
    public function sample(Widget $widget): Collection
    {
        return match (true) {
            $widget->indicator !== null => $this->indicators->rows($widget->indicator, new IndicatorFilters)->take(self::SAMPLE_SIZE)->values(),
            $widget->key === 'active_vessels' => Vessel::where('status', VesselStatus::Active)->orderBy('name')->limit(self::SAMPLE_SIZE)->get()
                ->map(fn (Vessel $vessel) => new IndicatorRow($vessel, null))->values(),
            default => collect(),
        };
    }

    private function kpiValue(Widget $widget): int
    {
        return match (true) {
            $widget->indicator !== null => $this->indicators->count($widget->indicator, new IndicatorFilters),
            $widget->key === 'active_vessels' => Vessel::where('status', VesselStatus::Active)->count(),
            default => Observation::pending()->count(),
        };
    }

    private function table(Widget $widget): WidgetData
    {
        return match ($widget->key) {
            'data_sources' => new WidgetData($widget, records: DataSource::withIssueCounts()->orderBy('name')->get()),
            default => new WidgetData($widget, records: Observation::pending()->with('vessel')->latest('observed_at')->limit(self::LATEST_OBSERVATIONS)->get()),
        };
    }

    private function chart(Widget $widget): WidgetData
    {
        [$figures, $type, $horizontal] = match ($widget->key) {
            'zones' => [$this->zoneFigures(), 'bar', true],
            'infractions_by_status' => [$this->enumFigures(InfractionCase::query()->selectRaw('status as bucket, count(*) as total')->groupBy('status')->pluck('total', 'bucket'), InfractionCaseStatus::class), 'doughnut', false],
            'inspections_monthly' => [$this->inspectionFigures(), 'bar', false],
            'fleet_status_lights' => [$this->enumFigures(VesselCurrentState::query()->selectRaw('status_light as bucket, count(*) as total')->groupBy('status_light')->pluck('total', 'bucket'), VesselStatusLight::class), 'doughnut', false],
            'vessels_by_segment' => [$this->enumFigures(Vessel::query()->selectRaw('fleet_segment as bucket, count(*) as total')->groupBy('fleet_segment')->pluck('total', 'bucket'), FleetSegment::class), 'doughnut', false],
            'vessels_by_flag' => [$this->flagFigures(), 'bar', true],
            'licenses_by_status' => [$this->licenseFigures(), 'doughnut', false],
            'licenses_expiring_by_month' => [$this->expiringLicenseFigures(), 'bar', false],
            'alerts_by_status' => [$this->enumFigures(Alert::query()->selectRaw('status as bucket, count(*) as total')->groupBy('status')->pluck('total', 'bucket'), AlertStatus::class), 'doughnut', false],
            'observations_by_level' => [$this->enumFigures(Observation::pending()->selectRaw('level as bucket, count(*) as total')->groupBy('level')->pluck('total', 'bucket'), ObservationLevel::class), 'bar', false],
            'operations_by_status' => [$this->enumFigures(Operation::query()->selectRaw('status as bucket, count(*) as total')->groupBy('status')->pluck('total', 'bucket'), OperationStatus::class), 'doughnut', false],
            'missions_by_status' => [$this->enumFigures(VesselMissions::countByStatus(), MissionStatus::class), 'doughnut', false],
        };

        return new WidgetData($widget, chart: $type === 'doughnut' ? $this->doughnut($figures) : $this->bar($figures, $horizontal), figures: $figures);
    }

    /** @param class-string<\BackedEnum&HasBadge> $enum */
    private function enumFigures(Collection $counts, string $enum): array
    {
        return collect($enum::cases())->values()->map(fn ($case, int $index) => [
            'label' => __($case->label()),
            'value' => (int) $counts->get($case->value, 0),
            'color' => ThemeColor::hex(method_exists($case, 'color') ? $case->color() : self::PALETTE[$index % count(self::PALETTE)]),
        ])->all();
    }

    private function zoneFigures(): array
    {
        $counts = ZoneSituation::vesselCountByZoneAt(now());

        return Zone::where('type', ZoneType::PortArea)->get()
            ->sortByDesc(fn (Zone $zone) => $counts->get($zone->id, 0))
            ->map(fn (Zone $zone) => ['label' => $zone->name, 'value' => (int) $counts->get($zone->id, 0), 'color' => ThemeColor::hex('inverse')])
            ->values()->all();
    }

    private function inspectionFigures(): array
    {
        $months = $this->months(past: true);
        $counts = ExternalInspection::where('started_at', '>=', $months->first())->pluck('started_at')->countBy(fn (Carbon $date) => $date->format('Y-m'));

        return $months->map(fn (Carbon $month) => [
            'label' => $month->isoFormat('MMM YYYY'),
            'value' => (int) $counts->get($month->format('Y-m'), 0),
            'color' => ThemeColor::hex('primary'),
        ])->all();
    }

    private function flagFigures(): array
    {
        $counts = Vessel::query()->selectRaw('flag_country_id as bucket, count(*) as total')->groupBy('flag_country_id')->orderByDesc('total')->limit(self::TOP_FLAGS)->pluck('total', 'bucket');
        $countries = Country::whereIn('id', $counts->keys()->filter())->get()->keyBy('id');

        return $counts->map(fn ($total, $countryId) => [
            'label' => $countries->get($countryId)?->label ?? __('dashboard::widgets.unknown'),
            'value' => (int) $total,
            'color' => ThemeColor::hex('primary'),
        ])->values()->all();
    }

    private function licenseFigures(): array
    {
        return collect(LicenseStatus::cases())->map(fn (LicenseStatus $status) => [
            'label' => __($status->label()),
            'value' => License::withStatus($status)->count(),
            'color' => ThemeColor::hex($status->color()),
        ])->all();
    }

    private function expiringLicenseFigures(): array
    {
        return $this->months(past: false)->map(function (Carbon $month) {
            $from = $month->copy()->max(today());

            return [
                'label' => $month->isoFormat('MMM YYYY'),
                'value' => License::expiringBetween((int) today()->diffInDays($from), (int) today()->diffInDays($month->copy()->endOfMonth()))->count(),
                'color' => ThemeColor::hex('warning'),
            ];
        })->all();
    }

    /** @return Collection<int, Carbon> */
    private function months(bool $past): Collection
    {
        $start = now()->startOfMonth();

        return collect(range(0, self::HISTORY_MONTHS - 1))->map(fn (int $offset) => $past
            ? $start->copy()->subMonths(self::HISTORY_MONTHS - 1 - $offset)
            : $start->copy()->addMonths($offset));
    }

    private function doughnut(array $figures): array
    {
        return [
            'type' => 'doughnut',
            'data' => [
                'labels' => array_column($figures, 'label'),
                'datasets' => [['data' => array_column($figures, 'value'), 'backgroundColor' => array_column($figures, 'color')]],
            ],
            'options' => ['plugins' => ['legend' => ['display' => false]]],
        ];
    }

    private function bar(array $figures, bool $horizontal): array
    {
        return [
            'type' => 'bar',
            'data' => [
                'labels' => array_column($figures, 'label'),
                'datasets' => [['data' => array_column($figures, 'value'), 'backgroundColor' => array_column($figures, 'color'), 'borderRadius' => 4]],
            ],
            'options' => [
                'indexAxis' => $horizontal ? 'y' : 'x',
                'plugins' => ['legend' => ['display' => false]],
                'scales' => [($horizontal ? 'x' : 'y') => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
            ],
        ];
    }
}
