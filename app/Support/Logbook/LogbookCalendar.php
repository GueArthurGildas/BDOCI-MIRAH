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
        $endHour = (int) config('surveillance.watch.shift_end_hour');
        $date = $moment->copy()->subHours($startHour)->startOfDay();

        return LogbookDay::firstOrCreate(
            ['day' => $date->toDateString()],
            [
                'shift_start_at' => $date->copy()->setTime($startHour, 0),
                'shift_end_at' => $date->copy()->setTime($endHour, 0),
                'status' => LogbookDayStatus::Open,
            ],
        );
    }

    public function isWorkingDay(Carbon $date): bool
    {
        return in_array($date->isoWeekday(), config('surveillance.watch.working_days'), true);
    }
}
