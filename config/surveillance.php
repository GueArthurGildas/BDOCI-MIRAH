<?php

return [
    'beacon_silence_threshold_hours' => (int) env('SURVEILLANCE_BEACON_SILENCE_THRESHOLD_HOURS', 2),
    'license_expiry_warning_days' => (int) env('SURVEILLANCE_LICENSE_EXPIRY_WARNING_DAYS', 30),
];
