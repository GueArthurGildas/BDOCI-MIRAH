<?php

namespace App\Support\Vms;

use App\Enums\SlotTestResult;
use App\Enums\ZoneType;
use App\Models\ConsistencyRule;
use App\Models\Vessel;
use App\Models\Zone;
use App\Models\ZoneRule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class AssistedTests
{
    public const SPEED_ACTIVITY_RULE = 'speed_activity';

    public const TRAJECTORY_RULE = 'trajectory';

    public const DEFAULT_SPEED_ACTIVITY = [
        'moored_max_knots' => 0.5,
        'fishing_min_knots' => 0.5,
        'fishing_max_knots' => 6.0,
        'transit_min_knots' => 6.0,
        'max_knots' => 25.0,
    ];

    public const DEFAULT_TRAJECTORY = ['window_hours' => 6, 'coastline' => null];

    public function __construct(
        private readonly SilenceThreshold $threshold,
        private readonly GeoCalculator $geo,
        private readonly SegmentMapper $segments,
    ) {}

    public function emission(PositionSnapshot $snapshot, Carbon $at): SlotTestResult
    {
        if ($snapshot->emittedAt === null) {
            return SlotTestResult::Ko;
        }

        return $snapshot->emittedAt->gte($at->copy()->subMinutes((int) round($this->threshold->hours() * 60))) ? SlotTestResult::Ok : SlotTestResult::Ko;
    }

    public function speedActivity(PositionSnapshot $snapshot): SlotTestResult
    {
        $rule = $this->parameters(self::SPEED_ACTIVITY_RULE, self::DEFAULT_SPEED_ACTIVITY);

        if ($snapshot->speed === null) {
            return SlotTestResult::ToConfirm;
        }

        if ($snapshot->speed > $rule['max_knots']) {
            return SlotTestResult::Ko;
        }

        $activity = $snapshot->activity ?? ($this->isInPortArea($snapshot) ? 'moored' : null);

        return match ($activity) {
            'moored' => $this->result($snapshot->speed <= $rule['moored_max_knots']),
            'fishing' => $this->result($snapshot->speed >= $rule['fishing_min_knots'] && $snapshot->speed <= $rule['fishing_max_knots']),
            'transit' => $this->result($snapshot->speed >= $rule['transit_min_knots']),
            default => SlotTestResult::ToConfirm,
        };
    }

    public function trajectory(PositionSnapshot $snapshot, Vessel $vessel, Carbon $at): SlotTestResult
    {
        if ($snapshot->emittedAt === null || $snapshot->latitude === null) {
            return SlotTestResult::ToConfirm;
        }

        $rule = $this->parameters(self::TRAJECTORY_RULE, self::DEFAULT_TRAJECTORY);
        $from = $at->copy()->subHours((int) $rule['window_hours']);

        if ($this->touchesRestrictedZone($vessel, $from, $at)) {
            return SlotTestResult::Ko;
        }

        return $this->respectsCoastDistance($snapshot, $vessel, $rule['coastline']) ? SlotTestResult::Ok : SlotTestResult::Ko;
    }

    private function touchesRestrictedZone(Vessel $vessel, Carbon $from, Carbon $to): bool
    {
        $traversed = $this->geo->zonesTraversed($vessel->id, $from, $to->copy()->addMinute());

        return Zone::whereIn('id', $traversed)->where('type', ZoneType::Restricted)->exists();
    }

    /** @param array<string, mixed>|null $coastline */
    private function respectsCoastDistance(PositionSnapshot $snapshot, Vessel $vessel, ?array $coastline): bool
    {
        $segment = $this->segments->for($vessel);

        if ($coastline === null || $segment === null || $snapshot->zoneId === null) {
            return true;
        }

        $distance = $this->geo->distanceToCoastNauticalMiles($snapshot->latitude, $snapshot->longitude, $coastline);

        return ! ZoneRule::where('segment', $segment)->get()->contains(
            fn (ZoneRule $rule) => $this->isInside($snapshot, $rule->zone_id) && $distance < $rule->nautical_miles,
        );
    }

    private function isInside(PositionSnapshot $snapshot, int $zoneId): bool
    {
        return (bool) DB::selectOne(
            'SELECT 1 AS inside FROM zones WHERE id = ? AND geom IS NOT NULL AND ST_Covers(geom, ST_SetSRID(ST_MakePoint(?, ?), 4326))',
            [$zoneId, $snapshot->longitude, $snapshot->latitude],
        );
    }

    private function isInPortArea(PositionSnapshot $snapshot): bool
    {
        return $snapshot->zoneId !== null && Zone::whereKey($snapshot->zoneId)->where('type', ZoneType::PortArea)->exists();
    }

    private function result(bool $coherent): SlotTestResult
    {
        return $coherent ? SlotTestResult::Ok : SlotTestResult::Ko;
    }

    /**
     * @param  array<string, mixed>  $defaults
     * @return array<string, mixed>
     */
    private function parameters(string $code, array $defaults): array
    {
        $rule = ConsistencyRule::where('code', $code)->where('is_active', true)->first();

        return array_merge($defaults, $rule?->parameters ?? []);
    }
}
