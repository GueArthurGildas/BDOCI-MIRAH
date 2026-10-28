<?php

namespace App\Support\Core;

use App\Models\Vessel;
use App\Models\VesselEvent;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class VesselEventRecorder
{
    public function record(
        Vessel|int $vessel,
        string $module,
        string $type,
        string $summary,
        ?Model $subject = null,
        ?DateTimeInterface $occurredAt = null,
        ?int $zoneId = null,
        ?string $source = null,
    ): VesselEvent {
        return VesselEvent::create([
            'vessel_id' => $vessel instanceof Vessel ? $vessel->getKey() : $vessel,
            'occurred_at' => $occurredAt ?? now(),
            'module' => $module,
            'type' => $type,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'summary' => $summary,
            'zone_id' => $zoneId,
            'user_id' => Auth::id(),
            'source' => $source,
        ]);
    }
}
