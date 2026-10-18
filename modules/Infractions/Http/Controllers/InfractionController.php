<?php

namespace Modules\Infractions\Http\Controllers;

use App\Enums\InfractionStatus;
use App\Http\Controllers\Controller;
use App\Models\Infraction;
use App\Models\InfractionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Infractions\Http\Requests\UpdateInfractionRequest;

class InfractionController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(InfractionStatus::class)],
            'type' => ['nullable', 'exists:infraction_types,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $infractions = Infraction::query()
            ->with(['vessel', 'type'])
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['type'] ?? null, fn (Builder $query, string $type) => $query->where('infraction_type_id', $type))
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->whereDate('detected_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->whereDate('detected_at', '<=', $to))
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(fn (Builder $query) => $query
                ->where('reference', 'like', "%{$search}%")
                ->orWhereHas('vessel', fn (Builder $query) => $query->search($search))))
            ->latest('detected_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('infractions::index', [
            'infractions' => $infractions,
            'filters' => $filters,
            'types' => InfractionType::orderBy('code')->pluck('name', 'id'),
        ]);
    }

    public function show(Infraction $infraction): View
    {
        $infraction->load(['vessel', 'type', 'inspection', 'activitiesAsSubject.causer']);

        return view('infractions::show', compact('infraction'));
    }

    public function update(UpdateInfractionRequest $request, Infraction $infraction): RedirectResponse
    {
        $infraction->update($request->validated());

        return to_route('infractions.show', $infraction)->with('status', __('infractions::infractions.updated'));
    }
}
