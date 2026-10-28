<?php

namespace Modules\Vessels\Http\Controllers;

use App\Enums\VesselCategory;
use App\Enums\VesselStatus;
use App\Http\Controllers\Controller;
use App\Models\ExternalList;
use App\Models\LegalEntity;
use App\Models\Person;
use App\Models\Vessel;
use App\Queries\OpenInfractionCases;
use App\Queries\VesselMissions;
use App\Support\Actions\ActionRunner;
use App\Support\Authorization\Permission;
use App\Support\Vessels\StatusLight;
use App\Support\Vessels\VesselIndicators;
use App\Support\Vessels\VigilanceScore;
use App\Support\Vms\ThemisLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Vessels\Actions\RegisterVessel;
use Modules\Vessels\Actions\UpdateVessel;
use Modules\Vessels\Http\Requests\VesselRequest;
use Modules\Vessels\Support\VesselTimeline;

class VesselController extends Controller
{
    private const PER_PAGE = 20;

    private const TRACK_LENGTH = 12;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', Rule::enum(VesselCategory::class)],
            'status' => ['nullable', Rule::enum(VesselStatus::class)],
            'flag' => ['nullable', 'string', 'size:2'],
        ]);

        $vessels = Vessel::query()
            ->with(['currentLicense', 'latestPosition', 'flagCountry'])
            ->search($filters['search'] ?? null)
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category', $category))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['flag'] ?? null, fn ($query, $flag) => $query->whereHas('flagCountry', fn ($countries) => $countries->where('iso2_code', $flag)))
            ->orderBy('name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('vessels::index', compact('vessels', 'filters'));
    }

    public function show(Request $request, Vessel $vessel): View
    {
        $timelineModule = $request->validate(['module' => ['nullable', 'string', 'max:30'], 'tab' => ['nullable', 'string', 'max:30']]);

        $vessel->load([
            'licenses',
            'trackingDevices',
            'latestPosition.zone',
            'movements.port',
            'observations.author',
            'externalInspections.port',
            'catchDeclarations.species',
            'currentState',
            'eezDeclarations',
            'watchEntries',
            'alerts.zone',
            'sentMessages',
            'internalNotes',
            'comments.author',
            'licenseDocuments.document',
            'flagCountry',
            'homePort',
            'vesselType',
            'identifiers',
            'history.formerFlagCountry',
            'actors.legalEntity',
            'actors.person',
            'sources',
            'fieldProvenance',
            'externalLists.institution',
        ]);

        $vessel->setRelation('infractions', OpenInfractionCases::forVessel($vessel->id));
        $vessel->setRelation('missions', VesselMissions::forVessel($vessel->id));

        $indicators = VesselIndicators::for($vessel);

        return view('vessels::show', [
            'vessel' => $vessel,
            'tabs' => $this->tabs(),
            'activeTab' => $timelineModule['tab'] ?? null,
            'assessment' => StatusLight::assess($indicators->facts),
            'indicators' => $indicators,
            'score' => VigilanceScore::compute($indicators),
            'themisUrl' => app(ThemisLink::class)->url($vessel),
            'timelineModule' => $timelineModule['module'] ?? null,
            'timelineModules' => VesselTimeline::modulesOf($vessel),
            'track' => $vessel->positions()->latest('recorded_at')->limit(self::TRACK_LENGTH)->get(),
            'timeline' => VesselTimeline::for($vessel, $timelineModule['module'] ?? null),
            'legalEntities' => LegalEntity::orderBy('name')->pluck('name', 'id'),
            'people' => Person::orderBy('name')->get()->pluck('full_name', 'id'),
            'externalLists' => ExternalList::orderBy('label')->pluck('label', 'id'),
        ]);
    }

    public function create(): View
    {
        return view('vessels::create', ['vessel' => new Vessel]);
    }

    public function store(VesselRequest $request, ActionRunner $actions): RedirectResponse
    {
        $vessel = $actions->run(RegisterVessel::class, $request->user(), $request->validated());

        return to_route('vessels.show', $vessel)->with('status', __('vessels::vessels.created'));
    }

    public function edit(Vessel $vessel): View
    {
        return view('vessels::edit', compact('vessel'));
    }

    public function update(VesselRequest $request, Vessel $vessel, ActionRunner $actions): RedirectResponse
    {
        $actions->run(UpdateVessel::class, $request->user(), ['vessel_id' => $vessel->id, 'attributes' => $request->validated()]);

        return to_route('vessels.show', $vessel)->with('status', __('vessels::vessels.updated'));
    }

    /** @return array<int, string> */
    private function tabs(): array
    {
        $gated = [
            'indicators' => Permission::IndicatorsView,
            'licenses' => Permission::LicensesView,
            'devices' => Permission::TrackingView,
            'alerts' => Permission::TrackingView,
            'eez' => Permission::MovementsView,
            'watch' => Permission::WatchView,
            'inspections' => Permission::InspectionsView,
            'infractions' => Permission::InfractionsView,
            'communications' => Permission::CommunicationsView,
            'documents' => Permission::LicensesView,
        ];

        return collect(['indicators', 'identity', 'identifiers', 'actors', 'sources', 'licenses', 'devices', 'alerts', 'movements', 'eez', 'watch', 'observations', 'inspections', 'infractions', 'catches', 'missions', 'communications', 'documents', 'comments', 'timeline'])
            ->filter(fn (string $tab) => ! isset($gated[$tab]) || auth()->user()->can($gated[$tab]->value))
            ->values()
            ->all();
    }
}
