<?php

return [
    'map' => [
        'title' => 'Situation map',
        'category' => 'Vessel category',
        'subtitle' => 'Last known position of each vessel and surveillance zones',
        'positions_count' => '{0} No vessel positioned|{1} :count vessel positioned|[2,*] :count vessels positioned',
        'legend' => [
            'emitting' => 'Beacon emitting',
            'silent' => 'Signal lost',
            'patrol' => 'Patrol boat',
        ],
    ],
    'zones' => [
        'title' => 'Situation by zone',
        'subtitle' => 'Number of vessels present in each zone at a given time',
        'situation_at' => 'Situation at :time',
        'at' => 'Date and time',
        'chart_title' => 'Vessels in port areas',
        'table_title' => 'All zones',
        'freshness_hint' => 'Only positions received in the previous :hours hours are counted.',
        'zone' => 'Zone',
        'type' => 'Type',
        'vessels' => 'Vessels',
    ],
    'beacons' => [
        'title' => 'Beacon status',
        'subtitle' => 'VMS and AIS emissions of installed beacons',
        'list_title' => 'Installed beacons',
        'threshold_hint' => 'A beacon is considered silent after :hours hours without emission.',
        'vessel' => 'Vessel',
        'system' => 'System',
        'identifier' => 'Identifier',
        'last_emission' => 'Last emission',
        'zone' => 'Zone',
        'status' => 'Status',
        'never' => 'Never',
    ],
];
