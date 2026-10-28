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

    'license_status' => [
        'valid' => 'Valide',
        'expiring' => 'Expire bientôt',
        'expired' => 'Expirée',
        'suspended' => 'Suspendue',
        'revoked' => 'Révoquée',
    ],

    'tracking_system' => [
        'vms' => 'VMS',
        'ais' => 'AIS',
        'sat_ais' => 'Sat-AIS',
        'manual' => 'Rapport manuel',
        'radar' => 'Radar',
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

    'operation_category_code' => [
        'vms' => 'VMS (balises)',
        'ers' => 'ERS (journaux de pêche électroniques)',
        'mep' => 'MEP (mise en place)',
        'inn' => 'INN (pêche illicite)',
        'div' => 'Divers',
    ],

    'operation_status' => [
        'open' => 'Ouverte',
        'in_progress' => 'En cours',
        'closed' => 'Clôturée',
        'archived' => 'Archivée',
    ],

    'logbook_day_status' => [
        'open' => 'Ouverte',
        'closed' => 'Close',
        'locked' => 'Verrouillée',
    ],

    'logbook_direction' => [
        'received' => 'Reçu',
        'sent' => 'Émis',
    ],

    'logbook_handling_status' => [
        'to_handle' => 'À traiter',
        'in_progress' => 'En cours',
        'closed' => 'Clos',
    ],

    'surveillance_observation_status' => [
        'monitoring' => 'En suivi',
        'closed' => 'Clôturée',
        'operation' => 'Opération ouverte',
    ],

    'watch_origin' => [
        'director_list_themis' => 'Liste du Directeur (THEMIS)',
        'director_list_manual' => 'Liste du Directeur (saisie)',
        'logbook' => 'Cité en main courante',
        'eez_entry' => 'Entrée en ZEE',
        'eez_exit' => 'Sortie de ZEE',
        'enhanced_monitoring' => 'Suivi renforcé',
        'more_than_3_checks' => 'Plus de 3 vérifications',
    ],

    'watch_status' => [
        'open' => 'Ouvert',
        'closed' => 'Clos',
    ],

    'report_frequency' => [
        'weekly' => 'Hebdomadaire',
        'monthly' => 'Mensuel',
        'yearly' => 'Annuel',
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
        'import' => 'Import contrôlé',
        'scraping' => 'Scraping',
        'direct_read' => 'Lecture directe de la base',
    ],

    'data_source_indicator' => [
        'green' => 'Opérationnelle',
        'orange' => 'Dégradée',
        'red' => 'Indisponible',
    ],

    'external_link_source' => [
        'themis' => 'THEMIS',
        'neno' => 'NENO',
        'e_inspection' => 'e-inspection',
    ],

    'mrep_purpose' => [
        'lan' => 'Débarquement',
        'trx' => 'Transbordement',
        'pro' => 'Transformation',
        'other' => 'Autre',
    ],

    'entry_decision' => [
        'authorization' => 'Autorisation',
        'refusal' => 'Refus',
        'inspection_only' => 'Inspection seule',
    ],

    'inspection_location_type' => [
        'port' => 'Au port',
        'sea' => 'En mer',
    ],

    'captain_signature_status' => [
        'signed' => 'Signé',
        'refused' => 'Refusé',
        'absent' => 'Capitaine absent',
    ],

    'inspection_document_type' => [
        'license' => 'Licence',
        'certificate' => 'Certificat',
        'fishing_log' => 'Journal de pêche',
        'hold_plan' => 'Plan de cale',
        'other' => 'Autre',
    ],

    'mirror_origin' => [
        'e_inspection' => 'e-inspection',
        'fallback_entry' => 'Saisie de repli',
    ],

    'license_segment' => [
        'artisanal' => 'Artisanale',
        'semi_industrial' => 'Semi-industrielle',
        'industrial' => 'Industrielle',
    ],

    'license_record_status' => [
        'active' => 'Active',
        'suspended' => 'Suspendue',
        'expired' => 'Expirée',
        'revoked' => 'Révoquée',
    ],

    'license_origin' => [
        'import' => 'Import de fichier',
        'batch' => 'Saisie par lot',
        'manual' => 'Saisie manuelle',
    ],

    'suspension_reason' => [
        'force_majeure' => 'Force majeure',
        'stocks' => 'Stocks',
        'art_105' => 'Article 105',
        'art_106' => 'Article 106',
        'vms_overdue' => 'Réparation VMS hors délai',
        'other' => 'Autre',
    ],

    'fnp_registration' => [
        'registered' => 'Inscrit',
        'deregistered' => 'Radié',
    ],

    'fnp_deregistration_reason' => [
        'destruction' => 'Destruction',
        'unauthorized_modification' => 'Modification non autorisée',
        'flag_change' => 'Changement de pavillon',
        'inn_list' => 'Liste INN',
        'five_years_without_license' => 'Cinq ans sans licence',
    ],

    'license_import_status' => [
        'preview' => 'Aperçu',
        'valid' => 'Validé',
        'cancelled' => 'Annulé',
    ],

    'license_import_line_status' => [
        'to_validate' => 'À valider',
        'valid' => 'Validée',
        'rejected' => 'Rejetée',
    ],

    'beacon_modem' => [
        'gnss' => 'GNSS',
        'satellite' => 'Satellite',
        'gprs' => 'GPRS',
    ],

    'slot_test_result' => [
        'ok' => 'OK',
        'ko' => 'KO',
        'to_confirm' => 'À confirmer',
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
        'geofencing' => 'Géorepérage',
        'silence' => 'Silence',
        'speed' => 'Vitesse',
        'zone' => 'Zone',
        'other' => 'Autre',
    ],

    'alert_status' => [
        'open' => 'Ouverte',
        'acknowledged' => 'Acquittée',
        'closed' => 'Close',
    ],

    'vms_failure_category' => [
        'foreign' => 'Navire étranger',
        'ivorian' => 'Navire ivoirien',
        'chartered' => 'Navire affrété',
        'tuna_vessel' => 'Thonier',
    ],

    'sweep_anomaly_type' => [
        'non_emitting' => 'Non émetteur',
        'licensed_absent_themis' => 'Licencié absent de THEMIS',
        'other' => 'Autre',
    ],

    'ers_source' => [
        'import' => 'Import',
        'api' => 'API',
        'manual_entry' => 'Saisie manuelle',
    ],

    'vms_request_status' => [
        'requested' => 'Demandée',
        'assessed' => 'Évaluée',
        'closed' => 'Close',
    ],

    'beacon_request_status' => [
        'requested' => 'Demandée',
        'installed' => 'Installée',
        'closed' => 'Close',
    ],

    'assessment_result' => [
        'compatible' => 'Compatible',
        'incompatible' => 'Incompatible',
        'to_complete' => 'À compléter',
    ],
];
