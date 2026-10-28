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

    'connector_error_code' => [
        'timeout' => 'Timeout',
        'connection_refused' => 'Connection refused',
        'unauthorized' => 'Unauthorized',
        'invalid_response' => 'Invalid response',
        'parsing_failed' => 'Parsing failed',
    ],

    'rejection_reason' => [
        'missing_field' => 'Missing field',
        'invalid_value' => 'Invalid value',
        'duplicate_record' => 'Duplicate record',
        'unknown_vessel' => 'Unknown vessel',
        'out_of_zone' => 'Outside authorized zone',
    ],

    'rejection_status' => [
        'pending' => 'To review',
        'corrected' => 'Corrected',
        'dismissed' => 'Dismissed',
    ],

    'connector_comment_kind' => [
        'note' => 'Note',
        'status' => 'Situation update',
        'action' => 'Operation launched',
    ],

    'current_license_status' => [
        'not_provided' => 'Not provided',
        'active' => 'Active',
        'suspended' => 'Suspended',
        'expired' => 'Expired',
        'revoked' => 'Revoked',
    ],

    'user_type' => [
        'human' => 'Human',
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

    'institution_type' => [
        'rfmo' => 'RFMO',
        'rfb' => 'RFB',
        'fao' => 'FAO',
        'state' => 'State',
        'authority' => 'Authority',
        'other' => 'Other',
    ],

    'identifier_type' => [
        'imo' => 'IMO',
        'mmsi' => 'MMSI',
        'ircs' => 'Call sign (IRCS)',
        'registration' => 'Registration',
        'iccat' => 'ICCAT',
        'eu' => 'EU number',
        'beacon' => 'Beacon',
        'other' => 'Other mark',
    ],

    'source_system' => [
        'manual_entry' => 'Manual entry',
        'themis' => 'THEMIS',
        'neno' => 'NENO',
        'e_inspection' => 'e-inspection',
        'dpep' => 'DPEP',
        'mrep' => 'MREP',
    ],

    'matching_status' => [
        'matched' => 'Matched',
        'possible_duplicate' => 'Possible duplicate',
        'new' => 'New',
        'rejected' => 'Rejected',
    ],

    'matching_criterion' => [
        'imo' => 'IMO',
        'mmsi' => 'MMSI',
        'ircs' => 'Call sign (IRCS)',
        'registration' => 'Registration',
        'name_flag' => 'Name and flag',
    ],

    'actor_role' => [
        'owner' => 'Owner',
        'shipowner' => 'Shipowner',
        'beneficiary' => 'Beneficiary',
        'local_agent' => 'Local agent',
        'captain' => 'Captain',
        'fishing_captain' => 'Fishing captain',
    ],

    'zone_rule_segment' => [
        'artisanal' => 'Artisanal',
        'small_pelagics' => 'Small pelagics',
        'trawl' => 'Trawl',
        'tuna_vessel' => 'Tuna vessel',
    ],

    'external_list_type' => [
        'global_record' => 'Global record',
        'rfmo' => 'RFMO list',
        'inn' => 'IUU list',
    ],

    'record_status' => [
        'draft' => 'Draft',
        'to_match' => 'To match',
        'validated' => 'Validated',
    ],

    'fleet_segment' => [
        'patrol_vessel' => 'Patrol vessel',
        'industrial' => 'Industrial',
        'semi_industrial' => 'Semi-industrial',
        'artisanal' => 'Artisanal',
        'authorized_foreign' => 'Authorized foreign',
        'known_unauthorized' => 'Known, unauthorized',
    ],

    'fleet_type' => [
        'fishing' => 'Fishing',
        'support' => 'Support',
        'reefer' => 'Reefer',
        'other' => 'Other',
    ],

    'legal_status' => [
        'ivorian' => 'Ivorian',
        'chartered' => 'Chartered',
        'foreign' => 'Foreign',
    ],

    'duplicate_decision' => [
        'merge' => 'Merge into the existing record',
        'keep_separate' => 'Keep separate',
        'reject' => 'Reject',
    ],

    'operation_category_code' => [
        'vms' => 'VMS (beacons)',
        'ers' => 'ERS (electronic fishing logs)',
        'mep' => 'MEP (set-up)',
        'inn' => 'IUU (illegal fishing)',
        'div' => 'Miscellaneous',
    ],

    'operation_status' => [
        'open' => 'Open',
        'in_progress' => 'In progress',
        'closed' => 'Closed',
        'archived' => 'Archived',
    ],

    'logbook_day_status' => [
        'open' => 'Open',
        'closed' => 'Closed',
        'locked' => 'Locked',
    ],

    'logbook_direction' => [
        'received' => 'Received',
        'sent' => 'Sent',
    ],

    'logbook_handling_status' => [
        'to_handle' => 'To handle',
        'in_progress' => 'In progress',
        'closed' => 'Closed',
    ],

    'surveillance_observation_status' => [
        'monitoring' => 'Monitoring',
        'closed' => 'Closed',
        'operation' => 'Operation opened',
    ],

    'watch_origin' => [
        'director_list_themis' => 'Director\'s list (THEMIS)',
        'director_list_manual' => 'Director\'s list (manual entry)',
        'logbook' => 'Cited in the logbook',
        'eez_entry' => 'EEZ entry',
        'eez_exit' => 'EEZ exit',
        'enhanced_monitoring' => 'Enhanced monitoring',
        'more_than_3_checks' => 'More than 3 checks',
    ],

    'watch_status' => [
        'open' => 'Open',
        'closed' => 'Closed',
    ],
];
