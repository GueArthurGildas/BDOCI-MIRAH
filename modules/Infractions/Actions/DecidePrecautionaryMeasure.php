<?php

namespace Modules\Infractions\Actions;

use App\Models\PrecautionaryMeasure;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Validation\ValidationException;

class DecidePrecautionaryMeasure extends Action
{
    public function code(): string
    {
        return 'infractions.decide_measure';
    }

    public function permission(): Permission
    {
        return Permission::InfractionsDecideMeasures;
    }

    public function handle(ActionContext $context, array $input): PrecautionaryMeasure
    {
        $measure = PrecautionaryMeasure::lockForUpdate()->with('infractionCase')->findOrFail($input['measure_id']);

        if ($measure->isDecided()) {
            throw ValidationException::withMessages(['measure' => __('infractions::infractions.errors.measure_already_decided')]);
        }

        $measure->update(['decided_by' => $context->actor->id, 'decided_at' => now()]);

        $context->publish(
            vessel: $measure->infractionCase->vessel_id,
            module: 'infractions',
            type: 'infraction_case.measure_decided',
            subject: $measure,
            summaryKey: 'infractions::events.measure_decided.'.$measure->type->value,
            params: ['reference' => $measure->infractionCase->reference],
        );

        return $measure;
    }
}
