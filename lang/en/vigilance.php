<?php

return [
    'reasons' => [
        'open_infractions' => ':count open infraction(s)',
        'license_expired' => 'License expired',
        'license_suspended' => 'License suspended',
        'license_revoked' => 'License revoked',
        'beacon_silent' => 'Beacon silent',
        'unexplained_variances' => ':count unexplained EEZ declaration variance(s)',
        'repeated_checks' => ':count checks without outcome',
        'vms_failure' => 'Beacon failure ongoing',
        'enhanced_monitoring' => 'Enhanced monitoring',
        'license_expiring' => 'License expiring in :days day(s)',
    ],
    'score' => [
        'levels' => [
            'low' => 'Low',
            'moderate' => 'Moderate',
            'high' => 'High',
        ],
        'components' => [
            'silence_now' => 'VMS silence ongoing',
            'silences_12_months' => 'VMS silences over 12 months',
            'license_blocked' => 'License expired, suspended or revoked',
            'license_expiring' => 'License close to expiry',
            'open_infractions' => 'Open infractions',
            'infractions_12_months' => 'Infractions over 12 months',
            'checks' => 'Repeated checks',
            'unexplained_variances' => 'Unexplained EEZ variances',
            'unanswered_messages' => 'Unanswered messages',
            'no_inspection' => 'Vessel seen but not inspected over 12 months',
        ],
    ],
];
