<?php

namespace Modules\Vms\Actions;

use App\Models\CessationOrder;
use App\Models\VmsFailure;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class IssueCessationOrder extends Action
{
    public function code(): string
    {
        return 'vms.issue_cessation_order';
    }

    public function permission(): Permission
    {
        return Permission::VmsManage;
    }

    public function handle(ActionContext $context, array $input): CessationOrder
    {
        $failure = VmsFailure::findOrFail($input['vms_failure_id']);

        $order = CessationOrder::updateOrCreate(['vms_failure_id' => $failure->id], [
            'ordered_at' => now(),
            'includes_suspension' => (bool) ($input['includes_suspension'] ?? false),
            'requires_port_return' => (bool) ($input['requires_port_return'] ?? false),
            'given_by' => $context->actor->id,
        ]);

        $context->publish(vessel: $failure->vessel_id, module: 'vms', type: 'failure.cessation_ordered', subject: $order, summaryKey: 'vms::events.cessation_ordered');

        return $order;
    }
}
