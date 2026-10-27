<?php

return [
    'beacon_silence_threshold_hours' => (int) env('SURVEILLANCE_BEACON_SILENCE_THRESHOLD_HOURS', 2),
    'notification_poll_seconds' => (int) env('SURVEILLANCE_NOTIFICATION_POLL_SECONDS', 30),
    'license_expiry_warning_days' => (int) env('SURVEILLANCE_LICENSE_EXPIRY_WARNING_DAYS', 30),

    'flag_states' => ['CI', 'BJ', 'BZ', 'CN', 'ES', 'FR', 'GH', 'GN', 'JP', 'KR', 'LR', 'MA', 'NG', 'PA', 'PT', 'SL', 'SN', 'TG', 'TW'],
];
