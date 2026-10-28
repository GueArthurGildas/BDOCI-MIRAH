<?php

return [
    'beacon_silence_threshold_hours' => (int) env('SURVEILLANCE_BEACON_SILENCE_THRESHOLD_HOURS', 2),
    'license_expiry_warning_days' => (int) env('SURVEILLANCE_LICENSE_EXPIRY_WARNING_DAYS', 30),

    'verifications_before_suspect' => (int) env('SURVEILLANCE_VERIFICATIONS_BEFORE_SUSPECT', 3),
    'catch_discrepancy_threshold_percent' => (float) env('SURVEILLANCE_CATCH_DISCREPANCY_THRESHOLD_PERCENT', 10),
    'shift_start' => env('SURVEILLANCE_SHIFT_START', '08:00'),
    'shift_end' => env('SURVEILLANCE_SHIFT_END', '18:00'),

    'flag_states' => ['CI', 'BJ', 'BZ', 'CN', 'ES', 'FR', 'GH', 'GN', 'JP', 'KR', 'LR', 'MA', 'NG', 'PA', 'PT', 'SL', 'SN', 'TG', 'TW'],
];
