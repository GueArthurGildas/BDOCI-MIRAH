<?php

namespace Modules\Movements\Actions;

use App\Enums\EntryExitAlertStatus;
use App\Models\EntryExitAlert;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class CloseEntryExitAlert extends Action
{
    public function code(): string
    {
        return 'movements.close_entry_exit_alert';
    }

    public function permission(): Permission
    {
        return Permission::MovementsDeclare;
    }

    public function handle(ActionContext $context, array $input): EntryExitAlert
    {
        $alert = EntryExitAlert::findOrFail($input['entry_exit_alert_id']);
        $alert->update(['status' => EntryExitAlertStatus::Closed]);

        $context->publish(
            vessel: $alert->vessel_id,
            module: 'movements',
            type: 'entry_exit_alert.closed',
            subject: $alert,
            summaryKey: 'movements::events.alert_closed',
            params: ['direction' => $alert->direction->value],
        );

        return $alert;
    }
}
