<?php

return [
    'title' => 'Settings',
    'subtitle' => 'Notification behavior and alert thresholds (no sensitive values)',
    'saved' => 'Settings saved.',
    'reset' => 'Restore defaults',
    'reset_done' => 'Default settings restored.',
    'default' => 'Default: :value',
    'range' => 'From :min to :max',
    'customized' => 'Customized',
    'groups' => [
        'browser' => [
            'title' => 'Notifications in the browser',
            'description' => 'How often the beacon checks for news and how toasts are displayed',
        ],
        'beacons' => [
            'title' => 'Silent beacon alert',
            'description' => 'Raised when installed beacons stop emitting',
        ],
        'eez' => [
            'title' => 'EEZ declarations alert',
            'description' => 'Raised when a foreign vessel is in the EEZ without an entry declaration, and for open entry and exit alerts',
        ],
        'licenses' => [
            'title' => 'Expiring license alert',
            'description' => 'Raised when licenses are about to expire',
        ],
        'watch' => [
            'title' => 'Watch and logbook',
            'description' => 'Shift hours, number of teams and check threshold (open point E1, provisional values)',
        ],
        'reports' => [
            'title' => 'Reports and archiving',
            'description' => 'Alert for generated reports and number of years before closed operations are archived (provisional values)',
        ],
    ],
    'fields' => [
        'notifications_poll_seconds' => 'Check for new notifications every (seconds)',
        'notifications_toast_seconds' => 'Toast display duration (seconds)',
        'notifications_max_toasts' => 'Maximum toasts per check',
        'alerts_beacons_silent_enabled' => 'Send the alert',
        'alerts_beacons_silent_threshold_hours' => 'A beacon is silent after (hours without emission)',
        'alerts_beacons_silent_cooldown_hours' => 'Minimum delay between two alerts (hours)',
        'alerts_licenses_expiring_enabled' => 'Send the alert',
        'alerts_licenses_expiring_warning_days' => 'A license is expiring within (days)',
        'alerts_licenses_expiring_cooldown_hours' => 'Minimum delay between two alerts (hours)',
        'alerts_eez_declarations_enabled' => 'Send the alert',
        'alerts_eez_declarations_position_window_hours' => 'A position counts as recent within (hours)',
        'alerts_eez_declarations_cooldown_hours' => 'Minimum delay between two alerts (hours)',
        'eez_variance_tolerance_percent' => 'Accepted entry/exit catch variance (percent)',
        'watch_shift_start_hour' => 'Shift start hour (0 to 23)',
        'watch_shift_end_hour' => 'Logbook closing hour (1 to 23)',
        'watch_teams_count' => 'Number of teams in rotation',
        'watch_check_threshold' => 'An alert is raised beyond (checks of the same vessel)',
        'alerts_scheduled_reports_enabled' => 'Notify recipients when a scheduled report is ready',
        'archive_after_years' => 'Closed operations are archived after (years)',
    ],
];
