<?php

return [
    'title' => 'Permission groups',
    'subtitle' => 'Groups add permissions to users on top of their role',
    'list_title' => 'Groups',
    'new' => 'New group',
    'create_title' => 'New group',
    'edit_title' => 'Edit group',
    'name' => 'Group',
    'label' => 'Group name',
    'description' => 'Description',
    'permissions' => 'Permissions granted by the group',
    'members' => 'Members',
    'members_count' => 'Members',
    'permissions_count' => 'Permissions',
    'confirm_delete' => 'Delete the group :group?',
    'created' => 'The group has been created.',
    'updated' => 'The group has been updated.',
    'deleted' => 'The group has been deleted.',
    'cannot_delete_system' => 'A system group cannot be deleted.',
    'system' => [
        'fnp_license_management' => [
            'label' => 'FNP and license management',
            'description' => 'Records licenses, their dates, suspensions and revocations.',
        ],
        'infraction_investigation' => [
            'label' => 'Infraction investigation',
            'description' => 'Prepares and follows infraction cases.',
        ],
        'surveillance_opinion' => [
            'label' => 'Surveillance opinion',
            'description' => 'Gives the surveillance opinion that the manual assigned to the surveillance deputy director.',
        ],
        'automatic_dispatch' => [
            'label' => 'Automatic dispatch management',
            'description' => 'Sets the frequency and activation of automatic messages.',
        ],
        'trigger_ai_agent' => [
            'label' => 'Trigger the AI agent',
            'description' => 'May trigger the AI agent.',
        ],
    ],
];
