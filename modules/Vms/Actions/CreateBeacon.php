<?php

namespace Modules\Vms\Actions;

use App\Models\Beacon;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class CreateBeacon extends Action
{
    public function code(): string
    {
        return 'vms.create_beacon';
    }

    public function permission(): Permission
    {
        return Permission::VmsManage;
    }

    public function handle(ActionContext $context, array $input): Beacon
    {
        return Beacon::create(collect($input)->only(['serial_number', 'model', 'manufacturer', 'supplier', 'modem', 'subscription'])->all());
    }
}
