<?php

return [
    'vessel_category' => [
        'fishing' => 'Navire de pêche',
        'patrol' => 'Vedette',
    ],

    'vessel_type' => [
        'trawler' => 'Chalutier',
        'seiner' => 'Senneur',
        'longliner' => 'Palangrier',
        'canoe' => 'Pirogue',
        'patrol_boat' => 'Vedette de surveillance',
        'support' => 'Navire d\'appui',
    ],

    'vessel_status' => [
        'active' => 'Actif',
        'suspended' => 'Suspendu',
        'inactive' => 'Inactif',
    ],

    'license_type' => [
        'industrial' => 'Industrielle',
        'artisanal' => 'Artisanale',
    ],

    'license_status' => [
        'valid' => 'Valide',
        'expiring' => 'Expire bientôt',
        'expired' => 'Expirée',
        'suspended' => 'Suspendue',
    ],

    'tracking_system' => [
        'vms' => 'VMS',
        'ais' => 'AIS',
    ],

    'beacon_status' => [
        'emitting' => 'Émet',
        'silent' => 'Rupture d\'émission',
        'no_data' => 'Aucune donnée',
    ],

    'zone_type' => [
        'port_area' => 'Zone portuaire',
        'territorial_sea' => 'Mer territoriale',
        'exclusive_economic_zone' => 'Zone économique exclusive',
        'restricted' => 'Zone réglementée',
    ],

    'movement_direction' => [
        'entry' => 'Entrée',
        'exit' => 'Sortie',
    ],

    'observation_level' => [
        'info' => 'Information',
        'warning' => 'Vigilance',
        'critical' => 'Critique',
    ],

    'observation_status' => [
        'open' => 'Ouverte',
        'in_progress' => 'En cours',
        'closed' => 'Clôturée',
    ],

    'inspection_type' => [
        'at_sea' => 'En mer',
        'at_port' => 'Au port',
        'documentary' => 'Documentaire',
    ],

    'inspection_result' => [
        'compliant' => 'Conforme',
        'minor_issues' => 'Écarts mineurs',
        'non_compliant' => 'Non conforme',
    ],

    'infraction_status' => [
        'reported' => 'Constatée',
        'under_investigation' => 'En instruction',
        'sanctioned' => 'Sanctionnée',
        'closed' => 'Classée',
        'dismissed' => 'Abandonnée',
    ],

    'catch_source' => [
        'logbook' => 'Journal de pêche',
        'landing' => 'Débarquement',
        'observer' => 'Observateur',
    ],

    'mission_type' => [
        'sea_patrol' => 'Patrouille en mer',
        'port_control' => 'Contrôle portuaire',
        'joint_operation' => 'Opération conjointe',
    ],

    'mission_status' => [
        'planned' => 'Planifiée',
        'in_progress' => 'En cours',
        'completed' => 'Terminée',
        'cancelled' => 'Annulée',
    ],

    'data_source_kind' => [
        'api' => 'Application / API',
        'database' => 'Base de données',
        'file' => 'Fichiers',
    ],

    'data_source_status' => [
        'operational' => 'Opérationnelle',
        'degraded' => 'Dégradée',
        'down' => 'Indisponible',
    ],
];
