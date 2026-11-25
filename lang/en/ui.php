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
    'refresh' => 'Refresh the page',
    'guide_hint' => 'A quick tour of this page',
    'user_guide' => 'User guide',
    'results_count' => '{0} No results|{1} :count result|[2,*] :count results',
    'no_results' => 'No results.',
    'select_placeholder' => 'Select…',
    'filter_placeholder' => 'Filter…',
    'per_page' => 'Show',
    'per_page_entries' => 'entries',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'create' => 'Create',
    'edit' => 'Edit',
    'view' => 'View',
    'back' => 'Back',
    'close' => 'Close',
    'required_fields' => 'Required fields',
    'open_full_page' => 'Open as full page',
    'form_error' => 'The form contains errors.',
    'form_failed' => 'Saving failed. Try again or open the form as a full page.',
    'back_home' => 'Back to home',
    'actions' => 'Actions',
    'all' => 'All',
    'yes' => 'Yes',
    'no' => 'No',
    'account_deactivated' => 'Your account has been deactivated. Contact an administrator.',
    'saved' => 'Changes saved.',
    'updated_at' => 'Updated at :time',

    'morning_menu' => [
        'title' => 'Morning board',
        'hint' => 'The screens of a CSP watch day, in working order',
        'sections' => [
            'shift' => 'Taking the watch',
            'watch' => 'Monitoring and alerts',
            'record' => 'Recording information',
            'operations' => 'Operations and exchanges',
            'follow_up' => 'Follow-up and decisions',
        ],
        'links' => [
            'handover' => 'Shift handover',
            'today' => 'Today\'s logbook',
            'new_operation' => '+ New operation',
        ],
    ],
    'navigation_folders' => [
        'e_inspection' => 'e-inspection',
        'morning_board' => 'Morning board',
        'logbook' => 'Logbook',
    ],

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
        'agent' => 'Agent',
    ],

    'role_descriptions' => [
        'admin' => 'Manages accounts, roles, settings, connectors and the audit log. Takes no business decision (requirement S-004).',
        'director' => 'Sees everything, including the fleet view, and takes the final decisions: port refusal, license suspension or revocation, precautionary measures.',
        'team_lead' => 'Validates the agents\' entries, runs the shift team and closes operations. Equivalent of the former « CSP supervisor » role.',
        'agent' => 'CSP operator or inspector: records observations, movements and inspections, and consults vessel files. An inspector is an agent holding an inspector accreditation.',
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

    'popup' => [
        'done' => 'Done',
        'continue' => 'Continue',
    ],

    'confirm' => [
        'title' => 'Confirmation',
        'proceed' => 'Yes, continue',
        'delete' => 'Yes, delete',
        'remove' => 'Yes, remove',
        'record_title' => 'Confirm the record',
        'record' => 'Check the information entered before saving it.',
        'record_label' => 'Yes, save',
    ],

    'health' => [
        'title' => 'System health',
        'message' => '{1} :count data source needs attention:|[2,*] :count data sources need attention:',
        'details' => 'See the connectors',
    ],
    'silence_presets' => [
        '2_days' => '2 days',
        '3_days' => '3 days',
        '4_days' => '4 days',
        '5_days' => '5 days',
        '1_week' => '1 week',
        '2_weeks' => '2 weeks',
        '1_month' => '1 month',
    ],
    'emission_gap' => [
        'days_hours' => ':days d :hours h',
        'days' => ':days d',
        'hours' => ':hours h',
        'minutes' => ':minutes min',
    ],
    'pager' => [
        'previous' => 'Previous page',
        'next' => 'Next page',
        'range' => '{from} to {to} of {total}',
    ],
    'notions' => [
        'title' => 'Alert, communication or notification?',
        'alert' => [
            'name' => 'Alert',
            'text' => 'a report raised automatically by a surveillance rule (beacon silence, restricted zone, speed). It needs handling and stays open until closed.',
        ],
        'communication' => [
            'name' => 'Communication',
            'text' => 'a message sent to a third party (owner, agent, institution) by e-mail or WhatsApp, or a note exchanged between agents. It keeps a record of the sending and the reply.',
        ],
        'notification' => [
            'name' => 'Notification',
            'text' => 'a reminder shown in the application (the bell) to tell you that an event concerns you. It is read then goes away; it replaces neither the alert nor the message.',
        ],
    ],
    'deletion' => [
        'title' => 'Delete this record',
        'button' => 'Delete',
        'reason' => 'Reason for deletion',
        'reason_placeholder' => 'Why delete? (required)',
        'hint' => 'The record leaves the lists; its logbook line stays visible, struck through, with your name and the reason.',
        'confirm' => 'Delete this record? It will leave the lists and its logbook line will be struck through.',
        'done' => 'The record has been deleted.',
        'day_closed' => 'The daily logbook of that day is closed: deletion is no longer possible, use a rectifying line.',
        'linked_entry' => 'This line comes from another record (observation, check, witness report or operation): delete the original record.',
        'cancelled' => 'Deleted by :name on :date: :reason',
    ],
];
