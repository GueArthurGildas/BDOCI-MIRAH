<?php

namespace Modules\Inspections\Http\Controllers;

use App\Enums\InspectionResult;
use App\Enums\InspectionType;
use App\Http\Controllers\Controller;
use App\Models\Inspection;
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
            'type' => ['nullable', Rule::enum(InspectionType::class)],
            'result' => ['nullable', Rule::enum(InspectionResult::class)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $inspections = Inspection::query()
            ->with(['vessel', 'port'])
            ->when($filters['type'] ?? null, fn (Builder $query, string $type) => $query->where('type', $type))
            ->when($filters['result'] ?? null, fn (Builder $query, string $result) => $query->where('result', $result))
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->whereDate('inspected_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->whereDate('inspected_at', '<=', $to))
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(fn (Builder $query) => $query
                ->whereLike('external_reference', "%{$search}%")
                ->orWhereHas('vessel', fn (Builder $query) => $query->search($search))))
            ->latest('inspected_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('inspections::index', compact('inspections', 'filters'));
    }

    public function show(Inspection $inspection): View
    {
        $inspection->load(['vessel', 'port', 'infractions.type']);

        return view('inspections::show', compact('inspection'));
    }
}
