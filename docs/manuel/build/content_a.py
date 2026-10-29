from lib import *


def intro():
    out = h1('À propos de ce document', 'About this document')
    out += p('Ce manuel accompagne la livraison de l\'application <strong>BDOCI MIRAH</strong>, l\'outil de surveillance des pêches du Centre de Surveillance des Pêches (CSP) du Ministère des Ressources Animales et Halieutiques (MIRAH) de Côte d\'Ivoire. Il a deux usages : <strong>référence</strong> pour les équipes qui utilisent l\'application au quotidien, et <strong>guide de prise en main</strong> pour les nouveaux utilisateurs.',
             'This manual accompanies the delivery of the <strong>BDOCI MIRAH</strong> application, the fisheries monitoring tool of the Fisheries Monitoring Centre (CSP) of the Ministry of Animal and Fisheries Resources (MIRAH) of Côte d\'Ivoire. It has two purposes: a <strong>reference</strong> for the teams who use the application every day, and a <strong>getting-started guide</strong> for new users.')
    out += h2('À qui s\'adresse-t-il ?', 'Who is it for?')
    out += table([('Lecteur', 'Reader'), ('Ce qu\'il y trouve', 'What they will find')], [
        [('Agent, Chef d\'équipe, Directeur', 'Agent, Team lead, Director'), ('Son chapitre de rôle : la journée type, les écrans, les procédures pas à pas, ce qui est permis et ce qui ne l\'est pas.', 'Their role chapter: a typical day, the screens, step-by-step procedures, what is allowed and what is not.')],
        [('Administrateur', 'Administrator'), ('Le chapitre d\'administration : comptes, rôles, paramètres, connecteurs, journal d\'audit.', 'The administration chapter: accounts, roles, settings, connectors, audit log.')],
    ], [30, 70])
    out += h2('Comment le lire', 'How to read it')
    out += ul([
        ('Le document est publié en <strong>français</strong> (première partie) puis en <strong>anglais</strong> (seconde partie) ; les deux parties ont exactement la même structure.', 'The document is published in <strong>French</strong> (first part) then in <strong>English</strong> (second part); both parts have exactly the same structure.'),
        ('Les <strong>encadrés de procédure</strong> (bandeau bleu nuit) décrivent une tâche pas à pas ; le texte à droite du titre indique qui peut la réaliser.', 'The <strong>procedure boxes</strong> (navy header) describe a task step by step; the text on the right of the title says who can perform it.'),
        ('Les encadrés <span class="tag tag-g">Conseil</span>, <span class="tag tag-b">À savoir</span> et <span class="tag tag-r">Attention</span> signalent respectivement une astuce, une précision et un risque.', 'The boxes <span class="tag tag-g">Tip</span>, <span class="tag tag-b">Good to know</span> and <span class="tag tag-r">Warning</span> flag a shortcut, a clarification and a risk respectively.'),
        ('Les noms de menus, de boutons et d\'écrans sont écrits comme dans l\'application, dans la langue du document.', 'Menu, button and screen names are written as they appear in the application, in the language of the document.'),
    ])
    out += h2('Comptes de démonstration', 'Demonstration accounts')
    out += p('L\'application est livrée avec des données et des comptes de démonstration pour s\'exercer sans risque. Le mot de passe commun est <kbd>password</kbd> ; il doit être changé avant toute mise en production.',
             'The application is delivered with demonstration data and accounts so you can practise safely. The shared password is <kbd>password</kbd>; it must be changed before any production use.')
    out += table([('Compte', 'Account'), ('Rôle', 'Role'), ('Ce que le compte permet d\'essayer', 'What the account lets you try')], [
        ['agent@example.com', ('Agent', 'Agent'), ('Saisir, consulter, signaler ; aucune clôture ni décision.', 'Enter, consult, report; no closing or decision.')],
        ['teamlead@example.com', ('Chef d\'équipe', 'Team lead'), ('Piloter la journée, clôturer, instruire, autoriser.', 'Run the day, close, investigate, authorize.')],
        ['director@example.com', ('Directeur', 'Director'), ('Tout le métier, plus les décisions finales et la vue flotte.', 'The whole business side, plus final decisions and the fleet view.')],
        ['admin@example.com', ('Administrateur', 'Administrator'), ('Comptes, droits, paramètres, connecteurs, audit ; aucune décision métier.', 'Accounts, rights, settings, connectors, audit; no business decision.')],
    ], [28, 17, 55])
    out += note('warn', 'Les données de démonstration sont fictives : elles servent uniquement à s\'exercer.', 'The demonstration data is fictitious: it is only there for practice.')
    return out


