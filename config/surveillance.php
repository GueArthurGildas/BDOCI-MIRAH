<?php

return [
    'beacon_silence_threshold_hours' => (int) env('SURVEILLANCE_BEACON_SILENCE_THRESHOLD_HOURS', 2),
    'notification_poll_seconds' => (int) env('SURVEILLANCE_NOTIFICATION_POLL_SECONDS', 30),
    'notification_toast_seconds' => (int) env('SURVEILLANCE_NOTIFICATION_TOAST_SECONDS', 8),
    'notification_max_toasts' => (int) env('SURVEILLANCE_NOTIFICATION_MAX_TOASTS', 3),

    'alerts' => [
        'beacons_silent' => ['enabled' => true, 'cooldown_hours' => 4],
        'licenses_expiring' => ['enabled' => true, 'cooldown_hours' => 24],
        'eez_declarations' => ['enabled' => true, 'cooldown_hours' => 12],
    ],

    'license_expiry_warning_days' => (int) env('SURVEILLANCE_LICENSE_EXPIRY_WARNING_DAYS', 30),
    'license_expiry_second_warning_days' => (int) env('SURVEILLANCE_LICENSE_EXPIRY_SECOND_WARNING_DAYS', 60),
    'license_expiry_third_warning_days' => (int) env('SURVEILLANCE_LICENSE_EXPIRY_THIRD_WARNING_DAYS', 90),
    'license_suspension_max_months' => 3,

    'eez_position_window_hours' => (int) env('SURVEILLANCE_EEZ_POSITION_WINDOW_HOURS', 6),
    'eez_variance_tolerance_percent' => (int) env('SURVEILLANCE_EEZ_VARIANCE_TOLERANCE_PERCENT', 10),
    'eez_designated_localities' => ['Abidjan', 'San Pedro'],
    'watch' => [
        'shift_start_hour' => (int) env('SURVEILLANCE_WATCH_SHIFT_START_HOUR', 8),
        'shift_end_hour' => (int) env('SURVEILLANCE_WATCH_SHIFT_END_HOUR', 18),
        'teams_count' => (int) env('SURVEILLANCE_WATCH_TEAMS_COUNT', 3),
        'check_threshold' => (int) env('SURVEILLANCE_WATCH_CHECK_THRESHOLD', 3),
        'working_days' => array_map('intval', explode(',', env('SURVEILLANCE_WATCH_WORKING_DAYS', '1,2,3,4,5,6,7'))),
    ],

    'flag_states' => ['CI', 'BJ', 'BZ', 'CN', 'ES', 'FR', 'GH', 'GN', 'JP', 'KR', 'LR', 'MA', 'NG', 'PA', 'PT', 'SL', 'SN', 'TG', 'TW'],
];
