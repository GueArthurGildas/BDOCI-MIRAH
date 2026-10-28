<?php

namespace Modules\Infractions\Actions;

use App\Models\Appeal;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class RecordAppealOutcome extends Action
{
    public function code(): string
    {
        return 'infractions.record_appeal_outcome';
    }

    public function permission(): Permission
    {
        return Permission::InfractionsDecide;
    }

    public function handle(ActionContext $context, array $input): Appeal
    {
        $appeal = Appeal::with('decision.followUp.infractionCase')->findOrFail($input['appeal_id']);
        $case = $appeal->decision->followUp->infractionCase;

        $appeal->update(['outcome' => $input['outcome']]);

        $context->publish(
            vessel: $case->vessel_id,
            module: 'infractions',
            type: 'infraction_case.appeal_decided',
            subject: $appeal,
            summaryKey: 'infractions::events.appeal_decided',
            params: ['reference' => $case->reference],
        );

        return $appeal;
    }
}
