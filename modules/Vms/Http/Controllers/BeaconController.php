<?php

namespace Modules\Vms\Http\Controllers;

use App\Enums\BeaconModem;
use App\Http\Controllers\Controller;
use App\Models\Beacon;
use App\Models\Vessel;
use App\Models\VesselReportingObligation;
use App\Support\Actions\ActionRunner;
use App\Support\Vms\BeaconCompliance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Vms\Actions\AssignBeacon;
use Modules\Vms\Actions\CreateBeacon;
use Modules\Vms\Actions\SetReportingObligations;
use Modules\Vms\Http\Requests\AssignBeaconRequest;
use Modules\Vms\Http\Requests\StoreBeaconRequest;

class BeaconController extends Controller
{
    public function index(BeaconCompliance $compliance): View
    {
        return view('vms::beacons.index', [
            'beacons' => Beacon::with(['currentAssignment.vessel'])->orderBy('serial_number')->get(),
            'missing' => $compliance->vesselsWithoutBeacon(),
            'obligations' => VesselReportingObligation::with('vessel')->get()->sortBy('vessel.name'),
            'vessels' => $this->vesselOptions(),
            'beaconOptions' => Beacon::orderBy('serial_number')->pluck('serial_number', 'id'),
            'modems' => BeaconModem::options(),
        ]);
    }

    public function store(StoreBeaconRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(CreateBeacon::class, $request->user(), $request->validated());

        return to_route('vms.beacons.index')->with('status', __('vms::vms.beacons.created'));
    }

    public function assign(AssignBeaconRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(AssignBeacon::class, $request->user(), $request->validated());

        return to_route('vms.beacons.index')->with('status', __('vms::vms.beacons.assigned'));
    }

    public function obligations(Request $request, ActionRunner $actions): RedirectResponse
    {
        $data = $request->validate([
            'vessel_id' => ['required', 'exists:vessels,id'],
            'requires_vms' => ['nullable', 'boolean'],
            'requires_ais' => ['nullable', 'boolean'],
            'requires_ers' => ['nullable', 'boolean'],
            'requires_eez_declaration' => ['nullable', 'boolean'],
            'requires_fishing_log' => ['nullable', 'boolean'],
        ]);

        $actions->run(SetReportingObligations::class, $request->user(), $data);

        return to_route('vms.beacons.index')->with('status', __('vms::vms.beacons.obligations_saved'));
    }

    private function vesselOptions()
    {
        return Vessel::orderBy('name')->get()->mapWithKeys(fn (Vessel $vessel) => [$vessel->id => "{$vessel->name} ({$vessel->registration_number})"]);
    }
}
