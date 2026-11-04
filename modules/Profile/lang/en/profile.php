<?php

return [
    'title' => 'My profile',
    'subtitle' => 'My information, messages, alerts and pending activities',
    'link' => 'My profile',
    'empty' => 'Nothing to show for now.',
    'fields' => [
        'first_name' => 'First name',
        'name' => 'Last name',
        'job_title' => 'Job title',
        'role' => 'Role',
        'team' => 'Team',
        'email' => 'Email',
        'phone' => 'Phone',
        'whatsapp_number' => 'WhatsApp number',
    ],
    'photo' => [
        'field' => 'photo',
        'change' => 'Change the photo',
        'remove' => 'Remove the photo',
        'updated' => 'Your photo has been updated.',
        'removed' => 'Your photo has been removed.',
    ],
    'cards' => [
        'messages' => 'Unread messages',
        'urgencies' => 'Urgent items',
        'alerts' => 'Current alerts',
        'pending' => 'Pending activities',
    ],
    'columns' => [
        'date' => 'Date',
        'from' => 'From',
        'urgency' => 'Urgency',
        'vessel' => 'Vessel',
        'message' => 'Message',
        'kind' => 'Kind',
        'detail' => 'Detail',
        'category' => 'Category',
        'status' => 'Status',
    ],
    'messages' => [
        'title' => 'My messages',
        'unread' => '{0} No unread message|{1} :count unread message|[2,*] :count unread messages',
        'all' => 'See all messages',
    ],
    'urgencies' => [
        'title' => 'My urgent items',
        'hint' => 'Urgent alerts and unread urgent messages that concern me',
        'alert' => 'Alert',
        'message' => 'Message',
    ],
    'alerts' => [
        'title' => 'My alerts',
        'hint' => 'Current alerts sorted: urgent, useful for my tasks, other',
    ],
    'pending' => [
        'title' => 'My pending activities',
        'hint' => 'Observations to follow, messages to handle before a deadline and operations I opened',
        'observation' => 'Observation',
        'note' => 'Message to handle',
        'operation' => 'Operation',
    ],
];
