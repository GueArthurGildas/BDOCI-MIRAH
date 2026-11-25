<?php

namespace Modules\Tracking\Actions;

use App\Enums\PositionLoadingSource;
use App\Enums\TrackingSystem;
use App\Models\ControlSlot;
use App\Models\Position;
use App\Models\SlotPosition;
use App\Models\Vessel;
use App\Models\ZoneCount;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Vms\AssistedTests;
use App\Support\Vms\LocalityResolver;
use App\Support\Vms\PositionSnapshot;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordManualPosition extends Action
{
    public function __construct(
        private readonly AssistedTests $tests,
        private readonly LocalityResolver $localities,
    ) {}

    public function code(): string
    {
        return 'tracking.record_manual_position';
    }

    public function permission(): Permission
    {
        return Permission::TrackingCreate;
    }

    public function handle(ActionContext $context, array $input): Position
    {
        $recordedAt = isset($input['recorded_at']) ? Carbon::parse($input['recorded_at']) : now();
        $zoneId = isset($input['zone_id']) ? (int) $input['zone_id'] : null;
        [$latitude, $longitude] = $this->coordinates($input, $zoneId);
        $zoneId ??= $this->zoneAt($latitude, $longitude);
        $speed = isset($input['speed_knots']) ? (float) $input['speed_knots'] : null;

        $position = Position::create([
            'vessel_id' => $input['vessel_id'],
            'zone_id' => $zoneId,
            'system' => TrackingSystem::Manual,
            'recorded_at' => $recordedAt,
            'received_at' => now(),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'speed_knots' => $speed,
            'activity' => $input['activity'] ?? null,
        ]);

        if (isset($input['control_slot_id'])) {
            $this->addToSlot(ControlSlot::findOrFail($input['control_slot_id']), $position, $speed, $input['activity'] ?? null);
        }

        $context->publish(
            vessel: (int) $input['vessel_id'],
            module: 'tracking',
            type: 'position.manual_recorded',
            subject: $position,
            summaryKey: 'tracking::events.manual_position_recorded',
            params: ['latitude' => round($latitude, 4), 'longitude' => round($longitude, 4)],
            occurredAt: $recordedAt,
        );

        return $position;
    }

    /** @return array{0: float, 1: float} */
    private function coordinates(array $input, ?int $zoneId): array
    {
        if (isset($input['latitude'], $input['longitude'])) {
            return [(float) $input['latitude'], (float) $input['longitude']];
        }

        $point = $zoneId === null ? null : DB::selectOne('SELECT ST_Y(ST_PointOnSurface(geom)) AS latitude, ST_X(ST_PointOnSurface(geom)) AS longitude FROM zones WHERE id = ? AND geom IS NOT NULL', [$zoneId]);

        if ($point === null) {
            throw ValidationException::withMessages(['latitude' => __('tracking::tracking.manual.errors.no_location')]);
        }

        return [round((float) $point->latitude, 6), round((float) $point->longitude, 6)];
    }

    private function zoneAt(float $latitude, float $longitude): ?int
    {
        $zone = DB::selectOne(
            'SELECT id FROM zones WHERE is_active AND geom IS NOT NULL AND ST_Covers(geom, ST_SetSRID(ST_MakePoint(?, ?), 4326)) ORDER BY ST_Area(geom) LIMIT 1',
            [$longitude, $latitude],
        );

        return $zone?->id;
    }

    private function addToSlot(ControlSlot $slot, Position $position, ?float $speed, ?string $activity): void
    {
        $lastEmission = Position::query()
            ->where('vessel_id', $position->vessel_id)
            ->where('system', '!=', TrackingSystem::Manual->value)
            ->where('recorded_at', '<=', now())
            ->max('recorded_at');
        $snapshot = new PositionSnapshot(
            $position->vessel_id,
            $position->latitude,
            $position->longitude,
            $speed,
            $position->zone_id,
            $lastEmission === null ? null : Carbon::parse($lastEmission),
            $activity,
        );
        $at = now();

        SlotPosition::updateOrCreate(['control_slot_id' => $slot->id, 'vessel_id' => $position->vessel_id], [
            'latitude' => $position->latitude,
            'longitude' => $position->longitude,
            'speed' => $speed,
            'zone_id' => $position->zone_id,
            'locality_id' => $this->localities->idFor($position->zone_id),
            'last_emission_at' => $snapshot->emittedAt,
            'emission_test' => $this->tests->emission($snapshot, $at),
            'speed_activity_test' => $this->tests->speedActivity($snapshot),
            'trajectory_test' => $this->tests->trajectory($snapshot, Vessel::findOrFail($position->vessel_id), $at),
            'result' => null,
            'confirmed_by' => null,
            'loading_source' => PositionLoadingSource::Manual,
        ]);

        ZoneCount::where('control_slot_id', $slot->id)->delete();
        $slot->slotPositions()->whereNotNull('zone_id')->get()->countBy('zone_id')->each(fn (int $count, int $zoneId) => ZoneCount::create([
            'control_slot_id' => $slot->id,
            'zone_id' => $zoneId,
            'vessels_count' => $count,
        ]));
    }
}
