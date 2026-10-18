<?php

namespace Modules\Missions\Http\Controllers;

use App\Enums\MissionStatus;
use App\Enums\MissionType;
use App\Http\Controllers\Controller;
use App\Models\Mission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MissionController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(MissionStatus::class)],
            'type' => ['nullable', Rule::enum(MissionType::class)],
        ]);

        $missions = Mission::query()
            ->with('zone')
            ->withCount(['vessels', 'agents'])
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['type'] ?? null, fn (Builder $query, string $type) => $query->where('type', $type))
            ->latest('starts_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('missions::index', compact('missions', 'filters'));
    }

    public function show(Mission $mission): View
    {
        $mission->load(['zone', 'vessels', 'agents.team', 'activitiesAsSubject.causer']);

        return view('missions::show', compact('mission'));
    }
}
