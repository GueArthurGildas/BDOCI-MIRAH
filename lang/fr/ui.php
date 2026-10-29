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
    'notifications' => 'Notifications',
    'fullscreen' => 'Plein écran',
    'guide_in_progress' => 'Guide en cours de rédaction',
    'user_guide' => 'Guide utilisateur',
    'results_count' => '{0} Aucun résultat|{1} :count résultat|[2,*] :count résultats',
    'no_results' => 'Aucun résultat.',
    'filter_placeholder' => 'Filtrer…',
    'per_page' => 'Afficher',
    'per_page_entries' => 'entrées',
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
    'account_deactivated' => 'Votre compte a été désactivé. Contactez un administrateur.',
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
        'director' => 'Directeur',
        'team_lead' => 'Chef d\'équipe',
        'agent' => 'Agent',
    ],

    'role_descriptions' => [
        'admin' => 'Gère les comptes, les rôles, les paramètres, les connecteurs et le journal d\'audit. Ne prend aucune décision métier (exigence S-004).',
        'director' => 'Voit tout, y compris la vue flotte, et prend les décisions finales : refus d\'entrée au port, suspension ou révocation de licence, mesures conservatoires.',
        'team_lead' => 'Valide la saisie des agents, pilote l\'équipe de quart et clôture les opérations. Équivalent de l\'ancien rôle « Responsable CSP » (superviseur).',
        'agent' => 'Opérateur CSP ou inspecteur : saisit les observations, les mouvements et les inspections, consulte les fiches navires. Un inspecteur est un agent titulaire d\'une habilitation d\'inspecteur.',
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

    'confirm' => [
        'title' => 'Confirmation',
        'proceed' => 'Oui, continuer',
        'delete' => 'Oui, supprimer',
        'remove' => 'Oui, retirer',
    ],

    'health' => [
        'title' => 'Santé du système',
        'message' => '{1} :count source de données demande votre attention :|[2,*] :count sources de données demandent votre attention :',
        'details' => 'Voir les connecteurs',
    ],
];
