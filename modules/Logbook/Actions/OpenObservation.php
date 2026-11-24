<?php

namespace Modules\Logbook\Actions;

use App\Enums\ObservationStatus;
use App\Models\Observation;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Logbook\LogbookChannels;
use App\Support\Logbook\LogbookWriter;
use App\Support\Logbook\ObservationMirror;

class OpenObservation extends Action
{
    public function __construct(
        private readonly ObservationMirror $mirror,
        private readonly LogbookWriter $logbook,
        private readonly LogbookChannels $channels,
    ) {}

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
        $entry = $this->logbook->write($context, [
            'channel_id' => $this->channels->idFor(LogbookChannels::INTERNAL),
            'content' => $input['description'],
            'vessel_ids' => array_filter([$input['vessel_id'] ?? null]),
            'publishes_vessel_event' => false,
        ]);

        $observation = Observation::create([
            ...$input,
            'author_id' => $context->actor->id,
            'team_id' => $context->actor->team_id,
            'status' => ObservationStatus::Open,
            'logbook_entry_id' => $entry->id,
        ]);

        $this->mirror->sync($observation);

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
