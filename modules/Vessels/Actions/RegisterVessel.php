<?php

namespace Modules\Vessels\Actions;

use App\Models\Vessel;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class RegisterVessel extends Action
{
    public function code(): string
    {
        return 'vessels.register';
    }

    public function permission(): Permission
    {
        return Permission::VesselsManage;
    }

    public function handle(ActionContext $context, array $input): Vessel
    {
        $vessel = Vessel::create($input);

        $context->publish(
            vessel: $vessel,
            module: 'vessels',
            type: 'vessel.registered',
            subject: $vessel,
            summaryKey: 'vessels::events.registered',
            params: ['name' => $vessel->name, 'registration_number' => $vessel->registration_number],
        );

        return $vessel;
    }
}
