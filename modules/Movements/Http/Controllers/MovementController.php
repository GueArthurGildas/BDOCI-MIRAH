<?php

namespace Modules\Movements\Http\Controllers;

use App\Enums\MovementDirection;
use App\Http\Controllers\Controller;
use App\Models\Movement;
use App\Models\Port;
use App\Models\Vessel;
use App\Support\Actions\ActionRunner;
use App\Support\Tables\PerPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Movements\Actions\RecordMovement;
use Modules\Movements\Http\Requests\StoreMovementRequest;

class MovementController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'direction' => ['nullable', Rule::enum(MovementDirection::class)],
            'port' => ['nullable', 'exists:ports,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $movements = Movement::query()
            ->with(['vessel', 'port', 'recorder'])
            ->when($filters['direction'] ?? null, fn (Builder $query, string $direction) => $query->where('direction', $direction))
            ->when($filters['port'] ?? null, fn (Builder $query, string $port) => $query->where('port_id', $port))
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->whereDate('occurred_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->whereDate('occurred_at', '<=', $to))
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->whereHas('vessel', fn (Builder $query) => $query->search($search)))
            ->latest('occurred_at')
            ->paginate(PerPage::from($request, self::PER_PAGE))
            ->withQueryString();

        return view('movements::index', [
            'movements' => $movements,
            'filters' => $filters,
            'ports' => Port::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function create(): View
    {
        return view('movements::create', [
            'vessels' => Vessel::orderBy('name')->get()->mapWithKeys(fn (Vessel $vessel) => [$vessel->id => "{$vessel->name} ({$vessel->registration_number})"]),
            'ports' => Port::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function store(StoreMovementRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(RecordMovement::class, $request->user(), $request->validated());

        return to_route('movements.index')->with('status', __('movements::movements.created'));
    }
}
