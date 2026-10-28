<?php

namespace Modules\Missions\Http\Controllers;

use App\Enums\AssetType;
use App\Enums\MissionNature;
use App\Enums\MissionStatus;
use App\Enums\MissionType;
use App\Enums\ObservedUnitLevel;
use App\Enums\SightingSource;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Asset;
use App\Models\ExternalInspection;
use App\Models\Mission;
use App\Models\Observer;
use App\Models\Vessel;
use App\Models\Zone;
use App\Support\Actions\ActionRunner;
use App\Support\Authorization\Permission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Missions\Actions\ScheduleMission;
use Modules\Missions\Http\Requests\ScheduleMissionRequest;
use Modules\Missions\Support\MissionAuthorizationRule;
use Modules\Missions\Support\MissionReviewCalculator;

class MissionController extends Controller
{
    private const PER_PAGE = 20;

    private const TRACK_MARKER_LIMIT = 60;

    private const NEVER_INSPECTED_LIMIT = 10;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(MissionStatus::class)],
            'type' => ['nullable', Rule::enum(MissionType::class)],
            'nature' => ['nullable', Rule::enum(MissionNature::class)],
        ]);

        $missions = Mission::query()
            ->withCount(['participants', 'observedUnits'])
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['type'] ?? null, fn (Builder $query, string $type) => $query->where('type', $type))
            ->when($filters['nature'] ?? null, fn (Builder $query, string $nature) => $query->where('nature', $nature))
            ->orderByDesc('planned_start_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $neverInspected = DB::table('v_seen_never_inspected')
            ->join('vessels', 'vessels.id', '=', 'v_seen_never_inspected.vessel_id')
            ->orderByDesc('sighted_count')
            ->limit(self::NEVER_INSPECTED_LIMIT)
            ->get(['vessels.id', 'vessels.name', 'vessels.registration_number', 'sighted_count']);

        return view('missions::index', compact('missions', 'filters', 'neverInspected'));
    }

    public function create(Request $request): View
    {
        return view('missions::create', [
            'zones' => Zone::orderBy('name')->pluck('name', 'id'),
            'agents' => Agent::orderBy('name')->pluck('name', 'id'),
            'assets' => Asset::where('is_active', true)->orderBy('label')->pluck('label', 'id'),
            'prefill' => ['witness_report_id' => $request->integer('witness_report') ?: null, 'operation_id' => $request->integer('operation') ?: null],
        ]);
    }

    public function store(ScheduleMissionRequest $request, ActionRunner $actions): RedirectResponse
    {
        $mission = $actions->run(ScheduleMission::class, $request->user(), $request->validated());

        return to_route('missions.show', $mission)->with('status', __('missions::missions.messages.scheduled'));
    }

    public function show(Mission $mission, MissionReviewCalculator $calculator): View
    {
        $mission->load([
            'authorizations.authorizer', 'participants.agent', 'missionAssets.asset', 'briefing', 'preparation', 'tracks',
            'observedUnits.vessel', 'observedUnits.inspection', 'events', 'debriefing', 'review', 'infractionCases.vessel',
            'observerBoardings.observer.person', 'observerBoardings.vessel', 'operation', 'witnessReport', 'activitiesAsSubject.causer',
        ]);

        return view('missions::show', [
            'mission' => $mission,
            'computed' => $calculator->compute($mission),
            'canAuthorize' => auth()->user()->can(Permission::MissionsAuthorizeAsDirector->value) || MissionAuthorizationRule::teamLeadMayAuthorize($mission->type),
            'markers' => $this->markers($mission),
            'options' => [
                'agents' => Agent::orderBy('name')->pluck('name', 'id'),
                'assets' => Asset::where('is_active', true)->orderBy('label')->pluck('label', 'id'),
                'assetTypes' => AssetType::options(),
                'vessels' => Vessel::orderBy('name')->pluck('name', 'id'),
                'inspections' => ExternalInspection::latest('started_at')->limit(100)->get()->mapWithKeys(fn (ExternalInspection $inspection) => [$inspection->id => $inspection->reference ?? $inspection->external_id]),
                'observers' => Observer::with('person')->get()->mapWithKeys(fn (Observer $observer) => [$observer->id => $observer->person->full_name]),
                'levels' => ObservedUnitLevel::options(),
                'sources' => SightingSource::options(),
            ],
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    private function markers(Mission $mission): array
    {
        $units = $mission->observedUnits->filter(fn ($unit) => $unit->latitude !== null && $unit->longitude !== null)->map(fn ($unit) => [
            'latitude' => (float) $unit->latitude,
            'longitude' => (float) $unit->longitude,
            'label' => ($unit->vessel?->name ?? $unit->recorded_name ?? '—').' — '.__($unit->level->label()),
            'color' => $unit->level === ObservedUnitLevel::Inspected ? '#2ed8b6' : ($unit->level === ObservedUnitLevel::Approached ? '#ffb64d' : '#4099ff'),
        ])->values()->all();

        $track = $mission->tracks->first();

        if (! $track) {
            return $units;
        }

        $points = DB::select('SELECT ST_Y((dp).geom) AS latitude, ST_X((dp).geom) AS longitude FROM (SELECT ST_DumpPoints(geometry) AS dp FROM patrol_tracks WHERE id = ?) AS points', [$track->id]);
        $step = max(1, intdiv(count($points), self::TRACK_MARKER_LIMIT));

        $trackMarkers = collect($points)->filter(fn ($point, $index) => $index % $step === 0)->map(fn ($point) => [
            'latitude' => (float) $point->latitude,
            'longitude' => (float) $point->longitude,
            'label' => __('missions::missions.track_point'),
            'color' => '#01a9ac',
        ])->values()->all();

        return [...$trackMarkers, ...$units];
    }
}
