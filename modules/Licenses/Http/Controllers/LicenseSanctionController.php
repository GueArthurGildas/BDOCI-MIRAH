<?php

namespace Modules\Licenses\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\LicenseSuspension;
use App\Support\Actions\ActionRunner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Licenses\Actions\LiftLicenseSuspension;
use Modules\Licenses\Actions\RevokeLicense;
use Modules\Licenses\Actions\SuspendLicense;
use Modules\Licenses\Http\Requests\RevokeLicenseRequest;
use Modules\Licenses\Http\Requests\SuspendLicenseRequest;

class LicenseSanctionController extends Controller
{
    public function suspend(License $license, SuspendLicenseRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(SuspendLicense::class, $request->user(), [...$request->validated(), 'license_id' => $license->id]);

        return to_route('licenses.show', $license)->with('status', __('licenses::licenses.suspension.created'));
    }

    public function lift(LicenseSuspension $suspension, Request $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(LiftLicenseSuspension::class, $request->user(), ['suspension_id' => $suspension->id, 'lifted_on' => $request->input('lifted_on', today()->toDateString())]);

        return to_route('licenses.show', $suspension->license_id)->with('status', __('licenses::licenses.suspension.lifted'));
    }

    public function revoke(License $license, RevokeLicenseRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(RevokeLicense::class, $request->user(), [...$request->validated(), 'license_id' => $license->id]);

        return to_route('licenses.show', $license)->with('status', __('licenses::licenses.revocation.created'));
    }
}
