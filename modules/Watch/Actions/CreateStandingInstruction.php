<?php

namespace Modules\Watch\Actions;

use App\Models\StandingInstruction;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Arr;

class CreateStandingInstruction extends Action
{
    public function code(): string
    {
        return 'watch.create_standing_instruction';
    }

    public function permission(): Permission
    {
        return Permission::WatchManage;
    }

    public function handle(ActionContext $context, array $input): StandingInstruction
    {
        $instruction = StandingInstruction::create([
            ...Arr::only($input, ['vessel_id', 'body', 'ended_on']),
            'started_on' => $input['started_on'] ?? today(),
        ]);

        if ($instruction->vessel_id) {
            $context->publish(
                vessel: $instruction->vessel_id,
                module: 'watch',
                type: 'standing_instruction.created',
                subject: $instruction,
                summaryKey: 'watch::events.standing_instruction_created',
            );
        }

        return $instruction;
    }
}
