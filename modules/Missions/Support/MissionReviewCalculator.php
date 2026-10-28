<?php

namespace Modules\Missions\Support;

use App\Enums\InspectionResult;
use App\Enums\ObservedUnitLevel;
use App\Enums\PrecautionaryMeasureType;
use App\Models\Decision;
use App\Models\ExternalInspection;
use App\Models\InfractionCase;
use App\Models\LitigationFollowUp;
use App\Models\Mission;
use App\Models\ObservedUnit;
use App\Models\PrecautionaryMeasure;
use App\Models\Seizure;
use App\Models\ViolationReport;
use Illuminate\Database\Eloquent\Builder;

final class MissionReviewCalculator
{
    public const ARRESTS_COUNT = 0;

    /** @return array<string, int|float|null> */
    public function compute(Mission $mission): array
    {
        $levels = ObservedUnit::where('mission_id', $mission->id)->selectRaw('level, count(*) as total')->groupBy('level')->pluck('total', 'level');
        $inspectionResults = ExternalInspection::query()
            ->whereIn('id', ObservedUnit::where('mission_id', $mission->id)->where('level', ObservedUnitLevel::Inspected->value)->select('inspection_id'))
            ->pluck('result');
        $compliant = $inspectionResults->filter(fn (?string $result) => InspectionResult::tryFrom((string) $result) === InspectionResult::Compliant)->count();

        $cases = $this->cases($mission);

        return [
            'agents_count' => $mission->participants()->count(),
            'duration_hours' => $this->durationHours($mission),
            'nautical_miles' => (float) $mission->missionAssets()->sum('nautical_miles'),
            'fuel_l' => (float) $mission->missionAssets()->sum('consumed_fuel_l'),
            'sighted_units_count' => (int) ($levels[ObservedUnitLevel::Sighted->value] ?? 0),
            'approached_units_count' => (int) ($levels[ObservedUnitLevel::Approached->value] ?? 0),
            'inspected_units_count' => (int) ($levels[ObservedUnitLevel::Inspected->value] ?? 0),
            'compliant_count' => $compliant,
            'non_compliant_count' => $inspectionResults->count() - $compliant,
            'infractions_count' => (clone $cases)->count(),
            'violation_reports_count' => ViolationReport::whereIn('infraction_case_id', (clone $cases)->select('id'))->count(),
            'diversions_count' => $this->decidedMeasures($cases, PrecautionaryMeasureType::Diversion),
            'immobilizations_count' => $this->decidedMeasures($cases, PrecautionaryMeasureType::Immobilization),
            'seizures_count' => Seizure::whereIn('infraction_case_id', (clone $cases)->select('id'))->count(),
            'arrests_count' => self::ARRESTS_COUNT,
            'penalties_count' => Decision::where('is_penalty', true)
                ->whereIn('follow_up_id', LitigationFollowUp::whereIn('infraction_case_id', (clone $cases)->select('id'))->select('id'))
                ->count(),
        ];
    }

    private function cases(Mission $mission): Builder
    {
        $inspectionIds = ObservedUnit::where('mission_id', $mission->id)->whereNotNull('inspection_id')->select('inspection_id');

        return InfractionCase::query()->where(fn (Builder $query) => $query
            ->where('mission_id', $mission->id)
            ->orWhereIn('inspection_id', $inspectionIds));
    }

    private function decidedMeasures(Builder $cases, PrecautionaryMeasureType $type): int
    {
        return PrecautionaryMeasure::query()
            ->whereIn('infraction_case_id', (clone $cases)->select('id'))
            ->where('type', $type->value)
            ->whereNotNull('decided_at')
            ->count();
    }

    private function durationHours(Mission $mission): ?float
    {
        $start = $mission->actual_start_at ?? $mission->planned_start_at;
        $end = $mission->actual_end_at ?? ($mission->actual_start_at ? null : $mission->planned_end_at);

        return $start && $end ? round($start->diffInMinutes($end) / 60, 1) : null;
    }
}
