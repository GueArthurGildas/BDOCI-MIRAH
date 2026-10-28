<?php

namespace Modules\Vessels\Actions;

use App\Models\Vessel;
use App\Models\VesselActor;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class AddVesselActor extends Action
{
    public function code(): string
    {
        return 'vessels.add_actor';
    }

    public function permission(): Permission
    {
        return Permission::VesselsManage;
    }

    public function handle(ActionContext $context, array $input): VesselActor
    {
        $vessel = Vessel::findOrFail($input['vessel_id']);
        $started = $input['started_on'] ?? today();

        VesselActor::where('vessel_id', $vessel->id)->where('role', $input['role'])->whereNull('ended_on')->update(['ended_on' => $started]);

        $actor = VesselActor::create([
            'vessel_id' => $vessel->id,
            'role' => $input['role'],
            'legal_entity_id' => $input['legal_entity_id'] ?? null,
            'person_id' => $input['person_id'] ?? null,
            'started_on' => $started,
        ]);

        $actor->load(['legalEntity', 'person']);

        $context->publish($vessel, 'vessels', 'vessel.actor_added', $actor, 'vessels::events.actor_added', [
            'name' => $vessel->name, 'role' => $actor->role->value, 'holder' => $actor->holderName(),
        ]);

        return $actor;
    }
}
