<?php

namespace Modules\Infractions\Actions;

use App\Enums\InfractionCaseStatus;
use App\Models\InfractionCase;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Validation\ValidationException;

class CloseInfractionCase extends Action
{
    public function code(): string
    {
        return 'infractions.close';
    }

    public function permission(): Permission
    {
        return Permission::InfractionsDecide;
    }

    public function handle(ActionContext $context, array $input): InfractionCase
    {
        $case = InfractionCase::lockForUpdate()->findOrFail($input['infraction_case_id']);

        if ($case->isClosed()) {
            throw ValidationException::withMessages(['case' => __('infractions::infractions.errors.already_closed')]);
        }

        $case->followUps()->whereNull('closed_at')->update(['closed_at' => now()]);
        $case->update(['status' => InfractionCaseStatus::Closed]);

        $context->publish(
            vessel: $case->vessel_id,
            module: 'infractions',
            type: 'infraction_case.closed',
            subject: $case,
            summaryKey: 'infractions::events.closed',
            params: ['reference' => $case->reference],
        );

        return $case;
    }
}
