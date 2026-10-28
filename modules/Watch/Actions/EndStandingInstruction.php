<?php

namespace Modules\Watch\Actions;

use App\Models\StandingInstruction;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class EndStandingInstruction extends Action
{
    public function code(): string
    {
        return 'watch.end_standing_instruction';
    }

    public function permission(): Permission
    {
        return Permission::WatchManage;
    }

    public function handle(ActionContext $context, array $input): StandingInstruction
    {
        $instruction = StandingInstruction::findOrFail($input['instruction_id']);

        $instruction->update(['ended_on' => today()]);

        if ($instruction->vessel_id) {
            $context->publish(
                vessel: $instruction->vessel_id,
                module: 'watch',
                type: 'standing_instruction.ended',
                subject: $instruction,
                summaryKey: 'watch::events.standing_instruction_ended',
            );
        }

        return $instruction;
    }
}
