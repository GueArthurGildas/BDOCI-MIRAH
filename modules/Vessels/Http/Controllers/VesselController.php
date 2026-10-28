<?php

namespace Modules\Vessels\Http\Controllers;

use App\Enums\VesselCategory;
use App\Enums\VesselStatus;
use App\Http\Controllers\Controller;
use App\Models\ExternalList;
use App\Models\LegalEntity;
use App\Models\Person;
use App\Models\Vessel;
use App\Support\Actions\ActionRunner;
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

    public function show(Vessel $vessel): View
    {
        $vessel->load([
            'licenses',
            'trackingDevices',
            'latestPosition.zone',
            'movements.port',
            'observations.author',
            'externalInspections.port',
            'infractions.type',
            'catchDeclarations.species',
            'missions',
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

        return view('vessels::show', [
            'vessel' => $vessel,
            'track' => $vessel->positions()->latest('recorded_at')->limit(self::TRACK_LENGTH)->get(),
            'timeline' => VesselTimeline::for($vessel),
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
}
