<?php

namespace App\Queries;

use App\Enums\SurveillanceObservationStatus;
use App\Enums\WatchOrigin;
use App\Models\CheckCounter;
use App\Models\Relay;
use App\Models\StandingInstruction;
use App\Models\SurveillanceObservation;
use App\Models\User;
use App\Models\WatchEntry;
use Illuminate\Support\Collection;

final class MorningBoard
{
    /** @return Collection<int, WatchEntry> */
    public static function watchEntries(): Collection
    {
        $priority = array_flip(array_map(fn (WatchOrigin $origin) => $origin->value, WatchOrigin::cases()));

        return WatchEntry::open()
            ->with(['vessel', 'operation', 'logbookEntry'])
            ->orderBy('started_at')
            ->get()
            ->sortBy(fn (WatchEntry $entry) => $priority[$entry->origin->value] * 1_000_000_000 + $entry->started_at->timestamp)
            ->values();
    }

    /** @return Collection<int, StandingInstruction> */
    public static function standingInstructions(): Collection
    {
        return StandingInstruction::query()
            ->with('vessel')
            ->where('started_on', '<=', today())
            ->where(fn ($query) => $query->whereNull('ended_on')->orWhere('ended_on', '>=', today()))
            ->latest('started_on')
            ->get();
    }

    /** @return Collection<int, SurveillanceObservation> */
    public static function openObservations(): Collection
    {
        return SurveillanceObservation::query()
            ->with(['vessel', 'author'])
            ->where('status', SurveillanceObservationStatus::Monitoring)
            ->latest('created_at')
            ->get();
    }

    /** @return Collection<int, Relay> */
    public static function pendingRelaysFor(User $user): Collection
    {
        return Relay::query()
            ->with(['observation.vessel', 'handover.rotation.team'])
            ->whereNull('acknowledged_at')
            ->where(fn ($query) => $query
                ->when($user->team_id, fn ($query, $teamId) => $query->where('next_team_id', $teamId))
                ->orWhereHas('nextTeam', fn ($query) => $query->where('lead_user_id', $user->id)))
            ->latest('id')
            ->get();
    }

    /** @return Collection<int, CheckCounter> */
    public static function checkAlerts(): Collection
    {
        return CheckCounter::query()
            ->with('vessel')
            ->where('is_alert_raised', true)
            ->whereNotIn('vessel_id', WatchEntry::open()->where('origin', WatchOrigin::MoreThan3Checks)->select('vessel_id'))
            ->get();
    }
}
