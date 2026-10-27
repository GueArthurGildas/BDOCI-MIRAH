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
    'notifications' => 'Notifications',
    'fullscreen' => 'Full screen',
    'guide_in_progress' => 'Guide in progress',
    'user_guide' => 'User guide',
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
        'supervisor' => 'CSP supervisor',
        'surveillance_agent' => 'Surveillance agent',
        'inspector' => 'Inspector',
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
