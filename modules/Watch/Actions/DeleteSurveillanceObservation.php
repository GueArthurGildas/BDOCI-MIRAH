<?php

namespace Modules\Watch\Actions;

use App\Models\Observation;
use App\Models\SurveillanceObservation;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Logbook\RecordDeletion;

class DeleteSurveillanceObservation extends Action
{
    public function __construct(private readonly RecordDeletion $deletion) {}

    public function code(): string
    {
        return 'watch.delete_observation';
    }

    public function permission(): Permission
    {
        return Permission::WatchDelete;
    }

    public function handle(ActionContext $context, array $input): SurveillanceObservation
    {
        $observation = SurveillanceObservation::with('logbookEntry.day')->findOrFail($input['observation_id']);
        $this->deletion->ensureDeletable($observation->logbookEntry);

        $this->deletion->cancel($observation->logbookEntry, $context->actor, $input['reason']);

        if ($observation->legacy_observation_id) {
            Observation::whereKey($observation->legacy_observation_id)->get()->each(fn (Observation $legacy) => $this->deletion->delete($legacy, $context->actor, $input['reason']));
        }

        $this->deletion->delete($observation, $context->actor, $input['reason']);

        $context->publish(
            vessel: $observation->vessel_id,
            module: 'watch',
            type: 'observation.deleted',
            subject: $observation,
            summaryKey: 'watch::events.observation_deleted',
            params: ['id' => $observation->id],
        );

        return $observation;
    }
}
