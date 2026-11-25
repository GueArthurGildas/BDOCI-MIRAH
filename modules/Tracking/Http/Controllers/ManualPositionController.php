<?php

namespace Modules\Tracking\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ControlSlot;
use App\Models\Vessel;
use App\Models\Zone;
use App\Support\Actions\ActionRunner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Tracking\Actions\RecordManualPosition;
use Modules\Tracking\Http\Requests\RecordManualPositionRequest;

class ManualPositionController extends Controller
{
    public function create(Request $request): View
    {
        $slot = $request->integer('control_slot_id') ? ControlSlot::find($request->integer('control_slot_id')) : null;

        return view('tracking::positions.create', [
            'slot' => $slot,
            'selectedVesselId' => $request->integer('vessel_id') ?: null,
            'vessels' => Vessel::orderBy('name')->get()->mapWithKeys(fn (Vessel $vessel) => [$vessel->id => $vessel->optionLabel()]),
            'zones' => Zone::where('is_active', true)->orderBy('name')->pluck('name', 'id'),
            'activities' => collect(['moored', 'fishing', 'transit'])->mapWithKeys(fn (string $activity) => [$activity => __('tracking::tracking.manual.activities.'.$activity)]),
        ]);
    }

    public function store(RecordManualPositionRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(RecordManualPosition::class, $request->user(), $request->validated());

        $redirect = $request->filled('control_slot_id')
            ? to_route('tracking.slots.show', $request->integer('control_slot_id'))
            : to_route('tracking.zones');

        return $redirect->with('status', __('tracking::tracking.manual.recorded'));
    }
}
