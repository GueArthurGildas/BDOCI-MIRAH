<?php

namespace Modules\Logbook\Actions;

use App\Models\Observation;
use App\Models\SurveillanceObservation;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Logbook\RecordDeletion;

class DeleteObservation extends Action
{
    public function __construct(private readonly RecordDeletion $deletion) {}

    public function code(): string
    {
        return 'logbook.delete_observation';
    }

    public function permission(): Permission
    {
        return Permission::LogbookDelete;
    }

    public function handle(ActionContext $context, array $input): Observation
    {
        $observation = Observation::with('logbookEntry.day')->findOrFail($input['observation_id']);
        $this->deletion->ensureDeletable($observation->logbookEntry);

        $this->deletion->cancel($observation->logbookEntry, $context->actor, $input['reason']);
        SurveillanceObservation::where('legacy_observation_id', $observation->id)->get()->each(fn (SurveillanceObservation $mirror) => $this->deletion->delete($mirror, $context->actor, $input['reason']));
        $this->deletion->delete($observation, $context->actor, $input['reason']);

        if ($observation->vessel_id) {
            $context->publish(
                vessel: $observation->vessel_id,
                module: 'logbook',
                type: 'observation.deleted',
                subject: $observation,
                summaryKey: 'logbook::events.deleted',
                params: ['id' => $observation->id],
            );
        }

        return $observation;
    }
}
