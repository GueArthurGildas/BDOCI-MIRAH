<?php

namespace Modules\Infractions\Http\Controllers;

use App\Enums\InfractionCaseStatus;
use App\Enums\InfractionQualification;
use App\Http\Controllers\Controller;
use App\Models\ExternalInspection;
use App\Models\InfractionCase;
use App\Models\InfractionType;
use App\Models\Mission;
use App\Models\Vessel;
use App\Models\WitnessReport;
use App\Support\Actions\ActionRunner;
use App\Support\Documents\DocumentStore;
use App\Support\Infractions\RepeatOffense;
use App\Support\Tables\PerPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Infractions\Actions\OpenInfractionCase;
use Modules\Infractions\Actions\UpdateInfractionCase;
use Modules\Infractions\Http\Requests\OpenCaseRequest;
use Modules\Infractions\Http\Requests\UpdateCaseRequest;

class InfractionController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(InfractionCaseStatus::class)],
            'type' => ['nullable', 'exists:infraction_types,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $cases = InfractionCase::query()
            ->with(['vessel', 'upheld.type'])
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['type'] ?? null, fn (Builder $query, string $type) => $query->whereHas('upheld', fn (Builder $query) => $query->where('infraction_type_id', $type)))
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->whereDate('opened_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->whereDate('opened_at', '<=', $to))
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(fn (Builder $query) => $query
                ->whereLike('reference', "%{$search}%")
                ->orWhereHas('vessel', fn (Builder $query) => $query->search($search))))
            ->latest('opened_at')
            ->paginate(PerPage::from($request, self::PER_PAGE))
            ->withQueryString();

        return view('infractions::index', [
            'cases' => $cases,
            'filters' => $filters,
            'types' => InfractionType::orderBy('code')->pluck('label', 'id'),
        ]);
    }

    public function create(Request $request): View
    {
        return view('infractions::create', [
            'vessels' => Vessel::orderBy('name')->pluck('name', 'id'),
            'types' => InfractionType::active()->orderBy('code')->get()->mapWithKeys(fn (InfractionType $type) => [$type->id => "{$type->code} — {$type->label}"]),
            'inspections' => ExternalInspection::latest('started_at')->limit(100)->get()->mapWithKeys(fn (ExternalInspection $inspection) => [$inspection->id => $inspection->reference ?? $inspection->external_id]),
            'missions' => Mission::latest('planned_start_at')->limit(100)->pluck('code', 'id'),
            'reports' => WitnessReport::latest('received_at')->limit(100)->get()->mapWithKeys(fn (WitnessReport $report) => [$report->id => '#'.$report->id.' — '.$report->received_at->isoFormat('L')]),
            'qualifications' => InfractionQualification::options(),
            'prefill' => [
                'vessel_id' => $request->integer('vessel') ?: null,
                'inspection_id' => $request->integer('inspection') ?: null,
                'mission_id' => $request->integer('mission') ?: null,
                'witness_report_id' => $request->integer('witness_report') ?: null,
                'operation_id' => $request->integer('operation') ?: null,
                'facts' => $request->string('facts')->toString() ?: null,
            ],
        ]);
    }

    public function store(OpenCaseRequest $request, ActionRunner $actions): RedirectResponse
    {
        $case = $actions->run(OpenInfractionCase::class, $request->user(), $request->validated());

        return to_route('infractions.show', $case)->with('status', __('infractions::infractions.opened'));
    }

    public function show(InfractionCase $case, DocumentStore $documents): View
    {
        $case->load([
            'vessel.currentLicense', 'captain', 'shipowner', 'inspection', 'mission', 'witnessReport', 'operation', 'opener',
            'upheld.type', 'evidence.document', 'seals', 'measures.proposer', 'measures.decider', 'seizures',
            'violationReport.document', 'followUps.decisions.payments', 'followUps.decisions.appeals',
            'suspensions.license', 'revocations.license', 'activitiesAsSubject.causer',
        ]);

        return view('infractions::show', [
            'case' => $case,
            'repeat' => RepeatOffense::for($case),
            'types' => InfractionType::active()->orderBy('code')->get()->mapWithKeys(fn (InfractionType $type) => [$type->id => "{$type->code} — {$type->label}"]),
            'decisionDocuments' => $case->followUps->flatMap->decisions->mapWithKeys(fn ($decision) => [$decision->id => $documents->for($decision)->first()]),
        ]);
    }

    public function update(UpdateCaseRequest $request, InfractionCase $case, ActionRunner $actions): RedirectResponse
    {
        $actions->run(UpdateInfractionCase::class, $request->user(), [...$request->validated(), 'infraction_case_id' => $case->id]);

        return to_route('infractions.show', $case)->with('status', __('infractions::infractions.updated'));
    }
}
