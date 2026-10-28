<?php

namespace Modules\Licenses\Actions;

use App\Models\License;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Arr;

class RecordLicense extends Action
{
    public function code(): string
    {
        return 'licenses.record';
    }

    public function permission(): Permission
    {
        return Permission::LicensesManage;
    }

    public function handle(ActionContext $context, array $input): License
    {
        $license = License::create(Arr::only($input, ['vessel_id', 'number', 'type', 'valid_from', 'valid_until']));

        $context->publish(
            vessel: $license->vessel_id,
            module: 'licenses',
            type: 'license.recorded',
            subject: $license,
            summaryKey: 'licenses::events.recorded',
            params: ['number' => $license->number, 'valid_until' => $license->valid_until->toDateString()],
        );

        return $license;
    }
}
