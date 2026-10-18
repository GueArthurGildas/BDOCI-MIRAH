<?php

return [
    'users' => [
        'title' => 'Users',
        'subtitle' => 'Accounts, roles and access',
        'list_title' => 'User accounts',
        'create_title' => 'New user',
        'edit_title' => 'Edit user',
        'new' => 'New user',
        'created' => 'The user has been created.',
        'updated' => 'The user has been updated.',
        'active' => 'Active',
        'inactive' => 'Deactivated',
        'password_hint' => 'Leave empty to keep the current password.',
        'cannot_deactivate_self' => 'You cannot deactivate your own account.',
        'search_placeholder' => 'Name or email…',
        'fields' => [
            'name' => 'Name',
            'email' => 'Email',
            'team' => 'Team',
            'role' => 'Role',
            'is_active' => 'Account active',
            'password' => 'Password',
            'password_confirmation' => 'Password confirmation',
            'status' => 'Status',
        ],
    ],
    'roles' => [
        'title' => 'Roles and permissions',
        'subtitle' => 'Permissions granted to each role, by module and action',
        'permission' => 'Permission',
    ],
    'permissions' => [
        'dashboard' => [
            'view' => 'View the dashboard',
        ],
        'vessels' => [
            'view' => 'View vessels',
            'manage' => 'Manage vessels',
        ],
        'licenses' => [
            'view' => 'View licenses',
            'manage' => 'Manage licenses',
        ],
        'tracking' => [
            'view' => 'View tracking',
        ],
        'logbook' => [
            'view' => 'View the logbook',
            'create' => 'Record observations',
            'manage' => 'Follow up observations',
        ],
        'movements' => [
            'view' => 'View movements',
            'create' => 'Record movements',
        ],
        'inspections' => [
            'view' => 'View inspections',
        ],
        'infractions' => [
            'view' => 'View infractions',
            'manage' => 'Manage infractions',
        ],
        'catches' => [
            'view' => 'View catches',
        ],
        'missions' => [
            'view' => 'View missions',
            'manage' => 'Manage missions',
        ],
        'users' => [
            'manage' => 'Manage users',
        ],
        'connectors' => [
            'view' => 'View connectors',
        ],
        'audit' => [
            'view' => 'View the audit log',
        ],
    ],
    'connectors' => [
        'title' => 'Connectors',
        'subtitle' => 'State of data sources, last synchronizations, errors and rejections',
        'kind' => 'Kind',
        'last_synced_at' => 'Last synchronization',
        'errors' => 'Errors',
        'rejected' => 'Rejected records',
        'never' => 'Never',
    ],
    'audit' => [
        'title' => 'Audit log',
        'subtitle' => 'Sign-ins, changes and administration actions',
        'list_title' => 'Events',
        'date' => 'Date',
        'user' => 'User',
        'event' => 'Event',
        'subject' => 'Record',
        'details' => 'Details',
        'from' => 'From',
        'to' => 'To',
        'changed_fields' => 'Changed fields: :fields',
        'system' => 'System',
        'events' => [
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'restored' => 'Restored',
            'login' => 'Sign-in',
            'logout' => 'Sign-out',
            'login_failed' => 'Failed sign-in',
            'role_changed' => 'Role changed',
        ],
        'subjects' => [
            'vessel' => 'Vessel',
            'license' => 'License',
            'tracking_device' => 'Beacon',
            'movement' => 'Movement',
            'observation' => 'Observation',
            'inspection' => 'Inspection',
            'infraction' => 'Infraction',
            'mission' => 'Mission',
            'user' => 'User',
        ],
    ],
];
