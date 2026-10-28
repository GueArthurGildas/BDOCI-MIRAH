<?php

namespace Modules\Vms\Http\Controllers;

use App\Enums\AssessmentResult;
use App\Http\Controllers\Controller;
use App\Models\ApprovedInstaller;
use App\Models\Beacon;
use App\Models\NewBeaconRequest;
use App\Models\Vessel;
use App\Models\VmsAssessmentRequest;
use App\Support\Actions\ActionRunner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Vms\Actions\CreateApprovedInstaller;
use Modules\Vms\Actions\RecordBeaconInstallation;
use Modules\Vms\Actions\RecordCompatibilityAssessment;
use Modules\Vms\Actions\RequestNewBeacon;
use Modules\Vms\Actions\RequestVmsAssessment;

class RequestController extends Controller
{
    public function index(): View
    {
        return view('vms::requests.index', [
            'assessmentRequests' => VmsAssessmentRequest::with(['vessel', 'assessments'])->latest('requested_on')->latest('id')->get(),
            'newBeaconRequests' => NewBeaconRequest::with(['vessel', 'installations.installer.legalEntity'])->latest('requested_on')->latest('id')->get(),
            'installers' => ApprovedInstaller::with('legalEntity')->get(),
            'vessels' => Vessel::orderBy('name')->get()->mapWithKeys(fn (Vessel $vessel) => [$vessel->id => "{$vessel->name} ({$vessel->registration_number})"]),
            'results' => AssessmentResult::options(),
            'beaconOptions' => Beacon::orderBy('serial_number')->pluck('serial_number', 'id'),
            'installerOptions' => ApprovedInstaller::with('legalEntity')->get()->mapWithKeys(fn (ApprovedInstaller $installer) => [$installer->id => $installer->legalEntity->name]),
        ]);
    }

    public function requestAssessment(Request $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(RequestVmsAssessment::class, $request->user(), $request->validate(['vessel_id' => ['required', 'exists:vessels,id']]));

        return to_route('vms.requests.index')->with('status', __('vms::vms.requests.assessment_requested'));
    }

    public function assess(VmsAssessmentRequest $assessmentRequest, Request $request, ActionRunner $actions): RedirectResponse
    {
        $data = $request->validate(['result' => ['required', Rule::enum(AssessmentResult::class)]]);

        $actions->run(RecordCompatibilityAssessment::class, $request->user(), [...$data, 'assessment_request_id' => $assessmentRequest->id]);

        return to_route('vms.requests.index')->with('status', __('vms::vms.requests.assessed'));
    }

    public function requestNewBeacon(Request $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(RequestNewBeacon::class, $request->user(), $request->validate(['vessel_id' => ['required', 'exists:vessels,id']]));

        return to_route('vms.requests.index')->with('status', __('vms::vms.requests.new_beacon_requested'));
    }

    public function install(NewBeaconRequest $newBeaconRequest, Request $request, ActionRunner $actions): RedirectResponse
    {
        $data = $request->validate([
            'installer_id' => ['required', 'exists:approved_installers,id'],
            'beacon_id' => ['nullable', 'exists:beacons,id'],
            'installed_on' => ['nullable', 'date'],
        ]);

        $actions->run(RecordBeaconInstallation::class, $request->user(), [...$data, 'new_beacon_request_id' => $newBeaconRequest->id]);

        return to_route('vms.requests.index')->with('status', __('vms::vms.requests.installed'));
    }

    public function storeInstaller(Request $request, ActionRunner $actions): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'approved_until' => ['nullable', 'date']]);

        $actions->run(CreateApprovedInstaller::class, $request->user(), $data);

        return to_route('vms.requests.index')->with('status', __('vms::vms.requests.installer_created'));
    }
}
