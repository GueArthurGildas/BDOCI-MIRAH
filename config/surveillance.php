<?php

return [
    'beacon_silence_threshold_hours' => (int) env('SURVEILLANCE_BEACON_SILENCE_THRESHOLD_HOURS', 2),
    'notification_poll_seconds' => (int) env('SURVEILLANCE_NOTIFICATION_POLL_SECONDS', 30),
    'notification_toast_seconds' => (int) env('SURVEILLANCE_NOTIFICATION_TOAST_SECONDS', 8),
    'notification_max_toasts' => (int) env('SURVEILLANCE_NOTIFICATION_MAX_TOASTS', 3),

    'alerts' => [
        'beacons_silent' => ['enabled' => true, 'cooldown_hours' => 4],
        'licenses_expiring' => ['enabled' => true, 'cooldown_hours' => 24],
    ],

    'license_expiry_warning_days' => (int) env('SURVEILLANCE_LICENSE_EXPIRY_WARNING_DAYS', 30),

    'flag_states' => ['CI', 'BJ', 'BZ', 'CN', 'ES', 'FR', 'GH', 'GN', 'JP', 'KR', 'LR', 'MA', 'NG', 'PA', 'PT', 'SL', 'SN', 'TG', 'TW'],
];
