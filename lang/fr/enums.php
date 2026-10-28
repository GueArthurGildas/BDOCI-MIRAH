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

    'connector_error_code' => [
        'timeout' => 'Délai dépassé',
        'connection_refused' => 'Connexion refusée',
        'unauthorized' => 'Accès refusé',
        'invalid_response' => 'Réponse invalide',
        'parsing_failed' => 'Lecture impossible',
    ],

    'rejection_reason' => [
        'missing_field' => 'Champ manquant',
        'invalid_value' => 'Valeur invalide',
        'duplicate_record' => 'Doublon',
        'unknown_vessel' => 'Navire inconnu',
        'out_of_zone' => 'Hors zone autorisée',
    ],

    'rejection_status' => [
        'pending' => 'À examiner',
        'corrected' => 'Corrigé',
        'dismissed' => 'Écarté',
    ],

    'connector_comment_kind' => [
        'note' => 'Note',
        'status' => 'Point de situation',
        'action' => 'Opération lancée',
    ],

    'current_license_status' => [
        'not_provided' => 'Non renseignée',
        'active' => 'Active',
        'suspended' => 'Suspendue',
        'expired' => 'Expirée',
        'revoked' => 'Révoquée',
    ],

    'user_type' => [
        'human' => 'Humain',
        'ai_agent' => 'Agent IA',
        'system' => 'Système',
    ],

    'custom_field_type' => [
        'text' => 'Texte',
        'number' => 'Nombre',
        'date' => 'Date',
        'boolean' => 'Oui / non',
        'list' => 'Liste',
    ],

    'institution_type' => [
        'rfmo' => 'ORGP',
        'rfb' => 'ORP',
        'fao' => 'FAO',
        'state' => 'État',
        'authority' => 'Autorité',
        'other' => 'Autre',
    ],

    'identifier_type' => [
        'imo' => 'IMO',
        'mmsi' => 'MMSI',
        'ircs' => 'Indicatif (IRCS)',
        'registration' => 'Immatriculation',
        'iccat' => 'ICCAT',
        'eu' => 'Numéro UE',
        'beacon' => 'Balise',
        'other' => 'Autre marque',
    ],

    'source_system' => [
        'manual_entry' => 'Saisie',
        'themis' => 'THEMIS',
        'neno' => 'NENO',
        'e_inspection' => 'e-inspection',
        'dpep' => 'DPEP',
        'mrep' => 'MREP',
    ],

    'matching_status' => [
        'matched' => 'Rapproché',
        'possible_duplicate' => 'Doublon possible',
        'new' => 'Nouveau',
        'rejected' => 'Rejeté',
    ],

    'matching_criterion' => [
        'imo' => 'IMO',
        'mmsi' => 'MMSI',
        'ircs' => 'Indicatif (IRCS)',
        'registration' => 'Immatriculation',
        'name_flag' => 'Nom et pavillon',
    ],

    'actor_role' => [
        'owner' => 'Propriétaire',
        'shipowner' => 'Armateur',
        'beneficiary' => 'Bénéficiaire',
        'local_agent' => 'Agent en Côte d\'Ivoire',
        'captain' => 'Capitaine',
        'fishing_captain' => 'Capitaine de pêche',
    ],

    'zone_rule_segment' => [
        'artisanal' => 'Pêche artisanale',
        'small_pelagics' => 'Petits pélagiques',
        'trawl' => 'Chalut',
        'tuna_vessel' => 'Thonier',
    ],

    'external_list_type' => [
        'global_record' => 'Fichier mondial',
        'rfmo' => 'Liste ORGP',
        'inn' => 'Liste INN',
    ],

    'record_status' => [
        'draft' => 'Brouillon',
        'to_match' => 'À rapprocher',
        'validated' => 'Validée',
    ],

    'fleet_segment' => [
        'patrol_vessel' => 'Vedette',
        'industrial' => 'Industriel',
        'semi_industrial' => 'Semi-industriel',
        'artisanal' => 'Artisanal',
        'authorized_foreign' => 'Étranger autorisé',
        'known_unauthorized' => 'Connu, non autorisé',
    ],

    'fleet_type' => [
        'fishing' => 'Pêche',
        'support' => 'Soutien',
        'reefer' => 'Reefer',
        'other' => 'Autre',
    ],

    'legal_status' => [
        'ivorian' => 'Ivoirien',
        'chartered' => 'Affrété',
        'foreign' => 'Étranger',
    ],

    'duplicate_decision' => [
        'merge' => 'Fusionner avec la fiche existante',
        'keep_separate' => 'Garder séparées',
        'reject' => 'Rejeter',
    ],
];
