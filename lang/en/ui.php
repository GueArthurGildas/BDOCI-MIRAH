<?php

return [
    'language' => 'English',
    'change_language' => 'Change language',
    'home' => 'Home',
    'log_out' => 'Log out',
    'filters' => 'Filters',
    'filter' => 'Filter',
    'reset' => 'Reset',
    'search' => 'Search',
    'results_count' => '{0} No results|{1} :count result|[2,*] :count results',
    'no_results' => 'No results.',
    'select_placeholder' => 'Select…',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'create' => 'Create',
    'edit' => 'Edit',
    'view' => 'View',
    'back' => 'Back',
    'close' => 'Close',
    'back_home' => 'Back to home',
    'actions' => 'Actions',
    'all' => 'All',
    'yes' => 'Yes',
    'no' => 'No',
    'account_deactivated' => 'Your account has been deactivated. Contact an administrator.',
    'saved' => 'Changes saved.',
    'updated_at' => 'Updated at :time',

    'navigation_groups' => [
        'overview' => 'Overview',
        'surveillance' => 'Surveillance',
        'registry' => 'Registry',
        'controls' => 'Controls',
        'administration' => 'Administration',
    ],

    'roles' => [
        'admin' => 'Administrator',
        'director' => 'Director',
        'team_lead' => 'Team lead',
        'surveillance_agent' => 'Surveillance agent',
        'inspector' => 'Inspector',
    ],

    'permission_groups' => [
        'license_management' => 'License management',
        'infraction_investigation' => 'Infraction investigation',
        'surveillance_opinion' => 'Surveillance opinion',
        'automatic_sending' => 'Automatic sending management',
        'ai_trigger' => 'Trigger the AI agent',
    ],

    'settings' => [
        'beacon_silence_threshold_hours' => 'Beacon emission break (hours)',
        'license_expiry_warning_days' => 'Warning before a license expires (days)',
        'verifications_before_suspect' => 'Verifications before a vessel becomes suspect',
        'catch_discrepancy_threshold_percent' => 'Declared vs observed catch discrepancy threshold (%)',
        'shift_start' => 'Shift start',
        'shift_end' => 'Shift end',
    ],

    'history' => [
        'title' => 'History',
        'created' => 'Created by :user',
        'updated' => 'Updated by :user',
        'system' => 'the system',
        'empty' => 'No change recorded yet.',
    ],

    'errors' => [
        403 => [
            'title' => 'Access denied',
            'message' => 'You don\'t have permission to access this page.',
        ],
        404 => [
            'title' => 'Page not found',
            'message' => 'The page you are looking for does not exist or has been moved.',
        ],
        419 => [
            'title' => 'Session expired',
            'message' => 'Your session has expired. Please reload the page and try again.',
        ],
        429 => [
            'title' => 'Too many requests',
            'message' => 'Please wait a moment before trying again.',
        ],
        500 => [
            'title' => 'Server error',
            'message' => 'Something went wrong on our side. Please try again later.',
        ],
        503 => [
            'title' => 'Under maintenance',
            'message' => 'The application is temporarily unavailable. Please try again later.',
        ],
    ],
];
