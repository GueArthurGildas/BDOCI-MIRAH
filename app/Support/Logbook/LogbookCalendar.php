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

    public function isWorkingDay(Carbon $date): bool
    {
        return in_array($date->isoWeekday(), config('surveillance.watch.working_days'), true);
    }
}
