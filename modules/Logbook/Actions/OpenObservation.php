<?php

namespace Modules\Logbook\Actions;

use App\Enums\ObservationStatus;
use App\Models\Observation;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class OpenObservation extends Action
{
    public function code(): string
    {
        return 'logbook.open';
    }

    public function permission(): Permission
    {
        return Permission::LogbookCreate;
    }

    public function handle(ActionContext $context, array $input): Observation
    {
        $observation = Observation::create([
            ...$input,
            'author_id' => $context->actor->id,
            'team_id' => $context->actor->team_id,
            'status' => ObservationStatus::Open,
        ]);

        if ($observation->vessel_id) {
            $context->publish(
                vessel: $observation->vessel_id,
                module: 'logbook',
                type: 'observation.opened',
                subject: $observation,
                summaryKey: 'logbook::events.opened',
                params: ['id' => $observation->id],
                zoneId: $observation->zone_id,
                occurredAt: $observation->observed_at,
            );
        }

        return $observation;
    }
}
