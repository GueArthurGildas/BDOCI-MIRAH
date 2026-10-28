<?php

namespace Modules\Infractions\Actions;

use App\Enums\FollowUpType;
use App\Enums\InfractionCaseStatus;
use App\Models\InfractionCase;
use App\Models\LitigationFollowUp;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Modules\Infractions\Support\CaseWorkflow;

class OpenLitigationFollowUp extends Action
{
    public function code(): string
    {
        return 'infractions.open_follow_up';
    }

    public function permission(): Permission
    {
        return Permission::InfractionsDecide;
    }

    public function handle(ActionContext $context, array $input): LitigationFollowUp
    {
        $case = InfractionCase::lockForUpdate()->findOrFail($input['infraction_case_id']);
        $type = FollowUpType::from($input['type']);

        $followUp = $case->followUps()->create(['type' => $type, 'opened_at' => now()]);
        CaseWorkflow::advanceTo($case, InfractionCaseStatus::Decision);

        $context->publish(
            vessel: $case->vessel_id,
            module: 'infractions',
            type: 'infraction_case.follow_up_opened',
            subject: $followUp,
            summaryKey: 'infractions::events.follow_up_opened.'.$type->value,
            params: ['reference' => $case->reference],
        );

        return $followUp;
    }
}
