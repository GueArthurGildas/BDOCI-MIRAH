<?php

namespace Modules\Movements\Actions;

use App\Enums\EezDeclarationStatus;
use App\Models\EezDeclaration;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class CloseEezDeclaration extends Action
{
    public function code(): string
    {
        return 'movements.close_eez_declaration';
    }

    public function permission(): Permission
    {
        return Permission::MovementsDeclare;
    }

    public function handle(ActionContext $context, array $input): EezDeclaration
    {
        $declaration = EezDeclaration::findOrFail($input['eez_declaration_id']);
        $declaration->update(['status' => EezDeclarationStatus::Closed]);

        if ($declaration->vessel_id !== null) {
            $context->publish(
                vessel: $declaration->vessel_id,
                module: 'movements',
                type: 'eez_declaration.closed',
                subject: $declaration,
                summaryKey: 'movements::events.eez_declaration_closed',
                params: ['direction' => $declaration->type->value],
            );
        }

        return $declaration;
    }
}
