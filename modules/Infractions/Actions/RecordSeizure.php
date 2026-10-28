<?php

namespace Modules\Infractions\Actions;

use App\Models\InfractionCase;
use App\Models\Seizure;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class RecordSeizure extends Action
{
    public function code(): string
    {
        return 'infractions.record_seizure';
    }

    public function permission(): Permission
    {
        return Permission::InfractionsManage;
    }

    public function handle(ActionContext $context, array $input): Seizure
    {
        $case = InfractionCase::findOrFail($input['infraction_case_id']);

        $seizure = $case->seizures()->create(['item' => $input['item'], 'decision' => $input['decision'] ?? null]);

        $context->publish(
            vessel: $case->vessel_id,
            module: 'infractions',
            type: 'infraction_case.seizure_recorded',
            subject: $seizure,
            summaryKey: 'infractions::events.seizure_recorded',
            params: ['reference' => $case->reference, 'item' => $seizure->item],
        );

        return $seizure;
    }
}
