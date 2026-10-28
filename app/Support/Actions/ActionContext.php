<?php

namespace App\Support\Actions;

use App\Models\User;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

final class ActionContext
{
    /** @var array<int, array<string, mixed>> */
    private array $events = [];

    public function __construct(public readonly User $actor, public readonly ?User $requestedBy = null) {}

    /** @param array<string, scalar|null> $params */
    public function publish(
        Vessel|int $vessel,
        string $module,
        string $type,
        Model $subject,
        string $summaryKey,
        array $params = [],
        ?int $zoneId = null,
        ?string $source = null,
        ?Carbon $occurredAt = null,
    ): void {
        $this->events[] = [
            'vessel_id' => $vessel instanceof Vessel ? $vessel->id : $vessel,
            'module' => $module,
            'type' => $type,
            'object_type' => $subject->getMorphClass(),
            'object_id' => $subject->getKey(),
            'summary_key' => $summaryKey,
            'summary_params' => $params,
            'zone_id' => $zoneId,
            'source' => $source,
            'occurred_at' => $occurredAt ?? now(),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function events(): array
    {
        return $this->events;
    }

    /** @return array<int, int> */
    public function touchedVesselIds(): array
    {
        return array_values(array_unique(array_column($this->events, 'vessel_id')));
    }
}