def overview():
    out = h1('Présentation de BDOCI MIRAH', 'BDOCI MIRAH at a glance')
    out += p('BDOCI MIRAH rassemble au même endroit tout ce que le CSP doit savoir et faire pour surveiller la pêche dans les eaux ivoiriennes : <strong>quels navires sont autorisés, où ils se trouvent, ce qu\'ils déclarent, ce qui a été constaté, et quelles suites sont données</strong>.',
             'BDOCI MIRAH brings together in one place everything the CSP needs to know and do to monitor fishing in Ivorian waters: <strong>which vessels are authorized, where they are, what they declare, what has been observed, and what follow-up is given</strong>.', )
    out += h2('Les grands domaines', 'The main areas')
    out += table([('Domaine', 'Area'), ('Ce que l\'application permet', 'What the application allows'), ('Menu', 'Menu')], [
        [('Navires et référentiels', 'Vessels and registries'), ('Une fiche unique par navire (identifiants, armateur, agent, capitaine, historique), rapprochement des doublons, listes de référence (pays, ports, espèces, engins, institutions…).', 'One single record per vessel (identifiers, owner, agent, captain, history), duplicate matching, reference lists (countries, ports, species, gear, institutions…).'), ('Référentiel', 'Registry')],
        [('Licences', 'Licences'), ('Suivi des licences délivrées : zones, engins, espèces, conditions, suspension, révocation, statut FNP, import par lot, registre annuel, liste des navires autorisés, alertes d\'échéance à 30, 60 et 90 jours.', 'Tracking of issued licences: zones, gear, species, conditions, suspension, revocation, FNP status, batch import, annual register, list of authorized vessels, expiry alerts at 30, 60 and 90 days.'), ('Référentiel', 'Registry')],
        [('Suivi par balise (VMS)', 'Beacon tracking (VMS)'), ('Carte de situation, état des balises, alertes (balise silencieuse, etc.), créneaux de contrôle avec trois tests assistés, conformité des balises, avaries, déclarations ERS.', 'Situation map, beacon status, alerts (silent beacon, etc.), control slots with three assisted tests, beacon conformity, failures, ERS declarations.'), ('Surveillance', 'Surveillance')],
        [('Veille de quart', 'Shift watch'), ('Main courante, observations de quart avec relais, rotations et relèves, consignes permanentes, tableau du matin, opérations.', 'Logbook, shift observations with relays, rotations and handovers, standing instructions, morning board, operations.'), ('Surveillance', 'Surveillance')],
        [('Entrées et sorties de la ZEE', 'EEZ entries and exits'), ('Déclarations d\'entrée et de sortie, appariement, non-déclarations, alertes, mouvements portuaires.', 'Entry and exit declarations, pairing, missed declarations, alerts, port movements.'), ('Surveillance', 'Surveillance')],
        [('Contrôles et suites', 'Controls and follow-up'), ('Inspections, captures, débarquements, transbordements, dossiers d\'infraction complets, missions et moyens, données MREP.', 'Inspections, catches, landings, transshipments, complete infraction case files, missions and assets, MREP data.'), ('Contrôles', 'Controls')],
        [('Signalements et communications', 'Reports and communications'), ('Signalements de témoins, messages préconçus et envois automatiques (in-app, e-mail, WhatsApp), notes internes, agent IA.', 'Witness reports, ready-made messages and automatic dispatch (in-app, e-mail, WhatsApp), internal notes, AI agent.'), ('Surveillance', 'Surveillance')],
        [('Pilotage', 'Management'), ('Tableau de bord, vue flotte, indicateurs, rapports programmés, exports journalisés, archivage à 5 ans.', 'Dashboard, fleet view, indicators, scheduled reports, logged exports, 5-year archiving.'), ('Pilotage / Administration', 'Overview / Administration')],
        [('Administration', 'Administration'), ('Comptes, rôles et groupes, paramètres, règles d\'alerte, listes de référence, champs extensibles, connecteurs, journal d\'audit.', 'Accounts, roles and groups, settings, alert rules, reference lists, extensible fields, connectors, audit log.'), ('Administration', 'Administration')],
    ], [20, 60, 20])
    out += h2('Les principes à retenir', 'Key principles')
    out += ul([
        ('<strong>Une fiche unique par navire.</strong> Quelle que soit l\'origine d\'une information (saisie manuelle, THEMIS, NENO, e-inspection, DPEP), elle aboutit à la même fiche. En cas de doute, la fiche entrante attend dans la file « Doublons possibles » : l\'application ne fusionne jamais deux navires sur le seul nom et le seul pavillon.', '<strong>One single record per vessel.</strong> Whatever the origin of a piece of information (manual entry, THEMIS, NENO, e-inspection, DPEP), it ends up in the same record. When in doubt, the incoming record waits in the “Possible duplicates” queue: the application never merges two vessels on name and flag alone.'),
        ('<strong>Tout est tracé.</strong> Chaque modification métier est une « action nommée » : l\'application vérifie votre droit, écrit la donnée, enregistre un événement dans l\'historique du navire, met à jour son état courant et inscrit une ligne dans le journal d\'audit. On sait toujours qui a fait quoi, quand.', '<strong>Everything is traced.</strong> Every business change is a “named action”: the application checks your right, writes the data, records an event in the vessel\'s history, updates its current state and adds a line to the audit log. It is always possible to know who did what, and when.'),
        ('<strong>Les décisions restent humaines.</strong> L\'application calcule des indicateurs et propose, mais aucune suite, sanction ou mesure n\'est décidée automatiquement.', '<strong>Decisions stay human.</strong> The application calculates indicators and suggests, but no follow-up, sanction or measure is decided automatically.'),
        ('<strong>Chacun voit ce qui le concerne.</strong> Les menus et les boutons s\'adaptent au rôle et aux droits de l\'utilisateur.', '<strong>Everyone sees what concerns them.</strong> Menus and buttons adapt to the user\'s role and rights.'),
        ('<strong>Français et anglais.</strong> Tous les textes sont disponibles dans les deux langues ; le choix se fait en haut à droite de chaque écran.', '<strong>French and English.</strong> All texts are available in both languages; the choice is made at the top right of every screen.'),
        ('<strong>Le statut à feux et le score de vigilance.</strong> Chaque navire reçoit un statut (conforme, à surveiller, suspect, en infraction) et un score de 0 à 100 calculés par des règles lisibles et paramétrables ; la fiche affiche toujours les raisons.', '<strong>The traffic-light status and the vigilance score.</strong> Each vessel receives a status (compliant, to watch, suspect, infringing) and a score from 0 to 100 calculated by readable, configurable rules; the record always shows the reasons.'),
    ])
    out += img('dashboard', 'Le tableau de bord : indicateurs clés, répartition par zone, infractions par statut et fraîcheur des données.', 'The dashboard: key indicators, distribution by zone, infractions by status and data freshness.')
    return out


