<?php

namespace Modules\Logbook\Actions;

use App\Enums\ObservationStatus;
use App\Models\Observation;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Logbook\ObservationMirror;

class UpdateObservation extends Action
{
    public function __construct(private readonly ObservationMirror $mirror) {}

    public function code(): string
    {
        return 'logbook.update';
    }

    public function permission(): Permission
    {
        return Permission::LogbookManage;
    }

    public function handle(ActionContext $context, array $input): Observation
    {
        $observation = Observation::findOrFail($input['observation_id']);
        $status = ObservationStatus::from($input['status']);

        $observation->update([
            'status' => $status,
            'follow_up' => $input['follow_up'] ?? null,
            'closed_at' => $status === ObservationStatus::Closed ? ($observation->closed_at ?? now()) : null,
        ]);

        $this->mirror->sync($observation);

        if ($observation->vessel_id) {
            $context->publish(
                vessel: $observation->vessel_id,
                module: 'logbook',
                type: 'observation.updated',
                subject: $observation,
                summaryKey: 'logbook::events.updated',
                params: ['id' => $observation->id, 'status' => $status->value],
                zoneId: $observation->zone_id,
            );
        }

        return $observation;
    }
}
