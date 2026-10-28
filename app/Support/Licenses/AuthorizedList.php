<?php

namespace App\Support\Licenses;

use App\Models\AuthorizedListPublication;
use App\Models\License;
use App\Models\LicenseRevocation;
use App\Models\LicenseSuspension;
use Illuminate\Support\Collection;

class AuthorizedList
{
    /** @return Collection<int, License> */
    public function rows(): Collection
    {
        return License::query()
            ->authorized()
            ->with(['vessel.flagCountry', 'zones', 'gears'])
            ->get()
            ->groupBy('vessel_id')
            ->map(fn (Collection $licenses) => CurrentLicense::select($licenses))
            ->sortBy(fn (License $license) => mb_strtolower($license->vessel->name))
            ->values();
    }

    public function lastPublication(): ?AuthorizedListPublication
    {
        return AuthorizedListPublication::query()->orderByDesc('version')->first();
    }

    public function isStale(): bool
    {
        $publication = $this->lastPublication();

        if ($publication === null) {
            return true;
        }

        return License::where('updated_at', '>', $publication->published_at)->exists()
            || LicenseSuspension::where('updated_at', '>', $publication->published_at)->exists()
            || LicenseRevocation::where('updated_at', '>', $publication->published_at)->exists()
            || License::where('status', 'active')->whereBetween('expires_on', [$publication->published_at->toDateString(), today()->subDay()->toDateString()])->exists();
    }
}