def interface():
    out = h1('Prise en main de l\'interface', 'Getting to know the interface')
    out += h2('Se connecter et se déconnecter', 'Signing in and out')
    out += p('Ouvrez l\'adresse de l\'application et saisissez votre e-mail et votre mot de passe. Vous arrivez sur le <strong>Tableau de bord</strong> ; le Directeur arrive sur la <strong>Vue flotte</strong>. Votre nom en haut à droite ouvre le menu utilisateur, dont l\'entrée de déconnexion. Pensez à vous déconnecter sur un poste partagé.',
             'Open the application address and enter your e-mail and password. You land on the <strong>Dashboard</strong>; the Director lands on the <strong>Fleet view</strong>. Your name at the top right opens the user menu, which holds the sign-out entry. Remember to sign out on a shared workstation.')
    out += h2('Les zones de l\'écran', 'The areas of the screen')
    out += ul([
        ('<strong>Barre orange du haut</strong> : un onglet par grande famille (Pilotage, Surveillance, Référentiel, Contrôles, Administration). À droite : la cloche des notifications, la recherche, le plein écran, le choix de langue et votre menu.', '<strong>Orange bar at the top</strong>: one tab per major family (Overview, Surveillance, Registry, Controls, Administration). On the right: the notification bell, search, full screen, the language choice and your menu.'),
        ('<strong>Menu latéral</strong> : les écrans de la famille choisie, chacun dans une pastille. L\'écran courant est en orange plein. Un <strong>rond orange avec un nombre</strong> indique des éléments en attente (par exemple les doublons possibles à trancher).', '<strong>Side menu</strong>: the screens of the chosen family, each in a pill. The current screen is solid orange. An <strong>orange circle with a number</strong> indicates items waiting (for example possible duplicates to settle).'),
        ('<strong>Bandeau noir de titre</strong> : le nom de l\'écran et son rôle. Sous lui, le contenu de la page.', '<strong>Black title band</strong>: the screen name and its purpose. Below it, the page content.'),
        ('<strong>Bandeau « Santé du système »</strong> (Directeur, Chef d\'équipe, Administrateur) : s\'affiche en rouge quand des sources de données demandent attention ; « Voir les connecteurs » mène au détail.', '<strong>“System health” banner</strong> (Director, Team lead, Administrator): shown in red when data sources need attention; “See the connectors” leads to the detail.'),
    ])
    out += img('vessels', 'La liste des navires : barre orange, menu latéral en pastilles, tableau encadré avec barre d\'outils et filtres par colonne.', 'The vessel list: orange bar, pill side menu, framed table with toolbar and column filters.')
    out += h2('Les tableaux', 'Tables')
    out += p('Les listes (navires, licences, infractions, missions…) suivent toutes la même présentation :', 'Lists (vessels, licences, infractions, missions…) all follow the same layout:')
    out += ul([
        ('<strong>La barre d\'outils</strong>, au-dessus du tableau : choix du nombre de lignes affichées (10, 20, 50 ou 100), recherche, filtres de dates ou de catégories, lien « Réinitialiser » quand un filtre est actif. Sur la liste des navires, les boutons <em>Export CSV</em> et <em>Export PDF</em> téléchargent la liste (réservé aux profils qui ont le droit d\'export ; chaque export est journalisé).', '<strong>The toolbar</strong>, above the table: choice of the number of rows shown (10, 20, 50 or 100), search, date or category filters, a “Reset” link when a filter is active. On the vessel list, the <em>Export CSV</em> and <em>Export PDF</em> buttons download the list (reserved for profiles with the export right; every export is logged).'),
        ('<strong>Les filtres par colonne</strong>, sous l\'en-tête bleu nuit : une liste déroulante ou un champ de saisie. Le filtre s\'applique dès que vous le modifiez.', '<strong>Column filters</strong>, under the navy header: a drop-down list or an input field. The filter applies as soon as you change it.'),
        ('<strong>La pagination</strong> en bas du tableau ; la page courante est en bleu nuit.', '<strong>Pagination</strong> at the bottom of the table; the current page is shown in navy.'),
        ('Le nom d\'un navire est un lien vers sa fiche ; l\'icône en forme d\'œil à côté ouvre un <strong>aperçu rapide</strong> (voir plus bas).', 'A vessel name is a link to its record; the eye icon next to it opens a <strong>quick view</strong> (see below).'),
    ])
    out += h2('Les fenêtres (modales)', 'Windows (modals)')
    out += p('Pour que l\'application vous guide sans vous faire quitter la page, trois sortes de fenêtres s\'affichent au centre de l\'écran.', 'To guide you without making you leave the page, three kinds of window appear in the centre of the screen.')
    out += h3('1. La confirmation', '1. The confirmation')
    out += p('Avant toute action importante ou difficile à défaire (clôturer un dossier, envoyer un message, suspendre ou révoquer une licence, fusionner deux navires, valider un lot, archiver…), une fenêtre vous demande de confirmer. Elle rappelle la conséquence ; pour l\'envoi d\'un message, elle récapitule le canal et le destinataire. <strong>Annuler</strong> ne change rien.',
             'Before any important or hard-to-undo action (closing a case, sending a message, suspending or revoking a licence, merging two vessels, validating a batch, archiving…), a window asks you to confirm. It reminds you of the consequence; when sending a message, it recaps the channel and the recipient. <strong>Cancel</strong> changes nothing.')
    out += img('infraction-confirm', 'Confirmation avant de clore un dossier d\'infraction.', 'Confirmation before closing an infraction case.')
    out += img('message-confirm', 'Confirmation d\'envoi d\'un message, avec le récapitulatif du canal et du destinataire.', 'Confirmation before sending a message, with the recap of the channel and the recipient.')
    out += h3('2. L\'aperçu rapide', '2. The quick view')
    out += p('Cliquez sur l\'<strong>œil</strong> à côté du nom d\'un navire pour voir, sans quitter votre liste : son statut à feux et les raisons, sa catégorie, son statut, sa licence et son échéance, l\'état de sa balise, son numéro IMO et MMSI, sa dernière position connue et son score de vigilance. Le bouton <em>Ouvrir la fiche complète</em> mène à la fiche détaillée.',
             'Click the <strong>eye</strong> next to a vessel name to see, without leaving your list: its traffic-light status and the reasons, its category, status, licence and expiry, beacon state, IMO and MMSI numbers, last known position and vigilance score. The <em>Open the full profile</em> button leads to the detailed record.')
    out += img('infractions-quick', 'L\'aperçu rapide d\'un navire ouvert depuis la liste des infractions.', 'The quick view of a vessel opened from the infractions list.')
    out += h3('3. Le message de réussite', '3. The success message')
    out += p('Après une action marquante (clôture d\'un dossier, d\'une opération, d\'une journée de main courante, d\'une mission ; autorisation et démarrage d\'une mission ; envoi d\'un message ; suspension ou révocation d\'une licence ; archivage), une fenêtre à coche verte confirme que c\'est fait. Pour les actions plus courantes, un bandeau vert en haut de la page suffit.',
             'After a significant action (closing a case, an operation, a logbook day or a mission; authorizing and starting a mission; sending a message; suspending or revoking a licence; archiving), a green-check window confirms it is done. For more routine actions, a green banner at the top of the page is enough.')
    out += img('infraction-popup', 'Le message de réussite après la clôture d\'un dossier.', 'The success message after closing a case.')
    out += h2('Notifications', 'Notifications')
    out += p('La <strong>cloche</strong> affiche le nombre de notifications non lues ; elle clignote quand une alerte vient d\'arriver, et un message éclair apparaît brièvement. L\'écran <em>Notifications</em> (Pilotage) liste tout l\'historique et permet de tout marquer comme lu. Les alertes envoyées dépendent de votre rôle : balise silencieuse, licence qui expire, relais à acquitter, etc.',
             'The <strong>bell</strong> shows the number of unread notifications; it blinks when an alert has just arrived, and a short toast message appears. The <em>Notifications</em> screen (Overview) lists the whole history and lets you mark everything as read. The alerts sent depend on your role: silent beacon, licence expiring, relay to acknowledge, etc.')
    out += h2('La visite guidée de la page', 'The guided tour of the page')
    out += p('Sur n\'importe quel écran, le bouton <strong>Guide utilisateur</strong> du menu latéral (ou l\'icône <strong>?</strong> de la barre orange) lance une visite de <strong>la page où vous êtes</strong>. Une première fenêtre dit à quoi sert la page ; puis chaque élément important est mis en lumière, le reste de l\'écran s\'assombrit, et une bulle explique en deux lignes ce qu\'on peut y faire ou ce que fait le bouton.',
             'On any screen, the <strong>User guide</strong> button in the side menu (or the <strong>?</strong> icon in the orange bar) starts a tour of <strong>the page you are on</strong>. A first window says what the page is for; then each important element is highlighted, the rest of the screen dims, and a bubble explains in two lines what you can do there or what the button does.')
    out += ul([
        ('<strong>Suivant</strong> et <strong>Précédent</strong> (ou les flèches du clavier) font avancer ; <strong>Terminer</strong>, la croix ou la touche <kbd>Échap</kbd> ferment la visite. Le compteur « Étape 3 sur 6 » indique où vous en êtes.', '<strong>Next</strong> and <strong>Previous</strong> (or the keyboard arrows) move along; <strong>Done</strong>, the cross or the <kbd>Esc</kbd> key close the tour. The “Step 3 of 6” counter shows where you are.'),
        ('La visite ne s\'ouvre <strong>jamais seule</strong> : elle n\'apparaît que si vous la demandez.', 'The tour <strong>never opens by itself</strong>: it only appears when you ask for it.'),
        ('Elle s\'adapte à votre rôle : un bouton que vous n\'avez pas le droit d\'utiliser n\'est pas présenté.', 'It adapts to your role: a button you are not allowed to use is not presented.'),
        ('Les principaux écrans ont une visite dédiée ; les autres affichent une visite générale du menu, des onglets et des outils.', 'The main screens have a dedicated tour; the others show a general tour of the menu, tabs and tools.'),
    ])
    out += img('tour-intro', 'La fenêtre d\'introduction de la visite guidée.', 'The introduction window of the guided tour.')
    out += img('tour-step', 'Une étape de la visite : l\'élément est mis en lumière et expliqué.', 'A tour step: the element is highlighted and explained.')
    out += h2('Rechercher, langue, plein écran', 'Search, language, full screen')
    out += ul([
        ('La <strong>loupe</strong> lance une recherche transversale (navires, inspections…) limitée à ce que vos droits vous permettent de voir.', 'The <strong>magnifier</strong> runs a cross-cutting search (vessels, inspections…) limited to what your rights allow you to see.'),
        ('Le sélecteur de <strong>langue</strong> bascule entre français et anglais ; votre choix est mémorisé.', 'The <strong>language</strong> selector switches between French and English; your choice is remembered.'),
        ('Les cartes (blocs avec un titre orange ou bleu nuit) se <strong>replient</strong> avec le tiret et s\'agrandissent avec l\'icône de plein écran.', 'Cards (blocks with an orange or navy title) <strong>collapse</strong> with the dash and expand with the full-screen icon.'),
    ])
    out += img('notifications', 'L\'écran des notifications.', 'The notifications screen.')
    return out


