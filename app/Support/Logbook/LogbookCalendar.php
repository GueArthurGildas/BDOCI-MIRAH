<?php

namespace App\Support\Logbook;

use App\Enums\LogbookDayStatus;
use App\Models\LogbookDay;
use Illuminate\Support\Carbon;

final class LogbookCalendar
{
    public function today(): LogbookDay
    {
        return $this->dayFor(now());
    }

    public function dayFor(Carbon $moment): LogbookDay
    {
        $startHour = (int) config('surveillance.watch.shift_start_hour');
        $date = $moment->copy()->subHours($startHour)->startOfDay();
        $start = $date->copy()->setTime($startHour, 0);

        return LogbookDay::firstOrCreate(
            ['day' => $date->toDateString()],
            [
                'shift_start_at' => $start,
                'shift_end_at' => $start->copy()->addDay(),
                'status' => LogbookDayStatus::Open,
            ],
        );
    }

    /** @return array<int, int> */
    public function handoverHours(): array
    {
        return [(int) config('surveillance.watch.shift_start_hour'), (int) config('surveillance.watch.shift_end_hour')];
    }

    public function handoverSlot(Carbon $at): Carbon
    {
        return collect([-1, 0, 1])
            ->flatMap(fn (int $offset) => collect($this->handoverHours())->map(fn (int $hour) => $at->copy()->addDays($offset)->setTime($hour, 0)))
            ->sortBy(fn (Carbon $slot) => abs($slot->diffInSeconds($at, false)))
            ->first();
    }

    public function isWorkingDay(Carbon $date): bool
    {
        return in_array($date->isoWeekday(), config('surveillance.watch.working_days'), true);
    }
}
