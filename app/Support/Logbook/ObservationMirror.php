<?php

namespace App\Support\Logbook;

use App\Enums\ObservationStatus;
use App\Enums\SurveillanceObservationStatus;
use App\Models\Observation;
use App\Models\SurveillanceObservation;

final class ObservationMirror
{
    public function sync(Observation $observation): ?SurveillanceObservation
    {
        if ($observation->vessel_id === null) {
            return null;
        }

        return SurveillanceObservation::updateOrCreate(
            ['legacy_observation_id' => $observation->id],
            [
                'vessel_id' => $observation->vessel_id,
                'body' => $observation->description,
                'status' => $observation->status === ObservationStatus::Closed ? SurveillanceObservationStatus::Closed : SurveillanceObservationStatus::Monitoring,
                'created_by' => $observation->author_id,
                'created_at' => $observation->observed_at,
            ],
        );
    }

    public function syncAll(): int
    {
        return Observation::query()->whereNotNull('vessel_id')->get()->each(fn (Observation $observation) => $this->sync($observation))->count();
    }
}
