<?php

namespace Modules\Inspections\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Queries\NeverInspectedVessels;
use App\Support\Tables\PerPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NeverInspectedController extends Controller
{
    private const PER_PAGE = 10;

    public const THRESHOLDS = [1, 2, 3, 5, 10];

    public const DEFAULT_THRESHOLD = 2;

    public function __invoke(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'min' => ['nullable', 'integer', Rule::in(self::THRESHOLDS)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $minimum = (int) ($filters['min'] ?? self::DEFAULT_THRESHOLD);

        $vessels = NeverInspectedVessels::query()
            ->where('seen.sighted_count', '>=', $minimum)
            ->when($filters['search'] ?? null, fn (Builder $query, string $term) => $query->search($term))
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->where('last_seen.last_seen_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->where('last_seen.last_seen_at', '<', now()->parse($to)->addDay()))
            ->orderByDesc('seen.sighted_count')
            ->orderByDesc('last_seen.last_seen_at')
            ->paginate(PerPage::from($request, self::PER_PAGE))
            ->withQueryString();

        return view('inspections::never-inspected', [
            'vessels' => $vessels,
            'filters' => $filters,
            'minimum' => $minimum,
            'thresholds' => collect(self::THRESHOLDS)->mapWithKeys(fn (int $count) => [$count => trans_choice('inspections::never.at_least', $count, ['count' => $count])])->all(),
        ]);
    }
}
