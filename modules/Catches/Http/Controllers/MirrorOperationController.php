<?php

namespace Modules\Catches\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Landing;
use App\Models\Transshipment;
use App\Support\Tables\PerPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MirrorOperationController extends Controller
{
    private const PER_PAGE = 20;

    public function landings(Request $request): View
    {
        $filters = $this->filters($request);

        $landings = Landing::query()
            ->with(['vessel', 'port'])
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->whereHas('vessel', fn (Builder $query) => $query->search($search)))
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->whereDate('landed_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->whereDate('landed_at', '<=', $to))
            ->latest('landed_at')
            ->paginate(PerPage::from($request, self::PER_PAGE))
            ->withQueryString();

        return view('catches::landings', compact('landings', 'filters'));
    }

    public function transshipments(Request $request): View
    {
        $filters = $this->filters($request);

        $transshipments = Transshipment::query()
            ->with(['vessel', 'partner'])
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->whereHas('vessel', fn (Builder $query) => $query->search($search)))
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->whereDate('transshipped_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->whereDate('transshipped_at', '<=', $to))
            ->latest('transshipped_at')
            ->paginate(PerPage::from($request, self::PER_PAGE))
            ->withQueryString();

        return view('catches::transshipments', compact('transshipments', 'filters'));
    }

    /** @return array<string, mixed> */
    private function filters(Request $request): array
    {
        return $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
    }
}
