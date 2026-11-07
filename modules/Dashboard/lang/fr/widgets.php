<?php

return [
    'kinds' => ['kpi' => 'Indicateur chiffré', 'chart' => 'Graphique', 'table' => 'Tableau'],
    'groups' => ['kpi' => 'Indicateurs chiffrés', 'chart' => 'Graphiques', 'table' => 'Tableaux'],
    'unknown' => 'Non renseigné',
    'active_vessels' => [
        'title' => 'Navires actifs',
        'explanation' => 'Nombre de navires du registre dont le statut est « actif », quelle que soit leur position.',
    ],
    'pending_observations' => [
        'title' => 'Observations en suspens',
        'explanation' => 'Observations de la main courante qui ne sont pas encore closes et attendent une suite.',
    ],
    'zones' => [
        'title' => 'Situation par zone',
        'explanation' => 'Nombre de navires présents à cet instant dans chaque zone portuaire, d\'après leur dernière position.',
    ],
    'infractions_by_status' => [
        'title' => 'Infractions par statut',
        'explanation' => 'Répartition des dossiers d\'infraction selon leur avancement, de l\'ouverture à la clôture.',
    ],
    'inspections_monthly' => [
        'title' => 'Inspections des six derniers mois',
        'explanation' => 'Nombre d\'inspections réalisées chaque mois, issues de la base e-inspection.',
    ],
    'fleet_status_lights' => [
        'title' => 'Statut à feux de la flotte',
        'explanation' => 'Répartition des navires entre conformes, à surveiller, suspects et en infraction.',
    ],
    'vessels_by_segment' => [
        'title' => 'Navires par segment de flotte',
        'explanation' => 'Répartition des navires du registre selon leur segment : industriel, semi-industriel, artisanal, étranger autorisé, etc.',
    ],
    'vessels_by_flag' => [
        'title' => 'Navires par pavillon',
        'explanation' => 'Les huit pavillons les plus représentés dans le registre des navires.',
    ],
    'licenses_by_status' => [
        'title' => 'Licences par statut',
        'explanation' => 'Répartition des licences entre valides, qui expirent, expirées, suspendues et révoquées.',
    ],
    'licenses_expiring_by_month' => [
        'title' => 'Échéances de licences à venir',
        'explanation' => 'Nombre de licences qui arrivent à échéance chaque mois, sur les six prochains mois.',
    ],
    'alerts_by_status' => [
        'title' => 'Alertes par statut',
        'explanation' => 'Alertes de suivi des navires selon leur traitement : ouvertes, prises en compte ou closes.',
    ],
    'observations_by_level' => [
        'title' => 'Observations en suspens par niveau',
        'explanation' => 'Répartition des observations non closes selon leur niveau de gravité.',
    ],
    'operations_by_status' => [
        'title' => 'Opérations par statut',
        'explanation' => 'Répartition des opérations selon leur état d\'avancement.',
    ],
    'missions_by_status' => [
        'title' => 'Missions par statut',
        'explanation' => 'Répartition des missions de surveillance selon leur état : préparation, en cours, terminées, etc.',
    ],
    'data_sources' => [
        'title' => 'Fraîcheur des données',
        'explanation' => 'Date de dernière synchronisation de chaque source de données, avec le nombre d\'erreurs et de rejets en attente.',
    ],
    'pending_observation_list' => [
        'title' => 'Dernières observations en suspens',
        'explanation' => 'Les cinq observations non closes les plus récentes de la main courante.',
    ],
];
