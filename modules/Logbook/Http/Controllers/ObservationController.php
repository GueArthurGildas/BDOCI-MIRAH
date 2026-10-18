<?php

namespace Modules\Logbook\Http\Controllers;

use App\Enums\ObservationLevel;
use App\Enums\ObservationStatus;
use App\Http\Controllers\Controller;
use App\Models\Observation;
use App\Models\Team;
use App\Models\Vessel;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Logbook\Http\Requests\StoreObservationRequest;
use Modules\Logbook\Http\Requests\UpdateObservationRequest;

class ObservationController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(ObservationStatus::class)],
            'level' => ['nullable', Rule::enum(ObservationLevel::class)],
            'team' => ['nullable', 'exists:teams,id'],
            'pending' => ['nullable', 'boolean'],
        ]);

        $observations = Observation::query()
            ->with(['vessel', 'author', 'team'])
            ->when($filters['pending'] ?? false, fn (Builder $query) => $query->pending())
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['level'] ?? null, fn (Builder $query, string $level) => $query->where('level', $level))
            ->when($filters['team'] ?? null, fn (Builder $query, string $team) => $query->where('team_id', $team))
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->whereHas('vessel', fn (Builder $query) => $query->search($search)))
            ->latest('observed_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('logbook::index', [
            'observations' => $observations,
            'filters' => $filters,
            'teams' => Team::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function create(): View
    {
        return view('logbook::create', [
            'vessels' => Vessel::orderBy('name')->get()->mapWithKeys(fn (Vessel $vessel) => [$vessel->id => "{$vessel->name} ({$vessel->registration_number})"]),
            'zones' => Zone::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function store(StoreObservationRequest $request): RedirectResponse
    {
        $observation = Observation::create([
            ...$request->validated(),
            'author_id' => $request->user()->id,
            'team_id' => $request->user()->team_id,
            'status' => ObservationStatus::Open,
        ]);

        return to_route('logbook.show', $observation)->with('status', __('logbook::logbook.created'));
    }

    public function show(Observation $observation): View
    {
        $observation->load(['vessel', 'zone', 'author', 'team', 'activitiesAsSubject.causer']);

        return view('logbook::show', compact('observation'));
    }

    public function update(UpdateObservationRequest $request, Observation $observation): RedirectResponse
    {
        $status = ObservationStatus::from($request->validated('status'));

        $observation->update([
            'status' => $status,
            'follow_up' => $request->validated('follow_up'),
            'closed_at' => $status === ObservationStatus::Closed ? ($observation->closed_at ?? now()) : null,
        ]);

        return to_route('logbook.show', $observation)->with('status', __('logbook::logbook.updated'));
    }
}