ROWS = [
    (('Consulter le tableau de bord et ses notifications', 'View the dashboard and notifications'), 1, 1, 1, 1),
    (('Consulter navires, licences, référentiels, inspections, captures, infractions, missions, MREP, mouvements, signalements, communications', 'View vessels, licences, registries, inspections, catches, infractions, missions, MREP, movements, reports, communications'), 1, 1, 1, 0),
    (('Saisir une observation de quart, une ligne de main courante, un mouvement, une déclaration ZEE', 'Enter a shift observation, a logbook line, a movement, an EEZ declaration'), 1, 1, 1, 0),
    (('Ouvrir une opération, programmer une mission, enregistrer un signalement, envoyer un message ou une note', 'Open an operation, schedule a mission, record a witness report, send a message or a note'), 1, 1, 1, 0),
    (('Saisir une balise, une avarie, une déclaration ERS, un créneau de contrôle', 'Enter a beacon, a failure, an ERS declaration, a control slot'), 1, 1, 1, 0),
    (('Créer ou modifier une fiche navire ; trancher les doublons', 'Create or edit a vessel record; settle duplicates'), 0, 1, 1, 0),
    (('Suivre et clôturer une opération ; clôturer la journée de main courante', 'Follow and close an operation; close the logbook day'), 0, 1, 1, 0),
    (('Prendre le service (relève), acquitter un relais, confirmer un suivi renforcé, ouvrir et confirmer un créneau de contrôle, lancer un balayage', 'Take over the shift (handover), acknowledge a relay, confirm enhanced monitoring, open and confirm a control slot, run a sweep'), 1, 1, 1, 0),
    (('Planifier les rotations, tenir la liste du Directeur, clore une ligne de veille, créer ou lever une consigne permanente', 'Plan rotations, maintain the Director\'s list, close a watch line, create or end a standing instruction'), 0, 1, 1, 0),
    (('Gérer les licences : enregistrer, importer, valider, suspendre, révoquer, publier la liste autorisée', 'Manage licences: record, import, validate, suspend, revoke, publish the authorized list'), 0, 1, 1, 0),
    (('Ouvrir et instruire un dossier d\'infraction ; décider des suites, paiements, recours ; clore', 'Open and investigate an infraction case; decide follow-ups, payments, appeals; close'), 0, 1, 1, 0),
    (('Décider d\'une mesure conservatoire', 'Decide on a precautionary measure'), 0, 0, 1, 0),
    (('Autoriser une mission (selon le type de mission)', 'Authorize a mission (depending on mission type)'), 0, 1, 1, 0),
    (('Autoriser une mission en tant que Directeur', 'Authorize a mission as Director'), 0, 0, 1, 0),
    (('Gérer modèles de messages et règles d\'envoi ; déclencher l\'agent IA', 'Manage message templates and dispatch rules; trigger the AI agent'), 0, 1, 1, 0),
    (('Vue flotte et indicateurs détaillés', 'Fleet view and detailed indicators'), 0, 1, 1, 0),
    (('Rapports programmés, exports, archives', 'Scheduled reports, exports, archives'), 0, 1, 1, 0),
    (('Voir l\'état des connecteurs et le bandeau « Santé du système »', 'See the connector status and the “System health” banner'), 0, 1, 1, 1),
    (('Synchroniser et configurer les connecteurs', 'Synchronise and configure connectors'), 0, 0, 0, 1),
    (('Comptes, rôles, groupes, droits limités, paramètres, règles d\'alerte, listes, champs extensibles', 'Accounts, roles, groups, limited rights, settings, alert rules, lists, extensible fields'), 0, 0, 0, 1),
    (('Nomenclature des infractions, activation de l\'agent IA, journal d\'audit', 'Infraction nomenclature, AI agent activation, audit log'), 0, 0, 0, 1),
]


