<?php

namespace Modules\Vessels\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Vessel;
use App\Support\Actions\ActionRunner;
use Illuminate\Http\RedirectResponse;
use Modules\Vessels\Actions\AddVesselActor;
use Modules\Vessels\Actions\AddVesselIdentifier;
use Modules\Vessels\Actions\ListVesselOnExternalList;
use Modules\Vessels\Http\Requests\AddVesselActorRequest;
use Modules\Vessels\Http\Requests\AddVesselIdentifierRequest;
use Modules\Vessels\Http\Requests\ListVesselRequest;

class VesselRegistryController extends Controller
{
    public function storeIdentifier(AddVesselIdentifierRequest $request, Vessel $vessel, ActionRunner $actions): RedirectResponse
    {
        $actions->run(AddVesselIdentifier::class, $request->user(), [...$request->validated(), 'vessel_id' => $vessel->id]);

        return to_route('vessels.show', $vessel)->with('status', __('vessels::vessels.identifiers.added'));
    }

    public function storeActor(AddVesselActorRequest $request, Vessel $vessel, ActionRunner $actions): RedirectResponse
    {
        $actions->run(AddVesselActor::class, $request->user(), [...$request->validated(), 'vessel_id' => $vessel->id]);

        return to_route('vessels.show', $vessel)->with('status', __('vessels::vessels.actors.added'));
    }

    public function storeList(ListVesselRequest $request, Vessel $vessel, ActionRunner $actions): RedirectResponse
    {
        $actions->run(ListVesselOnExternalList::class, $request->user(), [...$request->validated(), 'vessel_id' => $vessel->id]);

        return to_route('vessels.show', $vessel)->with('status', __('vessels::vessels.lists.added'));
    }
}
