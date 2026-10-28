<?php

namespace Modules\Infractions\Actions;

use App\Enums\InfractionQualification;
use App\Models\InfractionCase;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Arr;

class UpdateInfractionCase extends Action
{
    public function code(): string
    {
        return 'infractions.update';
    }

    public function permission(): Permission
    {
        return Permission::InfractionsManage;
    }

    public function handle(ActionContext $context, array $input): InfractionCase
    {
        $case = InfractionCase::findOrFail($input['infraction_case_id']);

        $case->update([
            ...Arr::only($input, ['facts', 'captain_observations', 'captain_id', 'shipowner_id']),
            ...(isset($input['qualification']) ? ['qualification' => InfractionQualification::from($input['qualification'])] : []),
        ]);

        $context->publish(
            vessel: $case->vessel_id,
            module: 'infractions',
            type: 'infraction.updated',
            subject: $case,
            summaryKey: 'infractions::events.updated',
            params: ['reference' => $case->reference],
        );

        return $case;
    }
}
