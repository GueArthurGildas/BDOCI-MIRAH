<?php

return [
    'intro_title' => 'Sur cette page',
    'next' => 'Suivant',
    'previous' => 'Précédent',
    'done' => 'Terminer',
    'progress' => 'Étape :current sur :total',
    'generic_intro' => 'Cette page n\'a pas de visite détaillée. Voici les repères de l\'application, les mêmes sur tous les écrans.',

    'common' => [
        'menu' => ['title' => 'Le menu latéral', 'text' => 'Les écrans de la famille choisie. L\'écran courant est en orange ; un rond orange avec un nombre signale des éléments en attente.'],
        'tabs' => ['title' => 'Les familles de menus', 'text' => 'Chaque onglet orange ouvre une famille d\'écrans : Pilotage, Surveillance, Référentiel, Contrôles, Administration (selon votre rôle).'],
        'tools' => ['title' => 'Vos outils', 'text' => 'La cloche des notifications, la recherche, ce guide, le plein écran, la langue et votre menu utilisateur.'],
        'stats' => ['title' => 'Les indicateurs', 'text' => 'Chaque carte chiffrée résume un état ; certaines sont cliquables pour ouvrir la liste correspondante.'],
        'toolbar' => ['title' => 'La barre d\'outils', 'text' => 'Choisissez le nombre de lignes, cherchez, filtrez par date ou catégorie. « Réinitialiser » apparaît quand un filtre est actif.'],
        'filters' => ['title' => 'Les filtres par colonne', 'text' => 'Choisissez une valeur sous l\'en-tête d\'une colonne : la liste se met à jour aussitôt.'],
        'table' => ['title' => 'Le tableau', 'text' => 'Chaque ligne est un élément de la liste ; cliquez son nom ou sa référence pour ouvrir le détail.'],
        'exports' => ['title' => 'Exporter la liste', 'text' => 'Téléchargez la liste en CSV ou en PDF. Chaque export est journalisé.'],
        'new' => ['title' => 'Créer un élément', 'text' => 'Le bouton orange crée un nouvel élément dans cette liste.'],
    ],

    'pages' => [
        'dashboard' => [
            'intro' => 'Votre tableau de bord : l\'état de la surveillance en un coup d\'œil, à chaque ouverture de l\'application.',
            'steps' => [
                'stats' => ['title' => 'Les indicateurs clés', 'text' => 'Navires actifs, licences qui expirent, observations en suspens et balises en rupture. Les chiffres sont recalculés à chaque ouverture de la page.'],
                'zones' => ['title' => 'Situation par zone', 'text' => 'Le nombre de navires présents dans chaque zone portuaire à cet instant.'],
                'infractions' => ['title' => 'Infractions par statut', 'text' => 'La répartition des dossiers d\'infraction : ouverts, en instruction, en décision, clos.'],
                'sources' => ['title' => 'Fraîcheur des données', 'text' => 'Pour chaque source externe : dernière synchronisation, erreurs et rejets. Un statut dégradé demande l\'attention de l\'Administrateur.'],
                'observations' => ['title' => 'Dernières observations en suspens', 'text' => 'Les observations ouvertes ou en cours, à suivre ou à transmettre à l\'équipe suivante.'],
            ],
        ],
        'fleet_index' => [
            'intro' => 'La vue d\'ensemble de la flotte, réservée aux profils de pilotage.',
            'steps' => [
                'stats' => ['title' => 'Les indicateurs cliquables', 'text' => 'Cliquez un indicateur pour afficher la liste des navires qui le composent.'],
                'filter_card' => ['title' => 'Période et filtres', 'text' => 'Restreignez la vue par période, zone ou segment.'],
                'distribution' => ['title' => 'Statut à feux de la flotte', 'text' => 'Combien de navires sont conformes, à surveiller, suspects ou en infraction.'],
                'ranking' => ['title' => 'Navires à plus forte vigilance', 'text' => 'Les navires classés par score de vigilance (0 à 100) ; cliquez-en un pour ouvrir sa fiche.'],
            ],
        ],
        'vessels_index' => [
            'intro' => 'La liste de tous les navires suivis. Retrouvez un navire, voyez d\'un coup d\'œil sa licence et sa balise, puis ouvrez sa fiche.',
            'steps' => [
                'table' => ['title' => 'Les navires', 'text' => 'Cliquez le nom d\'un navire pour sa fiche complète, ou l\'œil à côté pour un aperçu rapide sans quitter la liste.'],
                'new' => ['title' => 'Nouveau navire', 'text' => 'Crée une fiche navire à la main. Le rapprochement évite les doublons.'],
            ],
        ],
        'vessels_show' => [
            'intro' => 'La fiche complète d\'un navire : tout ce que l\'application sait de lui, au même endroit.',
            'steps' => [
                'header' => ['title' => 'Situation du navire', 'text' => 'Le statut à feux (conforme, à surveiller, suspect, en infraction) avec ses raisons, puis les identifiants, l\'armateur, l\'agent et le capitaine.'],
                'summary' => ['title' => 'Résumé', 'text' => 'Catégorie, statut, licence et état de la balise. Le bouton Modifier, s\'il apparaît, ouvre le formulaire d\'édition.'],
                'position' => ['title' => 'Dernière position connue', 'text' => 'La position et la trace récente du navire sur la carte.'],
                'tabs' => ['title' => 'Les onglets', 'text' => 'Licences, balises, alertes, inspections, infractions, missions, messages, chronologie… Les onglets affichés dépendent de vos droits.'],
            ],
        ],
        'vessels_duplicates_index' => [
            'intro' => 'Les fiches entrantes dont l\'identité est douteuse attendent ici votre décision.',
            'steps' => [
                'table' => ['title' => 'Trancher un doublon', 'text' => 'Comparez la fiche entrante à la fiche existante, puis choisissez : fusionner, garder séparées ou rejeter. Une confirmation est demandée avant d\'agir.'],
            ],
        ],
        'watch_board_index' => [
            'intro' => 'Le tableau du matin : ce qu\'il faut lire en prenant son poste.',
            'steps' => [
                'stats' => ['title' => 'Trois compteurs', 'text' => 'Navires à surveiller, observations ouvertes et relais à lire.'],
                'relays' => ['title' => 'Relais reçus', 'text' => 'Les observations transmises par l\'équipe précédente ; cliquez « Accuser lecture » pour chacune.'],
                'checks' => ['title' => 'Navires vérifiés trop souvent', 'text' => 'Au-delà du seuil de vérifications, un suivi renforcé est proposé ; confirmez-le si vous êtes d\'accord.'],
                'watched' => ['title' => 'Navires en veille', 'text' => 'Les navires à surveiller : liste du Directeur, suivi renforcé, entrées et sorties de la ZEE…'],
                'instructions' => ['title' => 'Consignes permanentes', 'text' => 'Les informations à appliquer tant qu\'elles ne sont pas levées.'],
                'observations' => ['title' => 'Observations ouvertes', 'text' => 'Les constats en cours, à suivre pendant le quart.'],
                'add' => ['title' => 'Ajouter un navire à surveiller', 'text' => 'Placez un navire en suivi renforcé, ou dans la liste du Directeur si votre rôle le permet.'],
            ],
        ],
        'watch_observations_index' => [
            'intro' => 'Les constats de surveillance, navire par navire, avec leur relais à l\'équipe suivante.',
            'steps' => [
                'new' => ['title' => 'Nouvelle observation', 'text' => 'Crée la ligne de main courante, augmente le compteur de vérifications du navire et peut relayer l\'observation à l\'équipe suivante.'],
            ],
        ],
        'logbook_days_show' => [
            'intro' => 'La main courante du quart : le journal QUAND / COMMENT / QUOI.',
            'steps' => [
                'entries' => ['title' => 'Les lignes du quart', 'text' => 'Chaque fait reçu est une ligne : quand, comment, quoi. Après la clôture, les lignes sont verrouillées.'],
                'new_entry' => ['title' => 'Ajouter une ligne', 'text' => 'Saisissez le moment, le moyen, ce qui s\'est passé, et rattachez les navires concernés.'],
                'close_day' => ['title' => 'Clôturer la journée', 'text' => 'Réservé au Chef d\'équipe : génère un PDF non modifiable et verrouille les lignes (confirmation demandée).'],
                'pdf' => ['title' => 'Copie PDF', 'text' => 'Le PDF figé de la journée, dont l\'intégrité se contrôle.'],
            ],
        ],
        'tracking_map' => [
            'intro' => 'La situation en mer : un point par navire à sa dernière position.',
            'steps' => [
                'map' => ['title' => 'La carte', 'text' => 'Zoomez avec + et −, déplacez-vous en glissant, cliquez un point pour voir le navire. Les zones de surveillance sont tracées.'],
            ],
        ],
        'tracking_alerts_index' => [
            'intro' => 'Les alertes levées par les règles de surveillance : silences de balise, géorepérage, vitesse.',
            'steps' => [
                'stats' => ['title' => 'Alertes par statut', 'text' => 'Combien d\'alertes sont ouvertes, acquittées ou traitées.'],
                'table' => ['title' => 'La liste des alertes', 'text' => 'Ouvrez une alerte pour l\'acquitter puis la traiter : vérifiez la position et la balise, consignez les mesures prises.'],
            ],
        ],
        'tracking_slots_index' => [
            'intro' => 'Un créneau de contrôle prend une photo des positions et propose trois tests assistés.',
            'steps' => [
                'new' => ['title' => 'Ouvrir un créneau', 'text' => 'Charge les positions du moment ; vous confirmez ou corrigez ensuite les tests navire par navire.'],
                'table' => ['title' => 'Les créneaux', 'text' => 'Ouvrez un créneau pour confirmer les positions et lancer un balayage.'],
            ],
        ],
        'operations_index' => [
            'intro' => 'Les opérations ouvertes pour un fait complexe, avec leur code OPS-numéro-catégorie-année-NOM.',
            'steps' => [
                'new' => ['title' => 'Nouvelle opération', 'text' => 'Ouvre un dossier pour un fait complexe ; son code est généré automatiquement.'],
            ],
        ],
        'operations_show' => [
            'intro' => 'Le dossier d\'une opération, du premier constat à la clôture.',
            'steps' => [
                'details' => ['title' => 'Détails', 'text' => 'Catégorie, navires concernés, équipe et état de l\'opération.'],
                'events' => ['title' => 'Chronologie', 'text' => 'Tous les événements de l\'opération.'],
                'add_event' => ['title' => 'Ajouter un événement', 'text' => 'Consignez chaque développement au fur et à mesure.'],
                'instructions' => ['title' => 'Instructions', 'text' => 'Les consignes données aux équipes pour cette opération.'],
                'close' => ['title' => 'Clôturer l\'opération', 'text' => 'Réservé au Chef d\'équipe : renseignez le résultat puis clôturez ; les lignes de veille de l\'opération se ferment.'],
            ],
        ],
        'licenses_index' => [
            'intro' => 'Toutes les licences de pêche suivies, avec leurs échéances.',
            'steps' => [
                'stats' => ['title' => 'Licences par statut et échéance', 'text' => 'Chaque carte est cliquable : elle filtre la liste (valides, expirées, qui expirent à 30, 60 ou 90 jours…).'],
                'new' => ['title' => 'Nouvelle licence', 'text' => 'Enregistre une licence déjà délivrée hors de l\'application.'],
            ],
        ],
        'licenses_show' => [
            'intro' => 'La fiche d\'une licence : identité, périmètre autorisé, documents, suspensions et révocation.',
            'steps' => [
                'identity' => ['title' => 'Identité de la licence', 'text' => 'Numéro, navire, segment, dates et statut.'],
                'scope' => ['title' => 'Zones, engins et espèces', 'text' => 'Ce que la licence autorise : zones de pêche, engins, espèces et conditions.'],
                'documents' => ['title' => 'Documents', 'text' => 'Les pièces jointes à la licence ; chaque ajout est tracé.'],
                'suspension' => ['title' => 'Suspensions', 'text' => 'Suspendez la licence avec un motif et une fin prévue (trois mois au plus hors force majeure). Une suspension se lève explicitement.'],
                'revocation' => ['title' => 'Révocation', 'text' => 'Décision définitive : une confirmation est demandée. Réservée au Chef d\'équipe et au Directeur.'],
            ],
        ],
        'infractions_index' => [
            'intro' => 'Les dossiers d\'infraction, de l\'ouverture à la clôture.',
            'steps' => [
                'new' => ['title' => 'Ouvrir un dossier', 'text' => 'Constitue un dossier à partir d\'un constat, d\'une inspection, d\'une mission ou d\'un signalement.'],
            ],
        ],
        'infractions_show' => [
            'intro' => 'Le dossier complet d\'une infraction, dans l\'ordre de l\'instruction.',
            'steps' => [
                'case' => ['title' => 'Le constat', 'text' => 'Le navire, les parties, les faits et le statut du dossier.'],
                'upheld' => ['title' => 'Infractions retenues', 'text' => 'Les infractions de la nomenclature retenues contre le navire.'],
                'evidence' => ['title' => 'Les preuves', 'text' => 'Chaque preuve est enregistrée avec sa date et son auteur ; scellés et saisies suivent dans les cartes voisines.'],
                'measures' => ['title' => 'Mesures conservatoires', 'text' => 'Le Chef d\'équipe les propose ; seul le Directeur les décide.'],
                'violation_report' => ['title' => 'Procès-verbal', 'text' => 'Établi une seule fois, il est figé en PDF.'],
                'follow_ups' => ['title' => 'Suites, décisions, paiements et recours', 'text' => 'La suite donnée au dossier, la décision, les paiements et les recours éventuels. Ces décisions restent humaines.'],
                'status' => ['title' => 'Clôture du dossier', 'text' => 'Quand tout est soldé, « Clore le dossier » (avec confirmation) termine l\'instruction.'],
            ],
        ],
        'missions_index' => [
            'intro' => 'Les missions de contrôle : programmées, autorisées, en cours, terminées ou clôturées.',
            'steps' => [
                'new' => ['title' => 'Programmer une mission', 'text' => 'La mission sera autorisée selon son type (le Directeur par défaut).'],
            ],
        ],
        'missions_show' => [
            'intro' => 'Le dossier d\'une mission, de la programmation au bilan.',
            'steps' => [
                'mission' => ['title' => 'La mission', 'text' => 'Son état et les boutons du cycle : Autoriser, Démarrer, Terminer, Clôturer (chacun avec confirmation).'],
                'map' => ['title' => 'Trace et rencontres', 'text' => 'La trace GPS importée et les zones traversées.'],
                'units' => ['title' => 'Unités rencontrées', 'text' => 'Vue, approchée ou contrôlée : un seul enregistrement, trois niveaux ; « contrôlée » exige le lien avec l\'inspection.'],
                'briefing' => ['title' => 'Briefing et préparation', 'text' => 'La préparation avant le départ.'],
                'review' => ['title' => 'Bilan', 'text' => 'Calculé automatiquement à partir des données de la mission, jamais saisi.'],
                'participants' => ['title' => 'Équipe', 'text' => 'Les personnes engagées dans la mission.'],
            ],
        ],
        'witnesses_index' => [
            'intro' => 'Les signalements de témoins reçus par e-mail, téléphone, fax, courrier ou fiche papier.',
            'steps' => [
                'new' => ['title' => 'Enregistrer un signalement', 'text' => 'Saisissez le rapport reçu : le témoin n\'a jamais accès à l\'application.'],
            ],
        ],
        'communications_messages_index' => [
            'intro' => 'L\'historique des messages envoyés et de ceux en attente de validation.',
            'steps' => [
                'new' => ['title' => 'Envoyer un message', 'text' => 'Choisissez un modèle, un canal et un destinataire ; une confirmation récapitule l\'envoi.'],
            ],
        ],
        'movements_eez_index' => [
            'intro' => 'Les déclarations d\'entrée et de sortie de la ZEE.',
            'steps' => [
                'new' => ['title' => 'Saisir une déclaration', 'text' => 'Entrée ou sortie, avec les captures telles qu\'annoncées par le navire ; l\'application vérifie et apparie.'],
            ],
        ],
        'movements_index' => [
            'intro' => 'Les mouvements des navires dans les ports.',
            'steps' => [
                'new' => ['title' => 'Enregistrer un mouvement', 'text' => 'Entrée ou sortie de port, avec le motif.'],
            ],
        ],
        'catches_index' => [
            'intro' => 'Les captures déclarées, consultables par espèce, zone et source.',
        ],
        'inspections_index' => [
            'intro' => 'Les inspections lues depuis e-inspection : consultation seule.',
        ],
        'administration_users_index' => [
            'intro' => 'Les comptes utilisateurs : création, rôle, groupes et désactivation.',
            'steps' => [
                'new' => ['title' => 'Nouvel utilisateur', 'text' => 'Créez un compte, choisissez son rôle et ses groupes ; désactivez-le pour retirer l\'accès aussitôt.'],
            ],
        ],
        'administration_roles_index' => [
            'intro' => 'Les rôles et ce que chacun peut faire.',
            'steps' => [
                'list' => ['title' => 'Les rôles', 'text' => 'Les quatre rôles système (leurs permissions sont modifiables) et vos rôles personnalisés.'],
                'matrix' => ['title' => 'La matrice des permissions', 'text' => 'Chaque ligne est un droit, chaque colonne un rôle : repérez d\'un coup d\'œil qui peut quoi.'],
                'new' => ['title' => 'Nouveau rôle', 'text' => 'Créez un rôle, éventuellement héritier d\'un rôle parent, puis cochez ses permissions.'],
            ],
        ],
        'administration_settings' => [
            'intro' => 'Les seuils, délais et interrupteurs de l\'application, modifiables sans développeur.',
            'steps' => [
                'group' => ['title' => 'Un groupe de réglages', 'text' => 'Modifiez une valeur dans les bornes autorisées et enregistrez : l\'effet est immédiat. « Rétablir les valeurs par défaut » ramène tout.'],
            ],
        ],
        'administration_connectors' => [
            'intro' => 'Les sources de données externes et leur état.',
            'steps' => [
                'source' => ['title' => 'Une source', 'text' => 'Un voyant vert, orange ou rouge et un indicateur de données périmées. Ouvrez-la pour voir ses synchronisations, rejets et erreurs.'],
            ],
        ],
        'reports_exports_index' => [
            'intro' => 'Les exports de données en CSV ou PDF, tous journalisés.',
            'steps' => [
                'type' => ['title' => 'Un type d\'export', 'text' => 'Choisissez le format et les filtres puis téléchargez ; chaque export est inscrit au journal.'],
                'log' => ['title' => 'Journal des exports', 'text' => 'Qui a exporté quoi, quand, avec quels filtres et combien de lignes : il ne peut pas être modifié.'],
            ],
        ],
    ],
];
