<?php

namespace Modules\Catches\Http\Controllers;

use App\Enums\CatchSource;
use App\Http\Controllers\Controller;
use App\Models\CatchDeclaration;
use App\Models\Species;
use App\Models\Vessel;
use App\Models\Zone;
use App\Support\Tables\PerPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CatchDeclarationController extends Controller
{
    private const PER_PAGE = 10;

    private const TOP_VESSELS = 10;

    private const PERIODS = ['30d' => 30, '90d' => 90, '365d' => 365];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'species' => ['nullable', 'exists:species,id'],
            'zone' => ['nullable', 'exists:zones,id'],
            'source' => ['nullable', Rule::enum(CatchSource::class)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'vessel' => ['nullable', 'integer', 'exists:vessels,id'],
            'last' => ['nullable', Rule::in(array_keys(self::PERIODS))],
        ]);

        if (isset($filters['last'])) {
            $filters['from'] = today()->subDays(self::PERIODS[$filters['last']])->toDateString();
            unset($filters['to']);
        }

        $query = CatchDeclaration::query()
            ->when($filters['vessel'] ?? null, fn (Builder $query, string $vessel) => $query->where('vessel_id', $vessel))
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
        $speciesById = $species->keyBy('id');
        $totalQuantity = (float) $quantityBySpecies->sum();

        $bySpecies = (clone $query)
            ->selectRaw('species_id, sum(quantity_kg) as total_kg, count(distinct vessel_id) as vessels_count')
            ->groupBy('species_id')
            ->orderByDesc('total_kg')
            ->get()
            ->map(fn ($row) => [
                'species' => $speciesById->get($row->species_id),
                'total_kg' => (float) $row->total_kg,
                'vessels_count' => (int) $row->vessels_count,
                'share' => $totalQuantity > 0 ? (float) $row->total_kg / $totalQuantity * 100 : 0,
            ]);

        $byPeriod = (clone $query)
            ->selectRaw("to_char(period_start, 'YYYY-MM') as month, sum(quantity_kg) as total_kg")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total_kg', 'month');

        $byVessel = (clone $query)
            ->selectRaw('vessel_id, sum(quantity_kg) as total_kg, count(distinct species_id) as species_count')
            ->groupBy('vessel_id')
            ->orderByDesc('total_kg')
            ->limit(self::TOP_VESSELS)
            ->get();
        $vesselModels = Vessel::query()->whereIn('id', $byVessel->pluck('vessel_id'))->get()->keyBy('id');

        return view('catches::index', [
            'declarations' => $query->with(['vessel', 'species', 'zone'])->latest('period_start')->paginate(PerPage::from($request, self::PER_PAGE))->withQueryString(),
            'filters' => $filters,
            'species' => $species->pluck('name', 'id'),
            'zones' => Zone::orderBy('name')->pluck('name', 'id'),
            'totalQuantity' => $totalQuantity,
            'bySpecies' => $bySpecies,
            'byVessel' => $byVessel->map(fn ($row) => ['vessel' => $vesselModels->get($row->vessel_id), 'total_kg' => (float) $row->total_kg, 'species_count' => (int) $row->species_count])->filter(fn (array $row) => $row['vessel'])->values(),
            'selectedVessel' => isset($filters['vessel']) ? Vessel::find($filters['vessel']) : null,
            'vessels' => Vessel::query()->whereHas('catchDeclarations')->orderBy('name')->get()->mapWithKeys(fn (Vessel $vessel) => [$vessel->id => "{$vessel->name} ({$vessel->registration_number})"]),
            'periods' => collect(self::PERIODS)->mapWithKeys(fn (int $days, string $key) => [$key => __('catches::catches.periods.'.$key)])->all(),
            'sources' => CatchDeclaration::query()->selectRaw('source, count(*) as total, max(updated_at) as last_update')->groupBy('source')->get(),
            'periodChart' => [
                'type' => 'bar',
                'data' => [
                    'labels' => $byPeriod->keys()->all(),
                    'datasets' => [[
                        'data' => $byPeriod->map(fn ($total) => round((float) $total))->values()->all(),
                        'backgroundColor' => '#404e67',
                        'borderRadius' => 4,
                    ]],
                ],
                'options' => [
                    'plugins' => ['legend' => ['display' => false]],
                    'scales' => ['y' => ['beginAtZero' => true]],
                ],
            ],
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
