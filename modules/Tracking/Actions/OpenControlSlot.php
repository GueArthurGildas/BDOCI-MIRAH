<?php

namespace Modules\Tracking\Actions;

use App\Models\ControlSlot;
use App\Models\SlotPosition;
use App\Models\Vessel;
use App\Models\ZoneCount;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Vms\AssistedTests;
use App\Support\Vms\LocalityResolver;
use App\Support\Vms\PositionFeed;
use Illuminate\Support\Carbon;

class OpenControlSlot extends Action
{
    public function __construct(
        private readonly PositionFeed $feed,
        private readonly AssistedTests $tests,
        private readonly LocalityResolver $localities,
    ) {}

    public function code(): string
    {
        return 'tracking.open_slot';
    }

    public function permission(): Permission
    {
        return Permission::TrackingCreate;
    }

    public function handle(ActionContext $context, array $input): ControlSlot
    {
        $startedAt = isset($input['started_at']) ? Carbon::parse($input['started_at']) : now()->startOfHour();
        $endedAt = isset($input['ended_at']) ? Carbon::parse($input['ended_at']) : $startedAt->copy()->addHours(config('surveillance.vms.slot_window_hours'));

        $slot = ControlSlot::create([
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'team_id' => $context->actor->team_id,
            'operator_user_id' => $context->actor->id,
        ]);

        $at = now();
        $snapshots = $this->feed->snapshot($at);
        $vessels = Vessel::whereIn('id', $snapshots->pluck('vesselId'))->get()->keyBy('id');

        foreach ($snapshots as $snapshot) {
            SlotPosition::create([
                'control_slot_id' => $slot->id,
                'vessel_id' => $snapshot->vesselId,
                'latitude' => $snapshot->latitude,
                'longitude' => $snapshot->longitude,
                'speed' => $snapshot->speed,
                'zone_id' => $snapshot->zoneId,
                'locality_id' => $this->localities->idFor($snapshot->zoneId),
                'last_emission_at' => $snapshot->emittedAt,
                'emission_test' => $this->tests->emission($snapshot, $at),
                'speed_activity_test' => $this->tests->speedActivity($snapshot),
                'trajectory_test' => $this->tests->trajectory($snapshot, $vessels[$snapshot->vesselId], $at),
                'loading_source' => $this->feed->source(),
            ]);
        }

        $snapshots->whereNotNull('zoneId')->countBy('zoneId')->each(fn (int $count, int $zoneId) => ZoneCount::create([
            'control_slot_id' => $slot->id,
            'zone_id' => $zoneId,
            'vessels_count' => $count,
        ]));

        return $slot;
    }
}
