<?php

return [
    'title' => 'Dashboard',
    'subtitle' => 'Key indicators of fisheries surveillance',
    'stats' => [
        'active_vessels' => 'Active vessels',
        'expiring_licenses' => 'Licenses expiring soon',
        'pending_observations' => 'Pending observations',
        'silent_beacons' => 'Beacons with signal lost',
    ],
    'zones_title' => 'Situation by zone',
    'zones_subtitle' => 'Vessels present in port areas at :time',
    'infractions_title' => 'Infractions by status',
    'inspections_title' => 'Inspections over the last six months',
    'sources_title' => 'Data freshness',
    'observations_title' => 'Latest pending observations',
    'columns' => [
        'source' => 'Source',
        'last_synced_at' => 'Last synchronization',
        'errors' => 'Errors',
        'rejected' => 'Rejected',
        'status' => 'Status',
        'date' => 'Date',
        'vessel' => 'Vessel',
        'level' => 'Level',
        'description' => 'Description',
    ],
    'never' => 'Never',
];
