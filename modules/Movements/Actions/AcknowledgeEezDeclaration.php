<?php

namespace Modules\Movements\Actions;

use App\Enums\EezDeclarationStatus;
use App\Models\EezDeclaration;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class AcknowledgeEezDeclaration extends Action
{
    public function code(): string
    {
        return 'movements.acknowledge_eez_declaration';
    }

    public function permission(): Permission
    {
        return Permission::MovementsDeclare;
    }

    public function handle(ActionContext $context, array $input): EezDeclaration
    {
        $declaration = EezDeclaration::findOrFail($input['eez_declaration_id']);

        if ($declaration->status !== EezDeclarationStatus::Variance) {
            $declaration->update(['status' => EezDeclarationStatus::Acknowledged]);
        }

        if ($declaration->vessel_id !== null) {
            $context->publish(
                vessel: $declaration->vessel_id,
                module: 'movements',
                type: 'eez_declaration.acknowledged',
                subject: $declaration,
                summaryKey: 'movements::events.eez_declaration_acknowledged',
                params: ['direction' => $declaration->type->value],
            );
        }

        return $declaration;
    }
}
