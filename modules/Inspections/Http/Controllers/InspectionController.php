<?php

namespace Modules\Inspections\Http\Controllers;

use App\Enums\ExternalLinkSource;
use App\Enums\InspectionLocationType;
use App\Enums\InspectionResult;
use App\Http\Controllers\Controller;
use App\Models\ExternalInspection;
use App\Models\ExternalLink;
use App\Support\Tables\PerPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InspectionController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'location_type' => ['nullable', Rule::enum(InspectionLocationType::class)],
            'result' => ['nullable', Rule::enum(InspectionResult::class)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $inspections = ExternalInspection::query()
            ->with(['vessel', 'port'])
            ->withCount(['infractions', 'catches as exceeding_catches_count' => fn (Builder $query) => $query->where('exceeds_csp_threshold', true)])
            ->when($filters['location_type'] ?? null, fn (Builder $query, string $type) => $query->where('location_type', $type))
            ->when($filters['result'] ?? null, fn (Builder $query, string $result) => $query->where('result', $result))
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->whereDate('started_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->whereDate('started_at', '<=', $to))
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(fn (Builder $query) => $query
                ->whereLike('reference', "%{$search}%")
                ->orWhereLike('external_id', "%{$search}%")
                ->orWhereHas('vessel', fn (Builder $query) => $query->search($search))))
            ->latest('started_at')
            ->paginate(PerPage::from($request, self::PER_PAGE))
            ->withQueryString();

        return view('inspections::index', compact('inspections', 'filters'));
    }

    public function show(ExternalInspection $inspection): View
    {
        $inspection->load(['vessel', 'port', 'documents', 'gears.gear', 'catches.species', 'catches.presentation', 'infractions']);

        return view('inspections::show', [
            'inspection' => $inspection,
            'link' => ExternalLink::forSource(ExternalLinkSource::EInspection),
        ]);
    }
}
