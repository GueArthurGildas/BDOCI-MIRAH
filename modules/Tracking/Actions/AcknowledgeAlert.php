<?php

namespace Modules\Tracking\Actions;

use App\Enums\AlertStatus;
use App\Models\Alert;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class AcknowledgeAlert extends Action
{
    public function code(): string
    {
        return 'tracking.acknowledge_alert';
    }

    public function permission(): Permission
    {
        return Permission::TrackingCreate;
    }

    public function handle(ActionContext $context, array $input): Alert
    {
        $alert = Alert::findOrFail($input['alert_id']);

        if ($alert->status === AlertStatus::Open) {
            $alert->update(['status' => AlertStatus::Acknowledged, 'acknowledged_by' => $context->actor->id]);

            $context->publish(
                vessel: $alert->vessel_id,
                module: 'tracking',
                type: 'alert.acknowledged',
                subject: $alert,
                summaryKey: 'tracking::events.alert_acknowledged',
                params: ['type' => $alert->type->value],
            );
        }

        return $alert;
    }
}
