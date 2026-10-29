<?php

namespace Modules\Witnesses\Http\Controllers;

use App\Enums\InvestigationElement;
use App\Enums\ReportChannel;
use App\Enums\ReportDecisionType;
use App\Enums\WitnessCategory;
use App\Enums\WitnessReportStatus;
use App\Http\Controllers\Controller;
use App\Models\Operation;
use App\Models\Witness;
use App\Models\WitnessReport;
use App\Support\Actions\ActionRunner;
use App\Support\Tables\PerPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Witnesses\Actions\DecideWitnessReport;
use Modules\Witnesses\Actions\LinkReportOperation;
use Modules\Witnesses\Actions\RecordWitnessReport;
use Modules\Witnesses\Actions\SaveReportInvestigation;
use Modules\Witnesses\Actions\SaveReportUsability;
use Modules\Witnesses\Http\Requests\DecisionRequest;
use Modules\Witnesses\Http\Requests\InvestigationRequest;
use Modules\Witnesses\Http\Requests\OperationLinkRequest;
use Modules\Witnesses\Http\Requests\RecordReportRequest;
use Modules\Witnesses\Http\Requests\UsabilityRequest;

class WitnessReportController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(WitnessReportStatus::class)],
            'channel' => ['nullable', Rule::enum(ReportChannel::class)],
        ]);

        $reports = WitnessReport::query()
            ->with(['witness', 'reportedVessels'])
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['channel'] ?? null, fn (Builder $query, string $channel) => $query->where('channel', $channel))
            ->latest('received_at')
            ->paginate(PerPage::from($request, self::PER_PAGE))
            ->withQueryString();

        return view('witnesses::index', compact('reports', 'filters'));
    }

    public function create(): View
    {
        return view('witnesses::create', [
            'witnesses' => Witness::orderBy('name')->pluck('name', 'id'),
            'channels' => ReportChannel::options(),
            'categories' => WitnessCategory::options(),
        ]);
    }

    public function store(RecordReportRequest $request, ActionRunner $actions): RedirectResponse
    {
        $report = $actions->run(RecordWitnessReport::class, $request->user(), $request->validated());

        return to_route('witnesses.show', $report)->with('status', __('witnesses::witnesses.messages.recorded'));
    }

    public function show(WitnessReport $report): View
    {
        $report->load(['witness', 'enteredBy', 'operation', 'reportedVessels.vessel', 'usability', 'investigation', 'decisions.decider', 'infractionCases', 'activitiesAsSubject.causer']);

        return view('witnesses::show', [
            'report' => $report,
            'elements' => InvestigationElement::options(),
            'decisions' => ReportDecisionType::options(),
            'innOperations' => Operation::whereHas('category', fn (Builder $query) => $query->where('code', 'inn'))->latest('started_at')->limit(100)->pluck('code', 'id'),
        ]);
    }

    public function usability(WitnessReport $report, UsabilityRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(SaveReportUsability::class, $request->user(), [...$request->validated(), 'witness_report_id' => $report->id]);

        return $this->back($report, 'usability');
    }

    public function investigation(WitnessReport $report, InvestigationRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(SaveReportInvestigation::class, $request->user(), [...$request->validated(), 'witness_report_id' => $report->id]);

        return $this->back($report, 'investigation');
    }

    public function operation(WitnessReport $report, OperationLinkRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(LinkReportOperation::class, $request->user(), [...$request->validated(), 'witness_report_id' => $report->id]);

        return $this->back($report, 'operation');
    }

    public function decide(WitnessReport $report, DecisionRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(DecideWitnessReport::class, $request->user(), [...$request->validated(), 'witness_report_id' => $report->id]);

        return $this->back($report, 'decided');
    }

    private function back(WitnessReport $report, string $key): RedirectResponse
    {
        return to_route('witnesses.show', $report)->with('status', __("witnesses::witnesses.messages.{$key}"));
    }
}
