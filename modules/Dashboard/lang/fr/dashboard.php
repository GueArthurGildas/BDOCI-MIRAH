<?php

return [
    'title' => 'Tableau de bord',
    'subtitle' => 'Indicateurs clés de la surveillance des pêches',
    'stats' => [
        'active_vessels' => 'Navires actifs',
        'expiring_licenses' => 'Licences à échéance',
        'pending_observations' => 'Observations en suspens',
        'silent_beacons' => 'Balises en rupture',
    ],
    'zones_title' => 'Situation par zone',
    'zones_subtitle' => 'Navires présents en zones portuaires à :time',
    'infractions_title' => 'Infractions par statut',
    'inspections_title' => 'Inspections des six derniers mois',
    'sources_title' => 'Fraîcheur des données',
    'observations_title' => 'Dernières observations en suspens',
    'columns' => [
        'source' => 'Source',
        'last_synced_at' => 'Dernière synchronisation',
        'errors' => 'Erreurs',
        'rejected' => 'Rejets',
        'status' => 'Statut',
        'date' => 'Date',
        'vessel' => 'Navire',
        'level' => 'Niveau',
        'description' => 'Description',
    ],
    'never' => 'Jamais',
];
