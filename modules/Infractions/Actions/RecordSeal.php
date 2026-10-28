<?php

namespace Modules\Infractions\Actions;

use App\Models\InfractionCase;
use App\Models\Seal;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Carbon;

class RecordSeal extends Action
{
    public function code(): string
    {
        return 'infractions.record_seal';
    }

    public function permission(): Permission
    {
        return Permission::InfractionsManage;
    }

    public function handle(ActionContext $context, array $input): Seal
    {
        $case = InfractionCase::findOrFail($input['infraction_case_id']);

        $seal = $case->seals()->create([
            'number' => $input['number'],
            'placed_at' => filled($input['placed_at'] ?? null) ? Carbon::parse($input['placed_at']) : now(),
        ]);

        $context->publish(
            vessel: $case->vessel_id,
            module: 'infractions',
            type: 'infraction_case.seal_placed',
            subject: $seal,
            summaryKey: 'infractions::events.seal_placed',
            params: ['reference' => $case->reference, 'number' => $seal->number],
        );

        return $seal;
    }
}
