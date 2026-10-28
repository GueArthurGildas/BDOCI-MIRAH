<?php

namespace Modules\Licenses\Actions;

use App\Enums\FnpRegistration;
use App\Models\FnpStatus;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class SetFnpStatus extends Action
{
    public function code(): string
    {
        return 'licenses.set_fnp_status';
    }

    public function permission(): Permission
    {
        return Permission::LicensesManage;
    }

    public function handle(ActionContext $context, array $input): FnpStatus
    {
        $status = FnpRegistration::from($input['status']);

        $fnp = FnpStatus::updateOrCreate(['vessel_id' => $input['vessel_id']], [
            'status' => $status,
            'deregistration_reason' => $status === FnpRegistration::Deregistered ? $input['deregistration_reason'] : null,
            'status_on' => $input['status_on'] ?? today(),
        ]);

        $context->publish(
            vessel: $fnp->vessel_id,
            module: 'licenses',
            type: 'fnp.status_set',
            subject: $fnp,
            summaryKey: 'licenses::events.fnp.'.$status->value,
            params: ['status_on' => $fnp->status_on->toDateString()],
        );

        return $fnp;
    }
}
