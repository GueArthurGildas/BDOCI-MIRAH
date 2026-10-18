<?php

return [
    'language' => 'Français',
    'change_language' => 'Changer de langue',
    'home' => 'Accueil',
    'log_out' => 'Déconnexion',
    'filters' => 'Filtres',
    'filter' => 'Filtrer',
    'reset' => 'Réinitialiser',
    'search' => 'Rechercher',
    'results_count' => '{0} Aucun résultat|{1} :count résultat|[2,*] :count résultats',
    'no_results' => 'Aucun résultat.',
    'select_placeholder' => 'Sélectionner…',
    'save' => 'Enregistrer',
    'cancel' => 'Annuler',
    'create' => 'Créer',
    'edit' => 'Modifier',
    'view' => 'Voir',
    'back' => 'Retour',
    'close' => 'Fermer',
    'back_home' => 'Retour à l\'accueil',
    'actions' => 'Actions',
    'all' => 'Tous',
    'yes' => 'Oui',
    'no' => 'Non',
    'saved' => 'Modifications enregistrées.',
    'updated_at' => 'Mis à jour à :time',

    'navigation_groups' => [
        'overview' => 'Pilotage',
        'surveillance' => 'Surveillance',
        'registry' => 'Référentiel',
        'controls' => 'Contrôles',
        'administration' => 'Administration',
    ],

    'roles' => [
        'admin' => 'Administrateur',
        'supervisor' => 'Responsable CSP',
        'surveillance_agent' => 'Agent de surveillance',
        'inspector' => 'Inspecteur',
    ],

    'history' => [
        'title' => 'Historique',
        'created' => 'Créé par :user',
        'updated' => 'Modifié par :user',
        'system' => 'le système',
        'empty' => 'Aucune modification enregistrée.',
    ],

    'errors' => [
        403 => [
            'title' => 'Accès refusé',
            'message' => 'Vous n\'avez pas l\'autorisation d\'accéder à cette page.',
        ],
        404 => [
            'title' => 'Page introuvable',
            'message' => 'La page demandée n\'existe pas ou a été déplacée.',
        ],
        419 => [
            'title' => 'Session expirée',
            'message' => 'Votre session a expiré. Rechargez la page et réessayez.',
        ],
        429 => [
            'title' => 'Trop de requêtes',
            'message' => 'Veuillez patienter un instant avant de réessayer.',
        ],
        500 => [
            'title' => 'Erreur serveur',
            'message' => 'Une erreur est survenue de notre côté. Réessayez plus tard.',
        ],
        503 => [
            'title' => 'Maintenance en cours',
            'message' => 'L\'application est temporairement indisponible. Réessayez plus tard.',
        ],
    ],
];
