<?php

namespace App\Support\Vms;

use App\Enums\AlertRuleType;
use App\Enums\AlertStatus;
use App\Enums\ZoneType;
use App\Models\Alert;
use App\Models\AlertRule;
use App\Models\Position;
use App\Models\Zone;
use App\Queries\SilentBeacons;
use Illuminate\Support\Collection;

final class AlertRuleEvaluator
{
    private const DEFAULT_MAX_SPEED_KNOTS = 25;

    /** @return Collection<int, Alert> */
    public function evaluate(): Collection
    {
        $raised = collect();

        foreach (AlertRule::active()->orderBy('id')->get() as $rule) {
            $raised = $raised->concat(match ($rule->type) {
                AlertRuleType::Silence => $this->silence($rule),
                AlertRuleType::Geofencing => $this->geofencing($rule),
                AlertRuleType::Speed => $this->speed($rule),
                default => [],
            });
        }

        return $raised->values();
    }

    /** @return array<int, Alert> */
    private function silence(AlertRule $rule): array
    {
        $silentIds = SilentBeacons::vessels()->pluck('id');

        Alert::unresolved()->where('alert_rule_id', $rule->id)->whereNotIn('vessel_id', $silentIds)->update(['status' => AlertStatus::Closed, 'updated_at' => now()]);

        return $silentIds->map(fn (int $vesselId) => $this->raise($rule, $vesselId, null))->filter()->all();
    }

    /** @return array<int, Alert> */
    private function geofencing(AlertRule $rule): array
    {
        $zones = Zone::where('is_active', true)
            ->when($rule->parameters['zone_ids'] ?? null, fn ($query, $ids) => $query->whereIn('id', $ids), fn ($query) => $query->where('type', ZoneType::Restricted))
            ->get();

        $raised = [];

        foreach ($zones as $zone) {
            $vesselIds = Position::latestPerVessel(now())
                ->where('positions.recorded_at', '>=', now()->subDay())
                ->whereRaw('ST_Covers((SELECT geom FROM zones WHERE id = ?), ST_SetSRID(ST_MakePoint(positions.longitude::double precision, positions.latitude::double precision), 4326))', [$zone->id])
                ->pluck('positions.vessel_id');

            foreach ($vesselIds as $vesselId) {
                $raised[] = $this->raise($rule, $vesselId, $zone->id);
            }
        }

        return array_values(array_filter($raised));
    }

    /** @return array<int, Alert> */
    private function speed(AlertRule $rule): array
    {
        $limit = $rule->parameters['max_knots'] ?? self::DEFAULT_MAX_SPEED_KNOTS;

        return Position::latestPerVessel(now())->where('positions.speed_knots', '>', $limit)->pluck('positions.vessel_id')
            ->map(fn (int $vesselId) => $this->raise($rule, $vesselId, null))->filter()->all();
    }

    private function raise(AlertRule $rule, int $vesselId, ?int $zoneId): ?Alert
    {
        $exists = Alert::unresolved()->where('alert_rule_id', $rule->id)->where('vessel_id', $vesselId)->where('zone_id', $zoneId)->exists();

        return $exists ? null : Alert::create([
            'vessel_id' => $vesselId,
            'alert_rule_id' => $rule->id,
            'type' => $rule->type,
            'zone_id' => $zoneId,
            'triggered_at' => now(),
            'status' => AlertStatus::Open,
        ]);
    }
}
