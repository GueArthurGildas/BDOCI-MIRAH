<?php

namespace Modules\Vessels\Actions;

use App\Enums\IdentifierType;
use App\Models\Vessel;
use App\Models\VesselIdentifier;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Vessels\VesselRelationsSynchronizer;

class AddVesselIdentifier extends Action
{
    public function code(): string
    {
        return 'vessels.add_identifier';
    }

    public function permission(): Permission
    {
        return Permission::VesselsManage;
    }

    public function handle(ActionContext $context, array $input): VesselIdentifier
    {
        $vessel = Vessel::findOrFail($input['vessel_id']);
        $type = IdentifierType::from($input['type']);
        $column = array_search($type, VesselRelationsSynchronizer::IDENTIFIER_COLUMNS, true);

        if ($column !== false) {
            $vessel->update([$column => $input['value']]);
        } else {
            VesselIdentifier::where('vessel_id', $vessel->id)->where('type', $type->value)->whereNull('valid_to')
                ->where('type', '!=', IdentifierType::Other->value)->update(['valid_to' => today()]);
            VesselIdentifier::create(['vessel_id' => $vessel->id, 'type' => $type, 'value' => $input['value'], 'valid_from' => $input['valid_from'] ?? today()]);
        }

        $identifier = VesselIdentifier::where('vessel_id', $vessel->id)->where('type', $type->value)->where('value', $input['value'])->latest('id')->firstOrFail();

        $context->publish($vessel, 'vessels', 'vessel.identifier_added', $identifier, 'vessels::events.identifier_added', [
            'name' => $vessel->name, 'type' => $type->value, 'value' => $input['value'],
        ]);

        return $identifier;
    }
}
