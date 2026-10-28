<?php

return [
    'title' => 'Paramètres',
    'subtitle' => 'Comportement des notifications et seuils d\'alerte (aucune valeur sensible)',
    'saved' => 'Paramètres enregistrés.',
    'reset' => 'Rétablir les valeurs par défaut',
    'reset_done' => 'Les paramètres par défaut ont été rétablis.',
    'default' => 'Par défaut : :value',
    'range' => 'De :min à :max',
    'customized' => 'Personnalisé',
    'groups' => [
        'browser' => [
            'title' => 'Notifications dans le navigateur',
            'description' => 'Fréquence de vérification de la balise et affichage des notifications flottantes',
        ],
        'beacons' => [
            'title' => 'Alerte balises muettes',
            'description' => 'Émise lorsque des balises installées cessent d\'émettre',
        ],
        'eez' => [
            'title' => 'Alerte déclarations ZEE',
            'description' => 'Émise lorsqu\'un navire étranger est en ZEE sans déclaration d\'entrée, et pour les alertes d\'entrée et de sortie ouvertes',
        ],
        'licenses' => [
            'title' => 'Alerte licences à échéance',
            'description' => 'Émise lorsque des licences arrivent bientôt à échéance',
        ],
        'watch' => [
            'title' => 'Surveillance de quart et main courante',
            'description' => 'Horaires du quart, nombre d\'équipes et seuil de vérifications (point ouvert E1, valeurs provisoires)',
        ],
        'reports' => [
            'title' => 'Rapports et archivage',
            'description' => 'Alerte des rapports produits et nombre d\'années avant l\'archivage des opérations clôturées (valeurs provisoires)',
        ],
        'connectors' => [
            'title' => 'Connecteurs',
            'description' => 'Fraîcheur des données synchronisées et seuil d\'écart de captures propre au CSP (points ouverts, valeurs provisoires)',
        ],
    ],
    'fields' => [
        'notifications_poll_seconds' => 'Vérifier les nouvelles notifications toutes les (secondes)',
        'notifications_toast_seconds' => 'Durée d\'affichage des notifications flottantes (secondes)',
        'notifications_max_toasts' => 'Nombre maximal de notifications flottantes par vérification',
        'alerts_beacons_silent_enabled' => 'Envoyer l\'alerte',
        'alerts_beacons_silent_threshold_hours' => 'Une balise est muette après (heures sans émission)',
        'alerts_beacons_silent_cooldown_hours' => 'Délai minimal entre deux alertes (heures)',
        'alerts_licenses_expiring_enabled' => 'Envoyer l\'alerte',
        'alerts_licenses_expiring_warning_days' => 'Une licence arrive à échéance dans (jours)',
        'alerts_licenses_expiring_cooldown_hours' => 'Délai minimal entre deux alertes (heures)',
        'alerts_eez_declarations_enabled' => 'Envoyer l\'alerte',
        'alerts_eez_declarations_position_window_hours' => 'Une position est récente en deçà de (heures)',
        'alerts_eez_declarations_cooldown_hours' => 'Délai minimal entre deux alertes (heures)',
        'eez_variance_tolerance_percent' => 'Écart de captures toléré entre entrée et sortie (pourcentage)',
        'watch_shift_start_hour' => 'Heure de début du quart (0 à 23)',
        'watch_shift_end_hour' => 'Heure de clôture de la main courante (1 à 23)',
        'watch_teams_count' => 'Nombre d\'équipes en rotation',
        'watch_check_threshold' => 'Une alerte est levée au-delà de (vérifications d\'un même navire)',
        'alerts_scheduled_reports_enabled' => 'Prévenir le destinataire lorsqu\'un rapport programmé est prêt',
        'archive_after_years' => 'Les opérations clôturées sont archivées après (années)',
        'connectors_stale_after_hours' => 'Une source est périmée après (heures sans synchronisation)',
        'connectors_catch_variance_threshold_percent' => 'Seuil d\'écart de captures propre au CSP (pourcentage)',
    ],
];
