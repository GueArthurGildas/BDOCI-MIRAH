<?php

namespace Modules\Infractions\Actions;

use App\Models\Infraction;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class UpdateInfraction extends Action
{
    public function code(): string
    {
        return 'infractions.update';
    }

    public function permission(): Permission
    {
        return Permission::InfractionsManage;
    }

    public function handle(ActionContext $context, array $input): Infraction
    {
        $infraction = Infraction::findOrFail($input['infraction_id']);
        $infraction->update($input['attributes']);

        $context->publish(
            vessel: $infraction->vessel_id,
            module: 'infractions',
            type: 'infraction.updated',
            subject: $infraction,
            summaryKey: 'infractions::events.updated',
            params: ['id' => $infraction->id, 'status' => $infraction->status->value],
        );

        return $infraction;
    }
}
