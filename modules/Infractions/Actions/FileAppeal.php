<?php

namespace Modules\Infractions\Actions;

use App\Models\Appeal;
use App\Models\Decision;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Carbon;

class FileAppeal extends Action
{
    public function code(): string
    {
        return 'infractions.file_appeal';
    }

    public function permission(): Permission
    {
        return Permission::InfractionsDecide;
    }

    public function handle(ActionContext $context, array $input): Appeal
    {
        $decision = Decision::with('followUp.infractionCase')->findOrFail($input['decision_id']);
        $case = $decision->followUp->infractionCase;

        $appeal = $decision->appeals()->create([
            'filed_on' => Carbon::parse($input['filed_on']),
            'outcome' => $input['outcome'] ?? null,
        ]);

        $context->publish(
            vessel: $case->vessel_id,
            module: 'infractions',
            type: 'infraction_case.appeal_filed',
            subject: $appeal,
            summaryKey: 'infractions::events.appeal_filed',
            params: ['reference' => $case->reference],
        );

        return $appeal;
    }
}
