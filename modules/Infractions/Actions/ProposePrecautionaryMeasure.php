<?php

namespace Modules\Infractions\Actions;

use App\Enums\InfractionCaseStatus;
use App\Enums\PrecautionaryMeasureType;
use App\Models\InfractionCase;
use App\Models\PrecautionaryMeasure;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Modules\Infractions\Support\CaseWorkflow;

class ProposePrecautionaryMeasure extends Action
{
    public function code(): string
    {
        return 'infractions.propose_measure';
    }

    public function permission(): Permission
    {
        return Permission::InfractionsManage;
    }

    public function handle(ActionContext $context, array $input): PrecautionaryMeasure
    {
        $case = InfractionCase::lockForUpdate()->findOrFail($input['infraction_case_id']);
        $type = PrecautionaryMeasureType::from($input['type']);

        $measure = $case->measures()->create(['type' => $type, 'proposed_by' => $context->actor->id]);
        CaseWorkflow::advanceTo($case, InfractionCaseStatus::Instruction);

        $context->publish(
            vessel: $case->vessel_id,
            module: 'infractions',
            type: 'infraction_case.measure_proposed',
            subject: $measure,
            summaryKey: 'infractions::events.measure_proposed.'.$type->value,
            params: ['reference' => $case->reference],
        );

        return $measure;
    }
}
