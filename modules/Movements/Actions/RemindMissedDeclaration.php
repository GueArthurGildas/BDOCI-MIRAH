<?php

namespace Modules\Movements\Actions;

use App\Models\MissedDeclaration;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class RemindMissedDeclaration extends Action
{
    public function code(): string
    {
        return 'movements.remind_missed_declaration';
    }

    public function permission(): Permission
    {
        return Permission::MovementsDeclare;
    }

    public function handle(ActionContext $context, array $input): MissedDeclaration
    {
        $missed = MissedDeclaration::findOrFail($input['missed_declaration_id']);
        $missed->increment('reminders');

        $context->publish(
            vessel: $missed->vessel_id,
            module: 'movements',
            type: 'missed_declaration.reminded',
            subject: $missed,
            summaryKey: 'movements::events.missed_reminded',
            params: ['reminders' => $missed->reminders],
        );

        return $missed;
    }
}
