<?php

namespace App\Support\Vms;

use Illuminate\Support\Carbon;

final class EmissionGap
{
    public static function format(?Carbon $lastEmission): string
    {
        if ($lastEmission === null) {
            return '—';
        }

        $minutes = (int) $lastEmission->diffInMinutes(now(), absolute: true);
        $days = intdiv($minutes, 1440);
        $hours = intdiv($minutes % 1440, 60);

        return match (true) {
            $days > 0 && $hours > 0 => __('ui.emission_gap.days_hours', ['days' => $days, 'hours' => $hours]),
            $days > 0 => __('ui.emission_gap.days', ['days' => $days]),
            $hours > 0 => __('ui.emission_gap.hours', ['hours' => $hours]),
            default => __('ui.emission_gap.minutes', ['minutes' => $minutes]),
        };
    }
}
