<?php

namespace Modules\Movements\Actions;

use App\Models\MissedDeclaration;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class QualifyMissedDeclaration extends Action
{
    public function code(): string
    {
        return 'movements.qualify_missed_declaration';
    }

    public function permission(): Permission
    {
        return Permission::MovementsDeclare;
    }

    public function handle(ActionContext $context, array $input): MissedDeclaration
    {
        $missed = MissedDeclaration::findOrFail($input['missed_declaration_id']);
        $missed->update(['qualification' => $input['qualification']]);

        $context->publish(
            vessel: $missed->vessel_id,
            module: 'movements',
            type: 'missed_declaration.qualified',
            subject: $missed,
            summaryKey: 'movements::events.missed_qualified',
            params: ['qualification' => $missed->qualification],
        );

        return $missed;
    }
}
