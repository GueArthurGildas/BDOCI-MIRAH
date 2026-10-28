<?php

namespace Modules\Movements\Actions;

use App\Models\Movement;
use App\Models\Port;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class RecordMovement extends Action
{
    public function code(): string
    {
        return 'movements.record';
    }

    public function permission(): Permission
    {
        return Permission::MovementsCreate;
    }

    public function handle(ActionContext $context, array $input): Movement
    {
        $movement = Movement::create([...$input, 'recorded_by' => $context->actor->id]);

        $context->publish(
            vessel: $movement->vessel_id,
            module: 'movements',
            type: 'movement.recorded',
            subject: $movement,
            summaryKey: 'movements::events.recorded',
            params: ['direction' => $movement->direction->value, 'port' => Port::find($movement->port_id)?->name],
            occurredAt: $movement->occurred_at,
        );

        return $movement;
    }
}
