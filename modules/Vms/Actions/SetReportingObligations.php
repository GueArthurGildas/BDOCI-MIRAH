<?php

namespace Modules\Vms\Actions;

use App\Models\VesselReportingObligation;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class SetReportingObligations extends Action
{
    public function code(): string
    {
        return 'vms.set_obligations';
    }

    public function permission(): Permission
    {
        return Permission::VmsManage;
    }

    public function handle(ActionContext $context, array $input): VesselReportingObligation
    {
        $obligation = VesselReportingObligation::updateOrCreate(
            ['vessel_id' => $input['vessel_id']],
            collect(['requires_vms', 'requires_ais', 'requires_ers', 'requires_eez_declaration', 'requires_fishing_log'])
                ->mapWithKeys(fn (string $field) => [$field => (bool) ($input[$field] ?? false)])
                ->all(),
        );

        $context->publish(
            vessel: $obligation->vessel_id,
            module: 'vms',
            type: 'obligations.set',
            subject: $obligation,
            summaryKey: 'vms::events.obligations_set',
        );

        return $obligation;
    }
}
