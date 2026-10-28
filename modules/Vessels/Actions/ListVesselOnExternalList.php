<?php

namespace Modules\Vessels\Actions;

use App\Models\ExternalList;
use App\Models\Vessel;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class ListVesselOnExternalList extends Action
{
    public function code(): string
    {
        return 'vessels.list_on_external_list';
    }

    public function permission(): Permission
    {
        return Permission::VesselsManage;
    }

    public function handle(ActionContext $context, array $input): Vessel
    {
        $vessel = Vessel::findOrFail($input['vessel_id']);
        $list = ExternalList::findOrFail($input['external_list_id']);

        $vessel->externalLists()->syncWithoutDetaching([
            $list->id => ['listed_from' => $input['listed_from'] ?? today(), 'listed_until' => $input['listed_until'] ?? null],
        ]);

        $context->publish($vessel, 'vessels', 'vessel.listed', $list, 'vessels::events.listed', [
            'name' => $vessel->name, 'list' => $list->label,
        ]);

        return $vessel;
    }
}
