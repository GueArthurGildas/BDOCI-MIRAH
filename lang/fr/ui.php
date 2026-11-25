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
    'refresh' => 'Rafraîchir la page',
    'guide_hint' => 'Une visite rapide de cette page',
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
    'required_fields' => 'Champs obligatoires',
    'open_full_page' => 'Ouvrir en pleine page',
    'form_error' => 'Le formulaire contient des erreurs.',
    'form_failed' => 'L\'enregistrement a échoué. Réessayez ou ouvrez le formulaire en pleine page.',
    'back_home' => 'Retour à l\'accueil',
    'actions' => 'Actions',
    'all' => 'Tous',
    'yes' => 'Oui',
    'no' => 'Non',
    'account_deactivated' => 'Votre compte a été désactivé. Contactez un administrateur.',
    'saved' => 'Modifications enregistrées.',
    'updated_at' => 'Mis à jour à :time',

    'morning_menu' => [
        'title' => 'Tableau du matin',
        'hint' => 'Les écrans d\'une journée de quart au CSP, dans l\'ordre du travail',
        'sections' => [
            'shift' => 'Prise de quart',
            'watch' => 'Veille et alertes',
            'record' => 'Enregistrer l\'information',
            'operations' => 'Opérations et échanges',
            'follow_up' => 'Suivi et décisions',
        ],
        'links' => [
            'handover' => 'Relève de quart',
            'today' => 'Main courante du jour',
            'new_operation' => '+ Nouvelle opération',
        ],
    ],
    'navigation_folders' => [
        'e_inspection' => 'e-inspection',
        'morning_board' => 'Tableau du matin',
        'logbook' => 'Main courante',
    ],

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

    'popup' => [
        'done' => 'Terminé',
        'continue' => 'Continuer',
    ],

    'confirm' => [
        'title' => 'Confirmation',
        'proceed' => 'Oui, continuer',
        'delete' => 'Oui, supprimer',
        'remove' => 'Oui, retirer',
        'record_title' => 'Confirmer l\'enregistrement',
        'record' => 'Vérifiez les informations saisies avant de les enregistrer.',
        'record_label' => 'Oui, enregistrer',
    ],

    'health' => [
        'title' => 'Santé du système',
        'message' => '{1} :count source de données demande votre attention :|[2,*] :count sources de données demandent votre attention :',
        'details' => 'Voir les connecteurs',
    ],
    'silence_presets' => [
        '2_days' => '2 jours',
        '3_days' => '3 jours',
        '4_days' => '4 jours',
        '5_days' => '5 jours',
        '1_week' => '1 semaine',
        '2_weeks' => '2 semaines',
        '1_month' => '1 mois',
    ],
    'emission_gap' => [
        'days_hours' => ':days j :hours h',
        'days' => ':days j',
        'hours' => ':hours h',
        'minutes' => ':minutes min',
    ],
    'pager' => [
        'previous' => 'Page précédente',
        'next' => 'Page suivante',
        'range' => '{from} à {to} sur {total}',
    ],
    'notions' => [
        'title' => 'Alerte, communication ou notification ?',
        'alert' => [
            'name' => 'Alerte',
            'text' => 'un signalement levé automatiquement par une règle de surveillance (silence d\'une balise, zone interdite, vitesse). Il demande un traitement et reste ouvert jusqu\'à sa clôture.',
        ],
        'communication' => [
            'name' => 'Communication',
            'text' => 'un message envoyé à un tiers (armateur, agent, institution) par e-mail ou WhatsApp, ou une note échangée entre agents. Elle garde une trace de l\'envoi et de la réponse.',
        ],
        'notification' => [
            'name' => 'Notification',
            'text' => 'un rappel affiché dans l\'application (la cloche) pour vous prévenir qu\'un événement vous concerne. Elle se lit puis disparaît ; elle ne remplace ni l\'alerte ni le message.',
        ],
    ],
    'deletion' => [
        'title' => 'Supprimer cet enregistrement',
        'button' => 'Supprimer',
        'reason' => 'Motif de la suppression',
        'reason_placeholder' => 'Pourquoi supprimer ? (obligatoire)',
        'hint' => 'L\'enregistrement disparaît des listes ; sa ligne de main courante reste visible, barrée, avec votre nom et le motif.',
        'confirm' => 'Supprimer cet enregistrement ? Il disparaîtra des listes et sa ligne de main courante sera barrée.',
        'done' => 'L\'enregistrement a été supprimé.',
        'day_closed' => 'La main courante de ce jour est clôturée : la suppression n\'est plus possible, passez par une ligne rectificative.',
        'linked_entry' => 'Cette ligne vient d\'un autre enregistrement (observation, vérification, signalement ou opération) : supprimez l\'enregistrement d\'origine.',
        'cancelled' => 'Supprimée par :name le :date : :reason',
    ],
];
