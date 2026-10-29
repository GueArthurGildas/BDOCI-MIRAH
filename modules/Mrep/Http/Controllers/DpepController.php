<?php

namespace Modules\Mrep\Http\Controllers;

use App\Enums\EntryDecision;
use App\Enums\MrepPurpose;
use App\Http\Controllers\Controller;
use App\Models\MrepDpep;
use App\Models\Species;
use App\Support\Tables\PerPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DpepController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'purpose' => ['nullable', Rule::enum(MrepPurpose::class)],
            'decision' => ['nullable', Rule::enum(EntryDecision::class)],
        ]);

        $dpeps = MrepDpep::query()
            ->with(['vessel', 'port', 'latestDecision'])
            ->when($filters['purpose'] ?? null, fn (Builder $query, string $purpose) => $query->where('purpose', $purpose))
            ->when($filters['decision'] ?? null, fn (Builder $query, string $decision) => $query->whereHas('latestDecision', fn (Builder $query) => $query->where('decision', $decision)))
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(fn (Builder $query) => $query
                ->whereLike('number', "%{$search}%")
                ->orWhereLike('external_id', "%{$search}%")
                ->orWhereHas('vessel', fn (Builder $query) => $query->search($search))))
            ->latest('planned_arrival_at')
            ->paginate(PerPage::from($request, self::PER_PAGE))
            ->withQueryString();

        return view('mrep::dpeps.index', compact('dpeps', 'filters'));
    }

    public function show(MrepDpep $dpep): View
    {
        $dpep->load(['vessel', 'port', 'entryDecisions' => fn ($query) => $query->latest('decided_at'), 'portCalls.inspection', 'notifications']);

        return view('mrep::dpeps.show', [
            'dpep' => $dpep,
            'species' => Species::pluck('name', 'fao_code'),
        ]);
    }
}
