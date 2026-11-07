<?php

return [
    'kinds' => ['kpi' => 'Key figure', 'chart' => 'Chart', 'table' => 'Table'],
    'groups' => ['kpi' => 'Key figures', 'chart' => 'Charts', 'table' => 'Tables'],
    'unknown' => 'Not provided',
    'active_vessels' => [
        'title' => 'Active vessels',
        'explanation' => 'Number of registered vessels whose status is "active", wherever they are.',
    ],
    'pending_observations' => [
        'title' => 'Pending observations',
        'explanation' => 'Logbook observations that are not closed yet and await follow-up.',
    ],
    'zones' => [
        'title' => 'Situation by zone',
        'explanation' => 'Number of vessels currently in each port zone, based on their latest position.',
    ],
    'infractions_by_status' => [
        'title' => 'Infractions by status',
        'explanation' => 'Breakdown of infraction cases by progress, from opening to closure.',
    ],
    'inspections_monthly' => [
        'title' => 'Inspections over the last six months',
        'explanation' => 'Number of inspections carried out each month, from the e-inspection database.',
    ],
    'fleet_status_lights' => [
        'title' => 'Fleet traffic-light status',
        'explanation' => 'Breakdown of vessels between compliant, to watch, suspect and in violation.',
    ],
    'vessels_by_segment' => [
        'title' => 'Vessels by fleet segment',
        'explanation' => 'Breakdown of registered vessels by segment: industrial, semi-industrial, artisanal, authorized foreign, etc.',
    ],
    'vessels_by_flag' => [
        'title' => 'Vessels by flag',
        'explanation' => 'The eight most represented flags in the vessel register.',
    ],
    'licenses_by_status' => [
        'title' => 'Licenses by status',
        'explanation' => 'Breakdown of licenses between valid, expiring, expired, suspended and revoked.',
    ],
    'licenses_expiring_by_month' => [
        'title' => 'Upcoming license expiries',
        'explanation' => 'Number of licenses expiring each month over the next six months.',
    ],
    'alerts_by_status' => [
        'title' => 'Alerts by status',
        'explanation' => 'Vessel tracking alerts by handling status: open, acknowledged or closed.',
    ],
    'observations_by_level' => [
        'title' => 'Pending observations by level',
        'explanation' => 'Breakdown of unclosed observations by severity level.',
    ],
    'operations_by_status' => [
        'title' => 'Operations by status',
        'explanation' => 'Breakdown of operations by progress.',
    ],
    'missions_by_status' => [
        'title' => 'Missions by status',
        'explanation' => 'Breakdown of surveillance missions by state: preparation, in progress, completed, etc.',
    ],
    'data_sources' => [
        'title' => 'Data freshness',
        'explanation' => 'Last synchronization date of each data source, with the number of pending errors and rejections.',
    ],
    'pending_observation_list' => [
        'title' => 'Latest pending observations',
        'explanation' => 'The five most recent unclosed logbook observations.',
    ],
];
