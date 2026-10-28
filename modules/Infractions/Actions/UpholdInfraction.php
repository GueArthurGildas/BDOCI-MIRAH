<?php

namespace Modules\Infractions\Actions;

use App\Enums\InfractionCaseStatus;
use App\Models\InfractionCase;
use App\Models\InfractionType;
use App\Models\UpheldInfraction;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Validation\ValidationException;
use Modules\Infractions\Support\CaseWorkflow;

class UpholdInfraction extends Action
{
    public function code(): string
    {
        return 'infractions.uphold';
    }

    public function permission(): Permission
    {
        return Permission::InfractionsManage;
    }

    public function handle(ActionContext $context, array $input): UpheldInfraction
    {
        $case = InfractionCase::lockForUpdate()->findOrFail($input['infraction_case_id']);
        $type = InfractionType::findOrFail($input['infraction_type_id']);

        if ($case->upheld()->where('infraction_type_id', $type->id)->exists()) {
            throw ValidationException::withMessages(['infraction_type_id' => __('infractions::infractions.errors.already_upheld')]);
        }

        $upheld = $case->upheld()->create(['infraction_type_id' => $type->id, 'upheld_at' => now()]);
        CaseWorkflow::advanceTo($case, InfractionCaseStatus::Instruction);

        $context->publish(
            vessel: $case->vessel_id,
            module: 'infractions',
            type: 'infraction_case.upheld',
            subject: $upheld,
            summaryKey: 'infractions::events.upheld',
            params: ['reference' => $case->reference, 'code' => $type->code],
        );

        return $upheld;
    }
}