def roles():
    dot = lambda v: '<span class="tag tag-g">●</span>' if v else '<span style="color:#b9bfd3">—</span>'
    out = h1('Rôles et droits', 'Roles and rights')
    out += p('L\'application distingue <strong>quatre rôles</strong>. Les rôles métier s\'emboîtent : le <strong>Directeur</strong> a tous les droits du <strong>Chef d\'équipe</strong>, qui a tous ceux de l\'<strong>Agent</strong>. L\'<strong>Administrateur</strong> est à part : il gère la technique mais n\'a aucun droit de décision métier.',
             'The application distinguishes <strong>four roles</strong>. The business roles nest into each other: the <strong>Director</strong> has all the rights of the <strong>Team lead</strong>, who has all those of the <strong>Agent</strong>. The <strong>Administrator</strong> stands apart: they manage the technical side but have no business decision right.')
    out += table([('Rôle', 'Role'), ('En une phrase', 'In one sentence'), ('Arrive sur', 'Lands on')], [
        [('<strong>Agent</strong>', '<strong>Agent</strong>'), ('Opérateur du CSP ou inspecteur (un inspecteur est un Agent titulaire d\'une habilitation) : il observe, saisit, consulte et signale.', 'CSP operator or inspector (an inspector is an Agent holding an accreditation): they observe, enter, consult and report.'), ('Tableau de bord', 'Dashboard')],
        [('<strong>Chef d\'équipe</strong>', '<strong>Team lead</strong>'), ('Responsable de l\'équipe de quart : il pilote la journée, valide, clôture, instruit et autorise.', 'Head of the shift team: they run the day, validate, close, investigate and authorize.'), ('Tableau de bord', 'Dashboard')],
        [('<strong>Directeur</strong>', '<strong>Director</strong>'), ('Voit tout et prend les décisions finales ; seul à décider des mesures conservatoires.', 'Sees everything and takes the final decisions; the only one to decide precautionary measures.'), ('Vue flotte', 'Fleet view')],
        [('<strong>Administrateur</strong>', '<strong>Administrator</strong>'), ('Gère comptes, rôles, paramètres, connecteurs et audit ; aucune décision métier.', 'Manages accounts, roles, settings, connectors and audit; no business decision.'), ('Tableau de bord', 'Dashboard')],
    ], [18, 62, 20])
    out += h2('Ce que chaque rôle peut faire', 'What each role can do')
    out += table([('Capacité', 'Capability'), ('Agent', 'Agent'), ('Chef d\'équipe', 'Team lead'), ('Directeur', 'Director'), ('Admin.', 'Admin.')],
                 [[(f'{r[0][0]}', f'{r[0][1]}'), dot(r[1]), dot(r[2]), dot(r[3]), dot(r[4])] for r in ROWS], [52, 10, 14, 12, 12])
    out += note('info', 'Cette matrice présente les droits livrés par défaut. L\'Administrateur peut les ajuster depuis <em>Administration › Rôles et permissions</em> (annexe A pour le détail permission par permission).', 'This matrix shows the rights delivered by default. The Administrator can adjust them from <em>Administration › Roles and permissions</em> (annex A for the permission-by-permission detail).')
    out += h2('Groupes et droits limités', 'Groups and limited rights')
    out += p('Au-delà du rôle, l\'Administrateur dispose de deux outils pour affiner :', 'Beyond the role, the Administrator has two tools to refine access:')
    out += ul([
        ('Les <strong>groupes de permissions</strong> (par exemple « Gestion FNP / licences », « Instruction ») : une personne reçoit les droits d\'un groupe en plus de ceux de son rôle, sans changer de rôle.', '<strong>Permission groups</strong> (for example “FNP / licence management”, “Investigation”): a person receives the rights of a group on top of those of their role, without changing role.'),
        ('Les <strong>droits limités</strong> : un droit peut être accordé seulement pour une <strong>zone</strong> ou pendant une <strong>période</strong> (par exemple un renfort de trois semaines). Il cesse tout seul à l\'échéance.', '<strong>Limited rights</strong>: a right can be granted only for a <strong>zone</strong> or during a <strong>period</strong> (for example three weeks of reinforcement). It ends by itself at expiry.'),
    ])
    out += h2('Les menus de chaque rôle', 'The menus of each role')
    out += table([('Famille de menus', 'Menu family'), ('Agent', 'Agent'), ('Chef d\'équipe / Directeur', 'Team lead / Director'), ('Administrateur', 'Administrator')], [
        [('Pilotage', 'Overview'), ('Tableau de bord, Notifications', 'Dashboard, Notifications'), ('+ Vue flotte', '+ Fleet view'), ('Tableau de bord, Notifications', 'Dashboard, Notifications')],
        [('Surveillance', 'Surveillance'), ('Carte, zones, balises, tableau du matin, observations de quart, rotations, consignes, alertes, créneaux, main courante, balises VMS, observations, conformité, opérations, avaries, ERS, communications, déclarations ZEE, mouvements, signalements', 'Map, zones, beacons, morning board, shift observations, rotations, instructions, alerts, slots, logbook, VMS beacons, observations, conformity, operations, failures, ERS, communications, EEZ declarations, movements, witness reports'), ('Idem + Diffusion de la liste', 'Same + List distribution'), '—'],
        [('Référentiel', 'Registry'), ('Navires, Licences, Référentiels, Navires autorisés, Import par lot, Registre annuel', 'Vessels, Licences, Registries, Authorized vessels, Batch import, Annual register'), ('+ Doublons possibles', '+ Possible duplicates'), '—'],
        [('Contrôles', 'Controls'), ('Inspections, Infractions, Captures, Débarquements, Transbordements, Missions, DPEP (MREP), Refus GIES, Ciblage MREP', 'Inspections, Infractions, Catches, Landings, Transshipments, Missions, DPEP (MREP), GIES refusals, MREP targeting'), ('Idem', 'Same'), '—'],
        [('Administration', 'Administration'), '—', ('Connecteurs, Rapports programmés, Exports, Archives, Agent IA', 'Connectors, Scheduled reports, Exports, Archives, AI agent'), ('Utilisateurs, Rôles, Groupes, Connecteurs, Journal d\'audit, Listes de référence, Champs extensibles, Paramètres, Activation de l\'agent IA, Nomenclature des infractions, Règles d\'alerte', 'Users, Roles, Groups, Connectors, Audit log, Reference lists, Extensible fields, Settings, AI agent activation, Infraction nomenclature, Alert rules')],
    ], [14, 38, 24, 24])
    return out
