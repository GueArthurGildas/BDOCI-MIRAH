<?php

namespace Modules\Watch\Actions;

use App\Enums\SurveillanceObservationStatus;
use App\Models\SurveillanceObservation;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class SetSurveillanceObservationStatus extends Action
{
    public function code(): string
    {
        return 'watch.set_observation_status';
    }

    public function permission(): Permission
    {
        return Permission::WatchCreate;
    }

    public function handle(ActionContext $context, array $input): SurveillanceObservation
    {
        $observation = SurveillanceObservation::findOrFail($input['observation_id']);
        $status = SurveillanceObservationStatus::from($input['status']);

        $observation->update(['status' => $status]);

        $context->publish(
            vessel: $observation->vessel_id,
            module: 'watch',
            type: 'observation.status_changed',
            subject: $observation,
            summaryKey: 'watch::events.observation_'.$status->value,
            params: ['id' => $observation->id],
        );

        return $observation;
    }
}
