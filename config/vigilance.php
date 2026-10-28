<?php

return [
    'status_light' => [
        'license_expiring_days' => 30,
        'unexplained_variance_days' => 30,
        'checks_before_suspect' => (int) env('SURVEILLANCE_WATCH_CHECK_THRESHOLD', 3),
    ],

    'score' => [
        'window_months' => 12,
        'maximum' => 100,
        'levels' => ['moderate' => 25, 'high' => 50],
        'components' => [
            'silence_now' => ['points' => 20, 'cap' => 20],
            'silences_12_months' => ['points' => 5, 'cap' => 15],
            'license_blocked' => ['points' => 20, 'cap' => 20],
            'license_expiring' => ['points' => 10, 'cap' => 10],
            'open_infractions' => ['points' => 15, 'cap' => 30],
            'infractions_12_months' => ['points' => 5, 'cap' => 15],
            'checks' => ['points' => 10, 'cap' => 10],
            'unexplained_variances' => ['points' => 10, 'cap' => 20],
            'unanswered_messages' => ['points' => 3, 'cap' => 9],
            'no_inspection' => ['points' => 10, 'cap' => 10],
        ],
    ],
];
