<?php

namespace App\Queries;

use App\Enums\InfractionStatus;
use App\Models\Infraction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class OpenInfractionCases
{
    private const CLOSED_STATUSES = [InfractionStatus::Closed, InfractionStatus::Dismissed];

    public static function query(): Builder
    {
        return Infraction::query()->whereNotIn('status', array_map(fn (InfractionStatus $status) => $status->value, self::CLOSED_STATUSES));
    }

    public static function countForVessel(int $vesselId): int
    {
        return self::query()->where('vessel_id', $vesselId)->count();
    }

    /** @return Collection<int, Infraction> */
    public static function all(): Collection
    {
        return self::query()->with(['vessel', 'type'])->orderBy('detected_at')->get();
    }

    /** @return Collection<int, Infraction> */
    public static function forVessel(int $vesselId): Collection
    {
        return Infraction::query()->where('vessel_id', $vesselId)->with('type')->latest('detected_at')->get();
    }

    public static function countSince(int $vesselId, \DateTimeInterface $since): int
    {
        return Infraction::query()->where('vessel_id', $vesselId)->where('detected_at', '>=', $since)->count();
    }
}
