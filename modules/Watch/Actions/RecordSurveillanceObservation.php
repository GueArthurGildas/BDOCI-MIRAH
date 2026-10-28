<?php

namespace Modules\Watch\Actions;

use App\Enums\SurveillanceObservationStatus;
use App\Models\CheckCounter;
use App\Models\Relay;
use App\Models\Rotation;
use App\Models\ShiftHandover;
use App\Models\SurveillanceObservation;
use App\Models\Team;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Logbook\LogbookCalendar;
use App\Support\Logbook\LogbookChannels;
use App\Support\Logbook\LogbookWriter;
use Illuminate\Validation\ValidationException;
use Modules\Watch\Notifications\RelayAlert;

class RecordSurveillanceObservation extends Action
{
    public function __construct(
        private readonly LogbookWriter $writer,
        private readonly LogbookChannels $channels,
        private readonly LogbookCalendar $calendar,
    ) {}

    public function code(): string
    {
        return 'watch.record_observation';
    }

    public function permission(): Permission
    {
        return Permission::WatchCreate;
    }

    public function handle(ActionContext $context, array $input): SurveillanceObservation
    {
        $nextTeam = isset($input['relay_to_team_id']) ? Team::findOrFail($input['relay_to_team_id']) : null;

        if ($nextTeam && $context->actor->team_id === null) {
            throw ValidationException::withMessages(['relay_to_team_id' => __('watch::watch.errors.no_team')]);
        }

        $entry = $this->writer->write($context, [
            'channel_id' => $this->channels->idFor(LogbookChannels::INTERNAL),
            'content' => $input['body'],
            'vessel_ids' => [$input['vessel_id']],
        ]);

        $observation = SurveillanceObservation::create([
            'vessel_id' => $input['vessel_id'],
            'slot_position_id' => $input['slot_position_id'] ?? null,
            'logbook_entry_id' => $entry->id,
            'body' => $input['body'],
            'status' => SurveillanceObservationStatus::Monitoring,
            'created_by' => $context->actor->id,
            'created_at' => now(),
        ]);

        $this->countCheck($observation->vessel_id);

        if ($nextTeam) {
            $this->relay($context, $observation, $nextTeam);
        }

        $context->publish(
            vessel: $observation->vessel_id,
            module: 'watch',
            type: 'observation.recorded',
            subject: $observation,
            summaryKey: 'watch::events.observation_recorded',
            params: ['id' => $observation->id],
        );

        return $observation;
    }

    private function countCheck(int $vesselId): void
    {
        $counter = CheckCounter::firstOrCreate(['vessel_id' => $vesselId], ['checks_count' => 0]);
        $count = $counter->checks_count + 1;

        $counter->update([
            'checks_count' => $count,
            'last_checked_at' => now(),
            'is_alert_raised' => $counter->is_alert_raised || $count > config('surveillance.watch.check_threshold'),
        ]);
    }

    private function relay(ActionContext $context, SurveillanceObservation $observation, Team $nextTeam): void
    {
        $day = $this->calendar->today()->day;

        $rotation = Rotation::firstOrCreate(['team_id' => $context->actor->team_id, 'day' => $day->toDateString()]);
        $handover = ShiftHandover::firstOrCreate(['rotation_id' => $rotation->id], ['started_at' => now()]);

        $relay = Relay::create([
            'shift_handover_id' => $handover->id,
            'surveillance_observation_id' => $observation->id,
            'next_team_id' => $nextTeam->id,
        ]);

        $nextTeam->members()->get()
            ->when($nextTeam->lead, fn ($members, $lead) => $members->push($lead)->unique('id'))
            ->reject(fn ($member) => $member->is($context->actor))
            ->each(fn ($member) => $member->notify(new RelayAlert($relay, $observation->vessel->name)));
    }
}
