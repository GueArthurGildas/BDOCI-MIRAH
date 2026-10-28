<?php

namespace Modules\Vms\Http\Controllers;

use App\Enums\VmsFailureCategory;
use App\Http\Controllers\Controller;
use App\Models\Operation;
use App\Models\Vessel;
use App\Models\VmsCutoffAuthorization;
use App\Models\VmsFailure;
use App\Support\Actions\ActionRunner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Vms\Actions\AuthorizeEmissionCutoff;
use Modules\Vms\Actions\DeclareVmsFailure;
use Modules\Vms\Actions\IssueCessationOrder;
use Modules\Vms\Actions\RecordManualPositionReport;
use Modules\Vms\Actions\RecordWorkingOrderAttestation;

class FailureController extends Controller
{
    public function index(): View
    {
        return view('vms::failures.index', [
            'failures' => VmsFailure::with(['vessel', 'cessationOrder', 'attestation'])->latest('started_at')->get(),
            'cutoffs' => VmsCutoffAuthorization::with('vessel')->latest('started_at')->limit(20)->get(),
            'vessels' => $this->vesselOptions(),
            'categories' => VmsFailureCategory::options(),
            'operations' => Operation::orderByDesc('started_at')->limit(50)->pluck('code', 'id'),
        ]);
    }

    public function show(VmsFailure $failure): View
    {
        return view('vms::failures.show', [
            'failure' => $failure->load(['vessel', 'operation', 'manualReports.enteredBy', 'attestation', 'cessationOrder.giver']),
        ]);
    }

    public function store(Request $request, ActionRunner $actions): RedirectResponse
    {
        $data = $request->validate([
            'vessel_id' => ['required', 'exists:vessels,id'],
            'category' => ['required', Rule::enum(VmsFailureCategory::class)],
            'started_at' => ['nullable', 'date'],
            'operation_id' => ['nullable', 'exists:operations,id'],
        ]);

        $failure = $actions->run(DeclareVmsFailure::class, $request->user(), $data);

        return to_route('vms.failures.show', $failure)->with('status', __('vms::vms.failures.declared'));
    }

    public function report(VmsFailure $failure, Request $request, ActionRunner $actions): RedirectResponse
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'received_at' => ['nullable', 'date'],
        ]);

        $actions->run(RecordManualPositionReport::class, $request->user(), [...$data, 'vms_failure_id' => $failure->id]);

        return to_route('vms.failures.show', $failure)->with('status', __('vms::vms.failures.report_recorded'));
    }

    public function attest(VmsFailure $failure, Request $request, ActionRunner $actions): RedirectResponse
    {
        $data = $request->validate(['attested_on' => ['nullable', 'date']]);

        $actions->run(RecordWorkingOrderAttestation::class, $request->user(), [...$data, 'vms_failure_id' => $failure->id]);

        return to_route('vms.failures.show', $failure)->with('status', __('vms::vms.failures.attested'));
    }

    public function order(VmsFailure $failure, Request $request, ActionRunner $actions): RedirectResponse
    {
        $data = $request->validate(['includes_suspension' => ['nullable', 'boolean'], 'requires_port_return' => ['nullable', 'boolean']]);

        $actions->run(IssueCessationOrder::class, $request->user(), [...$data, 'vms_failure_id' => $failure->id]);

        return to_route('vms.failures.show', $failure)->with('status', __('vms::vms.failures.ordered'));
    }

    public function authorizeCutoff(Request $request, ActionRunner $actions): RedirectResponse
    {
        $data = $request->validate([
            'vessel_id' => ['required', 'exists:vessels,id'],
            'started_at' => ['required', 'date'],
            'ended_at' => ['required', 'date', 'after:started_at'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $actions->run(AuthorizeEmissionCutoff::class, $request->user(), $data);

        return to_route('vms.failures.index')->with('status', __('vms::vms.failures.cutoff_authorized'));
    }

    private function vesselOptions()
    {
        return Vessel::orderBy('name')->get()->mapWithKeys(fn (Vessel $vessel) => [$vessel->id => "{$vessel->name} ({$vessel->registration_number})"]);
    }
}
