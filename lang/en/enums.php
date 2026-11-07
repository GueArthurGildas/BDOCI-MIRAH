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

    'license_status' => [
        'valid' => 'Valid',
        'expiring' => 'Expiring soon',
        'expired' => 'Expired',
        'suspended' => 'Suspended',
        'revoked' => 'Revoked',
    ],

    'tracking_system' => [
        'vms' => 'VMS',
        'ais' => 'AIS',
        'sat_ais' => 'Sat-AIS',
        'manual' => 'Manual report',
        'radar' => 'Radar',
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

    'catch_source' => [
        'logbook' => 'Logbook',
        'landing' => 'Landing',
        'observer' => 'Observer',
    ],

    'mission_type' => [
        'maritime' => 'Maritime',
        'lagoon' => 'Lagoon',
        'continental' => 'Continental',
        'port' => 'Port',
        'aerial_drone' => 'Aerial or drone',
    ],

    'mission_status' => [
        'scheduled' => 'Scheduled',
        'authorized' => 'Authorized',
        'in_progress' => 'In progress',
        'completed' => 'Completed',
        'closed' => 'Closed',
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

    'report_frequency' => [
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'yearly' => 'Yearly',
    ],

    'report_format' => [
        'pdf' => 'PDF',
        'excel' => 'Excel',
    ],

    'export_format' => [
        'csv' => 'CSV',
        'pdf' => 'PDF',
    ],

    'data_source_mode' => [
        'api' => 'API',
        'import' => 'Controlled import',
        'scraping' => 'Scraping',
        'direct_read' => 'Direct database read',
    ],

    'data_source_indicator' => [
        'green' => 'Operational',
        'orange' => 'Degraded',
        'red' => 'Down',
    ],

    'external_link_source' => [
        'themis' => 'THEMIS',
        'neno' => 'NENO',
        'e_inspection' => 'e-inspection',
    ],

    'mrep_purpose' => [
        'lan' => 'Landing',
        'trx' => 'Transshipment',
        'pro' => 'Processing',
        'other' => 'Other',
    ],

    'entry_decision' => [
        'authorization' => 'Authorization',
        'refusal' => 'Refusal',
        'inspection_only' => 'Inspection only',
    ],

    'inspection_location_type' => [
        'port' => 'In port',
        'sea' => 'At sea',
    ],

    'captain_signature_status' => [
        'signed' => 'Signed',
        'refused' => 'Refused',
        'absent' => 'Captain absent',
    ],

    'inspection_document_type' => [
        'license' => 'License',
        'certificate' => 'Certificate',
        'fishing_log' => 'Fishing log',
        'hold_plan' => 'Hold plan',
        'other' => 'Other',
    ],

    'mirror_origin' => [
        'e_inspection' => 'e-inspection',
        'fallback_entry' => 'Fallback entry',
    ],

    'license_segment' => [
        'artisanal' => 'Artisanal',
        'semi_industrial' => 'Semi-industrial',
        'industrial' => 'Industrial',
    ],

    'license_record_status' => [
        'active' => 'Active',
        'suspended' => 'Suspended',
        'expired' => 'Expired',
        'revoked' => 'Revoked',
    ],

    'license_origin' => [
        'import' => 'File import',
        'batch' => 'Batch entry',
        'manual' => 'Manual entry',
    ],

    'suspension_reason' => [
        'force_majeure' => 'Force majeure',
        'stocks' => 'Stocks',
        'art_105' => 'Article 105',
        'art_106' => 'Article 106',
        'vms_overdue' => 'VMS repair overdue',
        'other' => 'Other',
    ],

    'fnp_registration' => [
        'registered' => 'Registered',
        'deregistered' => 'Deregistered',
    ],

    'fnp_deregistration_reason' => [
        'destruction' => 'Destruction',
        'unauthorized_modification' => 'Unauthorized modification',
        'flag_change' => 'Flag change',
        'inn_list' => 'IUU list',
        'five_years_without_license' => 'Five years without a license',
    ],

    'license_import_status' => [
        'preview' => 'Preview',
        'valid' => 'Validated',
        'cancelled' => 'Cancelled',
    ],

    'license_import_line_status' => [
        'to_validate' => 'To validate',
        'valid' => 'Validated',
        'rejected' => 'Rejected',
    ],

    'beacon_modem' => [
        'gnss' => 'GNSS',
        'satellite' => 'Satellite',
        'gprs' => 'GPRS',
    ],

    'slot_test_result' => [
        'ok' => 'OK',
        'ko' => 'KO',
        'to_confirm' => 'To confirm',
    ],

    'slot_result' => [
        'normal' => 'Normal',
        'suspect' => 'Suspect',
    ],

    'position_loading_source' => [
        'api' => 'API',
        'import' => 'Import',
        'scraping' => 'Scraping',
    ],

    'alert_rule_type' => [
        'geofencing' => 'Geofencing',
        'silence' => 'Silence',
        'speed' => 'Speed',
        'zone' => 'Zone',
        'other' => 'Other',
    ],

    'alert_status' => [
        'open' => 'Open',
        'acknowledged' => 'Acknowledged',
        'closed' => 'Closed',
    ],

    'vms_failure_category' => [
        'foreign' => 'Foreign vessel',
        'ivorian' => 'Ivorian vessel',
        'chartered' => 'Chartered vessel',
        'tuna_vessel' => 'Tuna vessel',
    ],

    'sweep_anomaly_type' => [
        'non_emitting' => 'Not emitting',
        'licensed_absent_themis' => 'Licensed but absent from THEMIS',
        'other' => 'Other',
    ],

    'ers_source' => [
        'import' => 'Import',
        'api' => 'API',
        'manual_entry' => 'Manual entry',
    ],

    'vms_request_status' => [
        'requested' => 'Requested',
        'assessed' => 'Assessed',
        'closed' => 'Closed',
    ],

    'beacon_request_status' => [
        'requested' => 'Requested',
        'installed' => 'Installed',
        'closed' => 'Closed',
    ],

    'assessment_result' => [
        'compatible' => 'Compatible',
        'incompatible' => 'Incompatible',
        'to_complete' => 'To complete',
    ],
    'vessel_status_light' => [
        'compliant' => 'Compliant',
        'to_watch' => 'To watch',
        'suspect' => 'Suspect',
        'in_violation' => 'In violation',
    ],

    'witness_category' => [
        'state_vessel' => 'State vessel',
        'fisher' => 'Fisher',
        'coaster' => 'Coaster',
        'port_authority' => 'Port authority',
        'other' => 'Other',
    ],

    'report_channel' => [
        'email' => 'Email',
        'fax' => 'Fax',
        'phone' => 'Phone',
        'mail' => 'Mail',
        'form' => 'Paper or PDF form copied',
    ],

    'witness_report_status' => [
        'received' => 'Received',
        'verified' => 'Verified',
        'closed' => 'Closed',
        'forwarded' => 'Forwarded',
    ],

    'report_decision_type' => [
        'dismissal' => 'Dismissal',
        'forward_to_instruction' => 'Forward to instruction',
    ],

    'infraction_qualification' => [
        'presumed' => 'Presumed',
        'established' => 'Established',
    ],

    'infraction_case_status' => [
        'open' => 'Open',
        'instruction' => 'Under instruction',
        'decision' => 'Decision',
        'closed' => 'Closed',
    ],

    'precautionary_measure_type' => [
        'immobilization' => 'Immobilization',
        'port_services_refusal' => 'Port services refusal',
        'gear_seizure' => 'Gear seizure',
        'catch_seizure' => 'Catch seizure',
        'license_suspension' => 'License suspension',
        'license_withdrawal' => 'License withdrawal',
        'diversion' => 'Diversion',
        'seals' => 'Seals',
        'cessation_order' => 'Cessation order',
    ],

    'seizure_decision' => [
        'return' => 'Return',
        'destruction' => 'Destruction',
        'confiscation' => 'Confiscation',
    ],

    'follow_up_type' => [
        'warning' => 'Warning',
        'administrative_penalty' => 'Administrative penalty',
        'settlement' => 'Settlement',
        'forward_to_hierarchy' => 'Forward to hierarchy',
        'forward_to_court' => 'Forward to court',
    ],

    'infraction_severity' => [
        'minor' => 'Minor',
        'serious' => 'Serious',
        'very_serious' => 'Very serious',
    ],

    'mission_nature' => [
        'internal' => 'Internal',
        'joint' => 'Joint',
    ],

    'asset_type' => [
        'intervention_boat' => 'Intervention boat',
        'patrol_boat' => 'Patrol boat',
        'vehicle' => 'Vehicle',
        'drone' => 'Drone',
        'vhf' => 'VHF',
        'gps' => 'GPS',
        'inspection_equipment' => 'Inspection equipment',
        'seals' => 'Seals',
    ],

    'observed_unit_level' => [
        'sighted' => 'Sighted',
        'approached' => 'Approached',
        'inspected' => 'Inspected',
    ],

    'sighting_source' => [
        'visual' => 'Visual',
        'radar' => 'Radar',
        'vms' => 'VMS',
    ],

    'authorizing_role' => [
        'director' => 'Director',
        'team_lead' => 'Team lead',
    ],

    'mission_checklist_item' => [
        'fuel' => 'Fuel on board',
        'radio' => 'VHF radio checked',
        'gps' => 'GPS working',
        'inspection_kit' => 'Inspection equipment',
        'seals' => 'Seals available',
        'safety' => 'Safety equipment',
        'documents' => 'Mission order and documents',
    ],

    'investigation_element' => [
        'photos' => 'Photos',
        'themis' => 'THEMIS records',
        'global_record' => 'Global Record',
        'rfmo_lists' => 'RFMO and IUU lists',
        'trajectories' => 'Trajectories',
        'vms_extract' => 'VMS extract',
        'press' => 'Press',
    ],

    'descriptions' => [
        'alert_category' => [
            'urgent' => 'Open restricted-zone alerts, or alerts on a suspect vessel or one in violation.',
            'useful' => 'Current alerts on a vessel you follow: on watch, in one of your operations, or that you acknowledged.',
            'other' => 'The other alerts.',
        ],
        'vessel_status' => [
            'active' => 'The vessel is in service and can be inspected and tracked.',
            'suspended' => 'The vessel is temporarily banned from operating.',
            'inactive' => 'The vessel is no longer in operation.',
        ],
        'vessel_category' => [
            'fishing' => 'Fishing or fishing support vessel.',
            'patrol' => 'Administration surveillance vessel.',
        ],
        'vessel_status_light' => [
            'compliant' => 'No adverse fact is recorded for this vessel.',
            'to_watch' => 'A fact deserves attention: silent beacon, expiring license or ongoing observation.',
            'suspect' => 'An element is considered suspect by the teams: the vessel must be checked.',
            'in_violation' => 'An ongoing infraction is recorded against this vessel.',
        ],
        'beacon_status' => [
            'emitting' => 'The beacon emitted within the expected delay.',
            'silent' => 'The beacon has not emitted for longer than the silence threshold.',
            'no_data' => 'No position has ever been received for this vessel.',
        ],
        'license_status' => [
            'valid' => 'The license is valid.',
            'expiring' => 'The license expires within the warning period.',
            'expired' => 'The license expiry date has passed.',
            'suspended' => 'The license is suspended: fishing is banned during the suspension.',
            'revoked' => 'The license has been permanently withdrawn.',
        ],
        'current_license_status' => [
            'not_provided' => 'No license is recorded for this vessel.',
            'active' => 'The vessel holds a valid license.',
            'suspended' => 'The vessel license is suspended.',
            'expired' => 'The vessel license has expired.',
            'revoked' => 'The vessel license has been withdrawn.',
        ],
        'alert_status' => [
            'open' => 'The alert has not been handled yet.',
            'acknowledged' => 'The alert has been seen and is being handled.',
            'closed' => 'The alert is handled or its cause has disappeared.',
        ],
        'infraction_case_status' => [
            'open' => 'The case has just been opened.',
            'instruction' => 'The case is under investigation: evidence and hearings.',
            'decision' => 'The case is awaiting or subject to a decision.',
            'closed' => 'The case is closed.',
        ],
        'observation_level' => [
            'info' => 'Information for the record.',
            'warning' => 'Point of attention to follow.',
            'critical' => 'Serious situation requiring quick action.',
        ],
        'observation_status' => [
            'open' => 'The observation is open and has no follow-up yet.',
            'in_progress' => 'The observation is being handled.',
            'closed' => 'The observation is closed.',
        ],
        'operation_status' => [
            'open' => 'The operation is open.',
            'in_progress' => 'The operation is in progress.',
            'closed' => 'The operation is over.',
            'archived' => 'The operation is archived and can no longer be changed.',
        ],
        'mission_status' => [
            'scheduled' => 'The mission is scheduled.',
            'authorized' => 'The mission is authorized and can start.',
            'in_progress' => 'The mission is in progress.',
            'completed' => 'The mission is over, the report remains to be written.',
            'closed' => 'The mission is closed.',
        ],
        'movement_direction' => [
            'entry' => 'The vessel entered the port.',
            'exit' => 'The vessel left the port.',
        ],
    ],
    'eez_check_result' => [
        'ok' => 'Compliant',
        'ko' => 'Non-compliant',
        'na' => 'Not applicable',
    ],
    'eez_check_type' => [
        'vms' => 'VMS beacon',
        'ais' => 'AIS signal',
        'license' => 'License',
        'designated_port' => 'Designated port',
        'dpep_72h' => '72-hour port notice',
        'access' => 'Port access',
        'inn_lists' => 'IUU fishing lists',
        'consistency' => 'Declaration consistency',
    ],
    'eez_declaration_status' => [
        'received' => 'Received',
        'verified' => 'Verified',
        'acknowledged' => 'Acknowledged',
        'variance' => 'Variance found',
        'closed' => 'Closed',
    ],
    'eez_declaration_type' => [
        'entry' => 'EEZ entry',
        'exit' => 'EEZ exit',
    ],
    'eez_entry_origin' => [
        'manual' => 'Manual entry',
        'scraping' => 'Automatic collection',
    ],
    'eez_reason' => [
        'eez_transit' => 'EEZ transit',
        'eez_fishing' => 'Fishing in the EEZ',
        'ivorian_port_transit' => 'Transit to an Ivorian port',
        'high_seas_fishing' => 'High seas fishing',
        'foreign_eez_fishing' => 'Fishing in a foreign EEZ',
    ],
    'entry_exit_alert_status' => [
        'open' => 'Open',
        'closed' => 'Closed',
    ],
    'entry_exit_explanation' => [
        'catch_document' => 'Catch document',
        'transshipment' => 'Transshipment',
        'landing' => 'Landing',
        'unexplained' => 'Unexplained',
    ],
    'alert_category' => [
        'urgent' => 'Urgent',
        'useful' => 'Useful for my tasks',
        'other' => 'Other alerts',
    ],
];
