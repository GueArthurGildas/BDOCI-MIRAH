<?php

namespace Modules\Vessels\Http\Controllers;

use App\Enums\VesselCategory;
use App\Enums\VesselStatus;
use App\Http\Controllers\Controller;
use App\Models\Vessel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
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
            ->with(['currentLicense', 'latestPosition'])
            ->search($filters['search'] ?? null)
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category', $category))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['flag'] ?? null, fn ($query, $flag) => $query->where('flag', $flag))
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
            'inspections.port',
            'infractions.type',
            'catchDeclarations.species',
            'missions',
        ]);

        return view('vessels::show', [
            'vessel' => $vessel,
            'track' => $vessel->positions()->latest('recorded_at')->limit(self::TRACK_LENGTH)->get(),
            'timeline' => VesselTimeline::for($vessel),
        ]);
    }

    public function create(): View
    {
        return view('vessels::create', ['vessel' => new Vessel]);
    }

    public function store(VesselRequest $request): RedirectResponse
    {
        $vessel = Vessel::create($request->validated());

        return to_route('vessels.show', $vessel)->with('status', __('vessels::vessels.created'));
    }

    public function edit(Vessel $vessel): View
    {
        return view('vessels::edit', compact('vessel'));
    }

    public function update(VesselRequest $request, Vessel $vessel): RedirectResponse
    {
        $vessel->update($request->validated());

        return to_route('vessels.show', $vessel)->with('status', __('vessels::vessels.updated'));
    }
}
