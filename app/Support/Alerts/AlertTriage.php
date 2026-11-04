<?php

namespace App\Support\Alerts;

use App\Enums\AlertCategory;
use App\Enums\AlertRuleType;
use App\Enums\AlertStatus;
use App\Enums\OperationStatus;
use App\Enums\VesselStatusLight;
use App\Enums\WatchStatus;
use App\Models\Alert;
use App\Models\User;
use App\Models\VesselCurrentState;
use App\Models\WatchEntry;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class AlertTriage
{
    public static function apply(Builder $query, AlertCategory $category, User $user): Builder
    {
        return match ($category) {
            AlertCategory::Urgent => $query->where(self::urgent()),
            AlertCategory::Useful => $query->whereNot(self::urgent())->where(self::followed($user)),
            AlertCategory::Other => $query->whereNot(self::urgent())->whereNot(self::followed($user)),
        };
    }

    /** @return array<string, Collection<int, Alert>> */
    public static function latest(User $user, int $limit): array
    {
        return collect(AlertCategory::cases())
            ->mapWithKeys(fn (AlertCategory $category) => [
                $category->value => self::apply(Alert::query()->with(['vessel', 'zone']), $category, $user)
                    ->where('alerts.status', '!=', AlertStatus::Closed->value)
                    ->latest('triggered_at')
                    ->limit($limit)
                    ->get(),
            ])
            ->all();
    }

    /**
     * @param  Collection<int, Alert>  $alerts
     * @return array<int, AlertCategory>
     */
    public static function categorize(Collection $alerts, User $user): array
    {
        $categories = [];

        foreach (AlertCategory::cases() as $category) {
            foreach (self::apply(Alert::query(), $category, $user)->whereIn('alerts.id', $alerts->pluck('id')->all())->pluck('alerts.id') as $id) {
                $categories[$id] = $category;
            }
        }

        return $categories;
    }

    /** @return array<string, int> */
    public static function counts(User $user): array
    {
        return collect(AlertCategory::cases())
            ->mapWithKeys(fn (AlertCategory $category) => [
                $category->value => self::apply(Alert::query(), $category, $user)->where('alerts.status', '!=', AlertStatus::Closed->value)->count(),
            ])
            ->all();
    }

    private static function urgent(): Closure
    {
        return fn (Builder $query) => $query
            ->where('alerts.status', AlertStatus::Open->value)
            ->where(fn (Builder $inner) => $inner
                ->where('alerts.type', AlertRuleType::Geofencing->value)
                ->orWhereIn('alerts.vessel_id', VesselCurrentState::query()->select('vessel_id')->whereIn('status_light', [VesselStatusLight::Suspect->value, VesselStatusLight::InViolation->value])));
    }

    private static function followed(User $user): Closure
    {
        return fn (Builder $query) => $query
            ->where('alerts.status', '!=', AlertStatus::Closed->value)
            ->where(fn (Builder $inner) => $inner
                ->whereIn('alerts.vessel_id', WatchEntry::query()->select('vessel_id')->where('status', WatchStatus::Open->value))
                ->orWhereRaw('coalesce(alerts.acknowledged_by, 0) = ?', [$user->id])
                ->orWhereIn('alerts.vessel_id', DB::table('operation_vessel')
                    ->join('operations', 'operations.id', '=', 'operation_vessel.operation_id')
                    ->where('operations.created_by', $user->id)
                    ->whereIn('operations.status', [OperationStatus::Open->value, OperationStatus::InProgress->value])
                    ->select('operation_vessel.vessel_id')));
    }
}
