<?php

namespace Modules\Catches\Http\Controllers;

use App\Enums\CatchSource;
use App\Http\Controllers\Controller;
use App\Models\CatchDeclaration;
use App\Models\Species;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CatchDeclarationController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'species' => ['nullable', 'exists:species,id'],
            'zone' => ['nullable', 'exists:zones,id'],
            'source' => ['nullable', Rule::enum(CatchSource::class)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $query = CatchDeclaration::query()
            ->when($filters['species'] ?? null, fn (Builder $query, string $species) => $query->where('species_id', $species))
            ->when($filters['zone'] ?? null, fn (Builder $query, string $zone) => $query->where('zone_id', $zone))
            ->when($filters['source'] ?? null, fn (Builder $query, string $source) => $query->where('source', $source))
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->whereDate('period_start', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->whereDate('period_end', '<=', $to))
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->whereHas('vessel', fn (Builder $query) => $query->search($search)));

        $quantityBySpecies = (clone $query)
            ->selectRaw('species_id, sum(quantity_kg) as total_kg')
            ->groupBy('species_id')
            ->pluck('total_kg', 'species_id');

        $species = Species::orderBy('name')->get();

        return view('catches::index', [
            'declarations' => $query->with(['vessel', 'species', 'zone'])->latest('period_start')->paginate(self::PER_PAGE)->withQueryString(),
            'filters' => $filters,
            'species' => $species->pluck('name', 'id'),
            'zones' => Zone::orderBy('name')->pluck('name', 'id'),
            'totalQuantity' => $quantityBySpecies->sum(),
            'chart' => [
                'type' => 'bar',
                'data' => [
                    'labels' => $species->pluck('name'),
                    'datasets' => [[
                        'data' => $species->map(fn (Species $species) => round((float) $quantityBySpecies->get($species->id, 0))),
                        'backgroundColor' => '#01a9ac',
                        'borderRadius' => 4,
                    ]],
                ],
                'options' => [
                    'plugins' => ['legend' => ['display' => false]],
                    'scales' => ['y' => ['beginAtZero' => true]],
                ],
            ],
        ]);
    }
}
