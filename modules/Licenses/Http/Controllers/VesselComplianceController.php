<?php

namespace Modules\Licenses\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ExternalAuthorization;
use App\Models\Institution;
use App\Models\Vessel;
use App\Models\VmsCompatibilityCertificate;
use App\Support\Actions\ActionRunner;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Licenses\Actions\RecordExternalAuthorization;
use Modules\Licenses\Actions\RecordVmsCertificate;
use Modules\Licenses\Actions\SetFnpStatus;
use Modules\Licenses\Http\Requests\ExternalAuthorizationRequest;
use Modules\Licenses\Http\Requests\FnpStatusRequest;
use Modules\Licenses\Http\Requests\VmsCertificateRequest;

class VesselComplianceController extends Controller
{
    public function show(Vessel $vessel): View
    {
        return view('licenses::vessels.show', [
            'vessel' => $vessel->load(['fnpStatus', 'licenses']),
            'certificates' => VmsCompatibilityCertificate::where('vessel_id', $vessel->id)->latest('expires_on')->get(),
            'authorizations' => ExternalAuthorization::with('institution')->where('vessel_id', $vessel->id)->latest('valid_to')->get(),
            'institutions' => Institution::orderBy('name')->pluck('name', 'id')->all(),
        ]);
    }

    public function fnp(Vessel $vessel, FnpStatusRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(SetFnpStatus::class, $request->user(), [...$request->validated(), 'vessel_id' => $vessel->id]);

        return to_route('licenses.vessels.show', $vessel)->with('status', __('licenses::compliance.fnp.saved'));
    }

    public function certificate(Vessel $vessel, VmsCertificateRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(RecordVmsCertificate::class, $request->user(), [...$request->validated(), 'vessel_id' => $vessel->id]);

        return to_route('licenses.vessels.show', $vessel)->with('status', __('licenses::compliance.vms.saved'));
    }

    public function authorization(Vessel $vessel, ExternalAuthorizationRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(RecordExternalAuthorization::class, $request->user(), [...$request->validated(), 'vessel_id' => $vessel->id]);

        return to_route('licenses.vessels.show', $vessel)->with('status', __('licenses::compliance.external.saved'));
    }
}
