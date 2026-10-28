<?php

return [
    'vessel_category' => [
        'fishing' => 'Fishing vessel',
        'patrol' => 'Patrol boat',
    ],

    'vessel_type' => [
        'trawler' => 'Trawler',
        'seiner' => 'Seiner',
        'longliner' => 'Longliner',
        'canoe' => 'Canoe',
        'patrol_boat' => 'Patrol boat',
        'support' => 'Support vessel',
    ],

    'vessel_status' => [
        'active' => 'Active',
        'suspended' => 'Suspended',
        'inactive' => 'Inactive',
    ],

    'license_type' => [
        'industrial' => 'Industrial',
        'artisanal' => 'Artisanal',
    ],

    'license_status' => [
        'valid' => 'Valid',
        'expiring' => 'Expiring soon',
        'expired' => 'Expired',
        'suspended' => 'Suspended',
    ],

    'tracking_system' => [
        'vms' => 'VMS',
        'ais' => 'AIS',
    ],

    'beacon_status' => [
        'emitting' => 'Emitting',
        'silent' => 'Signal lost',
        'no_data' => 'No data',
    ],

    'zone_type' => [
        'port_area' => 'Port area',
        'territorial_sea' => 'Territorial sea',
        'exclusive_economic_zone' => 'Exclusive economic zone',
        'restricted' => 'Restricted area',
    ],

    'movement_direction' => [
        'entry' => 'Entry',
        'exit' => 'Exit',
    ],

    'observation_level' => [
        'info' => 'Information',
        'warning' => 'Warning',
        'critical' => 'Critical',
    ],

    'observation_status' => [
        'open' => 'Open',
        'in_progress' => 'In progress',
        'closed' => 'Closed',
    ],

    'inspection_type' => [
        'at_sea' => 'At sea',
        'at_port' => 'At port',
        'documentary' => 'Documentary',
    ],

    'inspection_result' => [
        'compliant' => 'Compliant',
        'minor_issues' => 'Minor issues',
        'non_compliant' => 'Non-compliant',
    ],

    'infraction_status' => [
        'reported' => 'Reported',
        'under_investigation' => 'Under investigation',
        'sanctioned' => 'Sanctioned',
        'closed' => 'Closed',
        'dismissed' => 'Dismissed',
    ],

    'catch_source' => [
        'logbook' => 'Logbook',
        'landing' => 'Landing',
        'observer' => 'Observer',
    ],

    'mission_type' => [
        'sea_patrol' => 'Sea patrol',
        'port_control' => 'Port control',
        'joint_operation' => 'Joint operation',
    ],

    'mission_status' => [
        'planned' => 'Planned',
        'in_progress' => 'In progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],

    'data_source_kind' => [
        'api' => 'Application / API',
        'database' => 'Database',
        'file' => 'Files',
    ],

    'data_source_status' => [
        'operational' => 'Operational',
        'degraded' => 'Degraded',
        'down' => 'Down',
    ],

    'user_kind' => [
        'human' => 'Person',
        'ai_agent' => 'AI agent',
        'system' => 'System',
    ],

    'custom_field_type' => [
        'text' => 'Text',
        'number' => 'Number',
        'date' => 'Date',
        'boolean' => 'Yes / no',
        'list' => 'List',
    ],

    'traffic_light' => [
        'compliant' => 'Compliant',
        'watch' => 'Watch',
        'suspect' => 'Suspect',
        'infringing' => 'Infringing',
    ],
];
