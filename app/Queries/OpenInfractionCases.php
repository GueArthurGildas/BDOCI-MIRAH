<?php

namespace App\Queries;

use App\Enums\InfractionCaseStatus;
use App\Models\InfractionCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class OpenInfractionCases
{
    public static function query(): Builder
    {
        return InfractionCase::query()->where('status', '!=', InfractionCaseStatus::Closed->value);
    }

    public static function countForVessel(int $vesselId): int
    {
        return self::query()->where('vessel_id', $vesselId)->count();
    }

    /** @return Collection<int, InfractionCase> */
    public static function all(): Collection
    {
        return self::query()->with(['vessel', 'upheld.type'])->orderBy('opened_at')->get();
    }

    /** @return Collection<int, InfractionCase> */
    public static function forVessel(int $vesselId): Collection
    {
        return InfractionCase::query()->where('vessel_id', $vesselId)->with('upheld.type')->latest('opened_at')->get();
    }

    public static function countSince(int $vesselId, \DateTimeInterface $since): int
    {
        return InfractionCase::query()->where('vessel_id', $vesselId)->where('opened_at', '>=', $since)->count();
    }
}
