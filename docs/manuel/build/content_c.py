import json
import pathlib

from lib import *


def director():
    out = h1('Chapitre Directeur', 'Director chapter')
    out += p('<span class="lead">Le Directeur voit tout et décide en dernier ressort. Il a tous les droits du Chef d\'équipe, plus deux décisions qui lui sont propres : décider des <strong>mesures conservatoires</strong> et autoriser les <strong>missions qui lui sont réservées</strong>. Il arrive directement sur la <strong>Vue flotte</strong>.</span>',
             '<span class="lead">The Director sees everything and decides in the last resort. They hold all the Team lead\'s rights, plus two decisions of their own: deciding <strong>precautionary measures</strong> and authorizing the <strong>missions reserved for them</strong>. They land directly on the <strong>Fleet view</strong>.</span>')
    out += h2('La vue flotte', 'The fleet view')
    out += p('La vue flotte donne la situation d\'ensemble en un coup d\'œil : des indicateurs cliquables (navires actifs en ZEE, balises silencieuses, licences qui expirent, infractions en cours, opérations ouvertes, missions en cours), la répartition des navires par statut à feux (conforme, à surveiller, suspect, en infraction) et le classement des navires par score de vigilance. Chaque indicateur ouvre la liste des navires qui le composent, filtrable par période, zone, pavillon, segment, statut et recherche.',
             'The fleet view gives the overall situation at a glance: clickable indicators (active vessels in the EEZ, silent beacons, expiring licences, ongoing infractions, open operations, ongoing missions), the distribution of vessels by traffic-light status (compliant, to watch, suspect, infringing) and the ranking of vessels by vigilance score. Each indicator opens the list of vessels that make it up, filterable by period, zone, flag, segment, status and search.')
    out += img('fleet', 'La vue flotte du Directeur.', 'The Director\'s fleet view.')
    out += h2('Comprendre le statut à feux et le score', 'Understanding the traffic light and the score')
    out += table([('Statut', 'Status'), ('Quand un navire l\'a', 'When a vessel has it')], [
        [('<span class="tag tag-r">En infraction</span>', '<span class="tag tag-r">Infringing</span>'), ('Au moins un dossier d\'infraction n\'est ni clos ni classé.', 'At least one infraction case is neither closed nor dismissed.')],
        [('<span class="tag tag-n">Suspect</span>', '<span class="tag tag-n">Suspect</span>'), ('Licence expirée, suspendue ou révoquée ; balise silencieuse hors avarie déclarée ; écart de déclaration ZEE non expliqué sur 30 jours ; 3 vérifications ou plus sans suite.', 'Licence expired, suspended or revoked; beacon silent without a declared failure; unexplained EEZ declaration gap over 30 days; 3 or more checks with no follow-up.')],
        [('<span class="tag">À surveiller</span>', '<span class="tag">To watch</span>'), ('Avarie de balise en cours ; suivi renforcé ouvert ; licence active qui expire dans 30 jours ou moins.', 'Beacon failure ongoing; enhanced monitoring open; active licence expiring within 30 days or less.')],
        [('<span class="tag tag-g">Conforme</span>', '<span class="tag tag-g">Compliant</span>'), ('Aucun des cas ci-dessus. Une licence non renseignée n\'influence pas le statut.', 'None of the cases above. A licence not recorded does not influence the status.')],
    ], [22, 78])
    out += p('Le <strong>score de vigilance</strong> (0 à 100) additionne des composantes lisibles — silence de balise en cours, silences sur 12 mois, licence expirée ou proche de l\'échéance, infractions ouvertes ou sur 12 mois, vérifications répétées, écarts ZEE, messages sans réponse, navire vu mais non inspecté — chacune plafonnée. Niveaux : faible (moins de 25), modéré (25 à 49), élevé (50 et plus). Chaque composante est affichée avec ses occurrences et ses points.',
             'The <strong>vigilance score</strong> (0 to 100) adds up readable components — silence of a beacon in progress, silences over 12 months, licence expired or close to expiry, open infractions or over 12 months, repeated checks, EEZ gaps, unanswered messages, vessel seen but not inspected — each one capped. Levels: low (under 25), moderate (25 to 49), high (50 and above). Each component is shown with its occurrences and points.')
    out += note('info', 'Les seuils (30 jours, 3 vérifications, 4 heures de silence) se modifient dans <em>Administration › Paramètres</em> sans intervention d\'un développeur.', 'The thresholds (30 days, 3 checks, 4 hours of silence) can be changed in <em>Administration › Settings</em> without a developer.')
    out += h2('Les décisions propres au Directeur', 'The Director\'s own decisions')
    out += proc(('Décider d\'une mesure conservatoire', 'Decide a precautionary measure'), ('Directeur uniquement', 'Director only'), [
        ('Ouvrez le dossier dans <strong>Contrôles › Infractions</strong> ; la section des mesures liste celles que le Chef d\'équipe a proposées (suspension de licence, retrait…).', 'Open the case in <strong>Controls › Infractions</strong>; the measures section lists those proposed by the Team lead (licence suspension, withdrawal…).'),
        ('Cliquez <strong>Décider</strong> sur la mesure et confirmez dans la fenêtre.', 'Click <strong>Decide</strong> on the measure and confirm in the window.'),
        ('Si la mesure touche la licence, utilisez le lien « Effet sur la licence » pour suspendre ou révoquer dans le module Licences.', 'If the measure affects the licence, use the “Effect on the licence” link to suspend or revoke in the Licences module.'),
    ], ('La décision est consignée avec votre nom et la date ; l\'effet sur la licence reste un acte distinct et délibéré.', 'The decision is recorded with your name and date; the effect on the licence remains a separate, deliberate act.'))
    out += proc(('Autoriser une mission réservée au Directeur', 'Authorize a mission reserved for the Director'), ('Directeur', 'Director'), [
        ('Ouvrez la mission programmée dans <strong>Contrôles › Missions</strong>.', 'Open the scheduled mission in <strong>Controls › Missions</strong>.'),
        ('Cliquez <strong>Autoriser</strong> et confirmez. La fiche affiche « Autorisée par … (rôle) le … ».', 'Click <strong>Authorize</strong> and confirm. The record shows “Authorized by … (role) on …”.'),
    ])
    out += h2('Piloter : rapports, exports, archives, agent IA', 'Steering: reports, exports, archives, AI agent')
    out += ul([
        ('<strong>Rapports programmés</strong> : des rapports figés par période, produits chaque matin à 05 h et envoyés au destinataire désigné.', '<strong>Scheduled reports</strong>: reports frozen per period, produced every morning at 05:00 and sent to the designated recipient.'),
        ('<strong>Exports</strong> (opérations, navires, main courante) en CSV ou PDF, chacun inscrit au journal des exports.', '<strong>Exports</strong> (operations, vessels, logbook) as CSV or PDF, each one written to the export log.'),
        ('<strong>Archives</strong> : les opérations closes depuis plus de cinq ans (réglable) passent en lecture seule ; l\'archivage se lance depuis l\'écran dédié, après confirmation.', '<strong>Archives</strong>: operations closed for more than five years (adjustable) become read-only; archiving is launched from the dedicated screen, after confirmation.'),
        ('<strong>Agent IA</strong> : la première action disponible est la création d\'une note interne ; l\'écran « Agent IA » liste toutes les demandes qui lui ont été faites, pour supervision. Son activation est un réglage de l\'Administrateur.', '<strong>AI agent</strong>: the first available action is creating an internal note; the “AI agent” screen lists all the requests made to it, for supervision. Its activation is an Administrator setting.'),
    ])
    out += img('reports', 'Les rapports programmés.', 'Scheduled reports.')
    out += img('archives', 'L\'archivage des opérations anciennes.', 'Archiving of old operations.')
    out += img('ai-agent', 'La supervision de l\'agent IA.', 'Supervision of the AI agent.')
    return out


def admin():
    out = h1('Chapitre Administrateur', 'Administrator chapter')
    out += p('<span class="lead">L\'Administrateur garde l\'application en état de marche : comptes, droits, paramètres, sources de données, audit. Il n\'a volontairement <strong>aucun droit de décision métier</strong> : il ne peut ni clôturer un dossier, ni suspendre une licence, ni valider une mission.</span>',
             '<span class="lead">The Administrator keeps the application running: accounts, rights, settings, data sources, audit. They deliberately have <strong>no business decision right</strong>: they can neither close a case, nor suspend a licence, nor validate a mission.</span>')
    out += h2('Ce que vous voyez', 'What you see')
    out += p('<strong>Pilotage</strong> (tableau de bord, notifications) et <strong>Administration</strong> : Utilisateurs, Rôles et permissions, Groupes de permissions, Connecteurs, Journal d\'audit, Listes de référence, Champs extensibles, Paramètres, Activation de l\'agent IA, Nomenclature des infractions, Règles d\'alerte.',
             '<strong>Overview</strong> (dashboard, notifications) and <strong>Administration</strong>: Users, Roles and permissions, Permission groups, Connectors, Audit log, Reference lists, Extensible fields, Settings, AI agent activation, Infraction nomenclature, Alert rules.')
    out += h2('Procédures pas à pas', 'Step-by-step procedures')
    out += proc(('Créer un compte utilisateur', 'Create a user account'), ('Administrateur', 'Administrator'), [
        ('Ouvrez <strong>Administration › Utilisateurs</strong> puis le bouton de création.', 'Open <strong>Administration › Users</strong> then the create button.'),
        ('Saisissez le nom, l\'e-mail, un mot de passe respectant les règles de sécurité, et choisissez le <strong>rôle</strong> (Agent, Chef d\'équipe, Directeur, Administrateur ou un rôle personnalisé) et, si besoin, des <strong>groupes</strong>.', 'Enter the name, e-mail, a password meeting the security rules, and choose the <strong>role</strong> (Agent, Team lead, Director, Administrator or a custom role) and, if needed, <strong>groups</strong>.'),
        ('Enregistrez. Pour retirer l\'accès, désactivez le compte : la personne est déconnectée immédiatement. Vous ne pouvez pas désactiver votre propre compte.', 'Save. To remove access, deactivate the account: the person is signed out immediately. You cannot deactivate your own account.'),
    ])
    out += img('users', 'La liste des utilisateurs.', 'The users list.')
    out += proc(('Créer un rôle personnalisé et choisir ses permissions', 'Create a custom role and choose its permissions'), ('Administrateur', 'Administrator'), [
        ('Ouvrez <strong>Administration › Rôles et permissions</strong> puis le bouton de création.', 'Open <strong>Administration › Roles and permissions</strong> then the create button.'),
        ('Donnez un nom unique, éventuellement un rôle <strong>parent</strong> dont le nouveau rôle hérite, puis cochez les permissions voulues.', 'Give a unique name, optionally a <strong>parent</strong> role the new role inherits from, then tick the permissions wanted.'),
        ('Enregistrez, puis affectez le rôle aux utilisateurs.', 'Save, then assign the role to users.'),
    ])
    out += ul([
        ('Les rôles système (Administrateur, Directeur, Chef d\'équipe, Agent) gardent leur nom et leur parent, mais leurs permissions sont modifiables — sauf que l\'Administrateur ne peut pas perdre les siennes.', 'The system roles (Administrator, Director, Team lead, Agent) keep their name and parent, but their permissions are editable — except that the Administrator cannot lose theirs.'),
        ('Un rôle personnalisé ne peut être supprimé que s\'il n\'est plus utilisé et n\'a pas de rôle enfant ; la suppression passe par une confirmation.', 'A custom role can only be deleted if it is no longer used and has no child role; deletion goes through a confirmation.'),
    ])
    out += img('roles', 'Les rôles et la matrice des permissions.', 'Roles and the permissions matrix.')
    out += proc(('Donner un droit limité à une zone ou une période', 'Give a right limited to a zone or period'), ('Administrateur', 'Administrator'), [
        ('Ouvrez la fiche de l\'utilisateur, du rôle ou du groupe concerné et repérez la section des permissions limitées.', 'Open the record of the user, role or group concerned and find the limited permissions section.'),
        ('Choisissez la permission, puis une <strong>zone</strong> ou une <strong>période</strong> (ou les deux, avec une plage cohérente).', 'Choose the permission, then a <strong>zone</strong> or a <strong>period</strong> (or both, with a consistent range).'),
        ('Pour le retirer : <strong>Retirer</strong> puis confirmation ; l\'effet est immédiat.', 'To remove it: <strong>Remove</strong> then confirmation; the effect is immediate.'),
    ])
    out += img('groups', 'Les groupes de permissions.', 'Permission groups.')
    out += proc(('Modifier un seuil ou un délai', 'Change a threshold or delay'), ('Administrateur', 'Administrator'), [
        ('Ouvrez <strong>Administration › Paramètres</strong>.', 'Open <strong>Administration › Settings</strong>.'),
        ('Repérez le réglage (silence de balise, alertes de licence, nombre de vérifications, horaires de quart, plafond de messages par jour, durée avant archivage…), modifiez-le dans les bornes autorisées et enregistrez.', 'Find the setting (beacon silence, licence alerts, number of checks, shift hours, daily message cap, time before archiving…), change it within the allowed bounds and save.'),
        ('Un réglage égal à la valeur par défaut n\'est pas stocké ; « Rétablir les valeurs par défaut » les ramène toutes.', 'A setting equal to the default value is not stored; “Restore defaults” brings them all back.'),
    ], ('Le changement s\'applique immédiatement, sans redémarrage et sans développeur.', 'The change applies immediately, without restart and without a developer.'))
    out += img('settings', 'Les paramètres modifiables.', 'The editable settings.')
    out += note('info', 'C\'est le moyen prévu pour adapter les règles de l\'application : les <strong>paramètres</strong> (seuils, délais, interrupteurs), les <strong>listes de référence</strong> et la <strong>nomenclature des infractions</strong> se complètent depuis l\'écran, sans programmation. Rien n\'impose de passer par un développeur pour ces ajustements.', 'This is the intended way to adapt the application\'s rules: <strong>settings</strong> (thresholds, delays, switches), <strong>reference lists</strong> and the <strong>infraction nomenclature</strong> are completed from the screen, without programming. Nothing requires a developer for these adjustments.')
    out += proc(('Compléter une liste de référence ou la nomenclature des infractions', 'Complete a reference list or the infraction nomenclature'), ('Administrateur', 'Administrator'), [
        ('Ouvrez <strong>Administration › Listes de référence</strong> (ou <strong>Nomenclature des infractions</strong>).', 'Open <strong>Administration › Reference lists</strong> (or <strong>Infraction nomenclature</strong>).'),
        ('Ajoutez une valeur ou modifiez une existante ; elle est aussitôt proposée dans les formulaires concernés.', 'Add a value or edit an existing one; it is immediately offered in the forms concerned.'),
        ('Pour une information qui n\'existe pas encore sur une fiche, créez un <strong>champ extensible</strong> dans <em>Champs extensibles</em>.', 'For information that does not yet exist on a record, create an <strong>extensible field</strong> in <em>Extensible fields</em>.'),
    ])
    out += img('reference-lists', 'Les listes de référence.', 'Reference lists.')
    out += img('infraction-types', 'La nomenclature des infractions.', 'The infraction nomenclature.')
    out += proc(('Régler une règle d\'alerte', 'Adjust an alert rule'), ('Administrateur', 'Administrator'), [
        ('Ouvrez <strong>Administration › Règles d\'alerte</strong>.', 'Open <strong>Administration › Alert rules</strong>.'),
        ('Modifiez une règle (silence, géorepérage, vitesse) ou ajoutez-en une ; les règles sont évaluées toutes les 15 minutes.', 'Edit a rule (silence, geofencing, speed) or add one; rules are evaluated every 15 minutes.'),
    ])
    out += img('alert-rules', 'Les règles d\'alerte.', 'Alert rules.')
    out += proc(('Surveiller et synchroniser les connecteurs', 'Monitor and synchronise the connectors'), ('Administrateur (la consultation : aussi Chef d\'équipe et Directeur)', 'Administrator (viewing: also Team lead and Director)'), [
        ('Ouvrez <strong>Administration › Connecteurs</strong> : chaque source (e-inspection, DPEP/MREP, VMS/THEMIS, NENO) a un voyant vert, orange ou rouge et un indicateur de données périmées.', 'Open <strong>Administration › Connectors</strong>: each source (e-inspection, DPEP/MREP, VMS/THEMIS, NENO) has a green, orange or red indicator and a stale-data flag.'),
        ('Ouvrez une source pour voir ses synchronisations, ses rejets (enregistrements invalides, conservés avec leur motif) et ses erreurs ; vous pouvez en commenter, résoudre ou rouvrir.', 'Open a source to see its synchronisations, its rejections (invalid records, kept with their reason) and its errors; you can comment, resolve or reopen them.'),
        ('<strong>Synchroniser</strong> lance une synchronisation immédiate (confirmation) ; <strong>mode</strong> change le mode de fonctionnement de la source (confirmation). Une synchronisation automatique a lieu chaque heure.', '<strong>Synchronise</strong> launches an immediate synchronisation (confirmation); <strong>mode</strong> changes the source\'s operating mode (confirmation). An automatic synchronisation takes place every hour.'),
    ], ('Un rejet est soldé automatiquement quand une synchronisation ultérieure accepte l\'enregistrement.', 'A rejection is settled automatically when a later synchronisation accepts the record.'))
    out += img('connectors', 'La supervision des connecteurs.', 'Connector supervision.')
    out += proc(('Consulter le journal d\'audit', 'Consult the audit log'), ('Administrateur', 'Administrator'), [
        ('Ouvrez <strong>Administration › Journal d\'audit</strong>.', 'Open <strong>Administration › Audit log</strong>.'),
        ('Filtrez par utilisateur, par type d\'événement et par période.', 'Filter by user, by event type and by period.'),
    ], ('Vous retrouvez qui a fait quoi et quand ; le journal est protégé contre toute modification.', 'You find who did what and when; the log is protected against any modification.'))
    out += img('audit', 'Le journal d\'audit.', 'The audit log.')
    out += h2('Ce que vous ne pouvez pas faire', 'What you cannot do')
    out += ul([
        ('Toute décision métier : clôturer, décider, autoriser, suspendre, révoquer, valider.', 'Any business decision: close, decide, authorize, suspend, revoke, validate.'),
        ('Saisir ou consulter les données de surveillance au quotidien (navires, licences, observations…).', 'Enter or view day-to-day surveillance data (vessels, licences, observations…).'),
    ])
    return out


def processes():
    out = h1('Les grands processus, de bout en bout', 'The main processes, end to end')
    out += p('Ces quatre enchaînements montrent comment les rôles se passent le relais. Chaque flèche est une action tracée.', 'These four sequences show how the roles hand over to each other. Each arrow is a traced action.')
    chain = lambda steps: '<div class="chain">' + '<span class="arrow">›</span>'.join(f'<div class="link"><span class="who">{t(*s[0])}</span>{t(*s[1])}</div>' for s in steps) + '</div>'
    out += h2('De la balise silencieuse à la décision', 'From a silent beacon to a decision')
    out += chain([
        (('Application', 'Application'), ('Une règle d\'alerte détecte un silence de balise ; une alerte et une notification sont créées.', 'An alert rule detects a beacon silence; an alert and a notification are created.')),
        (('Agent', 'Agent'), ('Acquitte, vérifie, consigne les mesures, ajoute une observation.', 'Acknowledges, checks, records measures, adds an observation.')),
        (('Application', 'Application'), ('Après plusieurs vérifications, propose un suivi renforcé.', 'After several checks, suggests enhanced monitoring.')),
        (('Chef d\'équipe', 'Team lead'), ('Ouvre une opération, puis un dossier d\'infraction si nécessaire.', 'Opens an operation, then an infraction case if needed.')),
        (('Directeur', 'Director'), ('Décide des mesures conservatoires ; la licence se traite à part.', 'Decides precautionary measures; the licence is handled separately.')),
    ])
    out += h2('De la déclaration à l\'alerte ZEE', 'From a declaration to an EEZ alert')
    out += chain([
        (('Agent', 'Agent'), ('Saisit la déclaration d\'entrée ou de sortie.', 'Enters the entry or exit declaration.')),
        (('Application', 'Application'), ('Vérifie, apparie entrées et sorties, lève une alerte si une déclaration manque.', 'Checks, pairs entries and exits, raises an alert if a declaration is missing.')),
        (('Agent', 'Agent'), ('Relance ou qualifie la non-déclaration.', 'Chases or qualifies the missed declaration.')),
        (('Chef d\'équipe', 'Team lead'), ('Clôture la déclaration ou l\'alerte.', 'Closes the declaration or the alert.')),
    ])
    out += h2('De la signalisation au dossier', 'From a witness report to a case file')
    out += chain([
        (('Agent', 'Agent'), ('Enregistre le signalement reçu.', 'Records the report received.')),
        (('Chef d\'équipe', 'Team lead'), ('Évalue l\'exploitabilité, enquête, décide : classer ou transmettre.', 'Assesses usability, investigates, decides: dismiss or transmit.')),
        (('Chef d\'équipe', 'Team lead'), ('Ouvre un dossier d\'infraction ou une mission, préremplis.', 'Opens a prefilled infraction case or mission.')),
        (('Directeur', 'Director'), ('Autorise la mission ; décide des mesures et des suites.', 'Authorizes the mission; decides measures and follow-ups.')),
    ])
    out += h2('La vie d\'une mission', 'The life of a mission')
    out += chain([
        (('Agent', 'Agent'), ('Programme la mission.', 'Schedules the mission.')),
        (('Chef d\'équipe / Directeur', 'Team lead / Director'), ('Autorise (selon le type).', 'Authorizes (depending on type).')),
        (('Chef d\'équipe', 'Team lead'), ('Prépare, démarre, suit, termine.', 'Prepares, starts, follows, completes.')),
        (('Chef d\'équipe', 'Team lead'), ('Débriefe et clôture : le bilan est calculé.', 'Debriefs and closes: the review is calculated.')),
    ])
    return out


def faq():
    out = h1('Aide-mémoire et dépannage', 'Cheat sheet and troubleshooting')
    out += table([('Situation', 'Situation'), ('Que faire ?', 'What to do?')], [
        [('Je ne retrouve plus mon mot de passe.', 'I have lost my password.'), ('Demander à l\'Administrateur de le réinitialiser depuis Administration › Utilisateurs.', 'Ask the Administrator to reset it from Administration › Users.')],
        [('Une page affiche « Accès refusé ».', 'A page shows “Access denied”.'), ('Votre rôle n\'a pas le droit correspondant ; demandez-le au Chef d\'équipe ou à l\'Administrateur.', 'Your role does not have the corresponding right; ask the Team lead or the Administrator.')],
        [('Un bouton que je vois dans le manuel n\'apparaît pas.', 'A button I see in the manual does not appear.'), ('Il est réservé à un autre rôle, ou l\'élément n\'est pas dans l\'état attendu (par exemple une mission déjà démarrée).', 'It is reserved for another role, or the item is not in the expected state (for example a mission already started).')],
        [('Le bandeau rouge « Santé du système » est affiché.', 'The red “System health” banner is displayed.'), ('Une source de données demande attention : cliquez « Voir les connecteurs » ; l\'Administrateur traite les rejets ou lance une synchronisation.', 'A data source needs attention: click “See the connectors”; the Administrator handles rejections or runs a synchronisation.')],
        [('Les positions semblent anciennes.', 'The positions look old.'), ('Vérifier l\'état des balises et le voyant du connecteur VMS ; l\'Administrateur peut relancer la synchronisation.', 'Check the beacon status and the VMS connector indicator; the Administrator can run the synchronisation again.')],
        [('Une liste est vide alors qu\'elle ne devrait pas l\'être.', 'A list is empty when it should not be.'), ('Un filtre est actif : cliquez « Réinitialiser » dans la barre d\'outils.', 'A filter is active: click “Reset” in the toolbar.')],
        [('Je me suis trompé après une clôture.', 'I made a mistake after closing.'), ('Main courante : ligne rectificative. Autres dossiers : demander au Chef d\'équipe ou au Directeur ; toute correction reste tracée.', 'Logbook: rectifying line. Other files: ask the Team lead or the Director; every correction remains traced.')],
        [('Je ne reçois plus de notifications.', 'I no longer receive notifications.'), ('Ouvrez Notifications ; les alertes envoyées dépendent de votre rôle et des paramètres fixés par l\'Administrateur.', 'Open Notifications; the alerts sent depend on your role and the settings set by the Administrator.')],
        [('La page est affichée dans la mauvaise langue.', 'The page is shown in the wrong language.'), ('Utilisez le sélecteur de langue en haut à droite.', 'Use the language selector at the top right.')],
    ], [38, 62])
    out += h2('Raccourcis utiles', 'Useful shortcuts')
    out += ul([
        ('Bouton Guide utilisateur (ou icône ?) : visite guidée de la page courante.', 'User guide button (or ? icon): guided tour of the current page.'),
        ('Œil à côté d\'un navire : aperçu rapide sans quitter la liste.', 'Eye next to a vessel: quick view without leaving the list.'),
        ('Rond orange du menu : éléments en attente.', 'Orange circle on the menu: items waiting.'),
        ('Icône de plein écran d\'une carte : agrandir un tableau ou un graphique ; tiret : replier.', 'Full-screen icon on a card: enlarge a table or a chart; dash: collapse.'),
        ('Onglet Chronologie d\'une fiche navire : tout l\'historique d\'un navire en une ligne de temps.', 'Timeline tab of a vessel record: a vessel\'s whole history on one timeline.'),
    ])
    return out


def glossary():
    out = h1('Glossaire', 'Glossary', numbered=False)
    rows = [
        ('CSP', 'CSP', 'Centre de Surveillance des Pêches : l\'utilisateur de l\'application.', 'Fisheries Monitoring Centre: the user of the application.'),
        ('MIRAH', 'MIRAH', 'Ministère de tutelle du CSP.', 'Ministry overseeing the CSP.'),
        ('ZEE', 'EEZ', 'Zone économique exclusive ; un navire étranger y déclare son entrée et sa sortie.', 'Exclusive economic zone; a foreign vessel declares its entry and exit there.'),
        ('VMS', 'VMS', 'Suivi des navires par balise satellite ; les positions et alertes viennent de THEMIS.', 'Vessel tracking by satellite beacon; positions and alerts come from THEMIS.'),
        ('AIS / Sat-AIS', 'AIS / Sat-AIS', 'Identification automatique des navires, par radio ou par satellite.', 'Automatic vessel identification, by radio or satellite.'),
        ('ERS', 'ERS', 'Déclarations électroniques de captures ; l\'écart avec le constat d\'inspection est comparé à une tolérance.', 'Electronic catch declarations; the gap with the inspection finding is compared to a tolerance.'),
        ('FNP', 'FNP', 'Registre dans lequel un navire est inscrit ou radié.', 'Register in which a vessel is entered or struck off.'),
        ('THEMIS', 'THEMIS', 'Plateforme VMS : positions, alertes, liste des navires à surveiller du Directeur.', 'VMS platform: positions, alerts, the Director\'s list of vessels to watch.'),
        ('NENO', 'NENO', 'Source des vedettes (navires patrouilleurs).', 'Source of patrol vessels.'),
        ('e-inspection', 'e-inspection', 'Application d\'inspection lue en sens unique : inspections, captures, débarquements, transbordements.', 'Inspection application read one way: inspections, catches, landings, transshipments.'),
        ('MREP / DPEP', 'MREP / DPEP', 'Autre application lue en lecture seule : déclaration préalable (72 h), décision d\'entrée au port, escales, notifications, refus GIES, ciblage.', 'Another application read-only: prior declaration (72 h), port entry decision, port calls, notifications, GIES refusals, targeting.'),
        ('GIES', 'GIES', 'Fichier mondial de la FAO ; le refus GIES se saisit à la main sur le site de la FAO.', 'Global FAO record; the GIES refusal is entered manually on the FAO site.'),
        ('INN', 'IUU', 'Pêche illicite, non déclarée et non réglementée.', 'Illegal, unreported and unregulated fishing.'),
        ('Main courante', 'Logbook', 'Journal de quart : chaque fait devient une ligne QUAND / COMMENT / QUOI ; clôturée en PDF figé.', 'Shift journal: each fact becomes a WHEN / HOW / WHAT line; closed as a frozen PDF.'),
        ('Tableau du matin', 'Morning board', 'Vue d\'ouverture : navires en veille, consignes, observations ouvertes, relais.', 'Opening view: vessels on watch, instructions, open observations, relays.'),
        ('Relais / relève', 'Relay / handover', 'Transmission des observations à l\'équipe suivante, avec accusé de lecture.', 'Passing observations to the next team, with acknowledgement.'),
        ('Suivi renforcé', 'Enhanced monitoring', 'Surveillance accrue d\'un navire d\'intérêt ou suspect.', 'Increased surveillance of a vessel of interest or suspect.'),
        ('Créneau de contrôle', 'Control slot', 'Photo des positions à un instant donné, avec trois tests assistés.', 'Photo of positions at a given moment, with three assisted tests.'),
        ('Statut à feux', 'Traffic-light status', 'Conforme, à surveiller, suspect, en infraction.', 'Compliant, to watch, suspect, infringing.'),
        ('Score de vigilance', 'Vigilance score', 'Note de 0 à 100 faite de composantes lisibles.', 'Score from 0 to 100 made of readable components.'),
        ('Action nommée', 'Named action', 'Toute écriture métier : droit vérifié, donnée écrite, événement publié, état mis à jour, audit.', 'Any business write: right checked, data written, event published, state updated, audit.'),
        ('Unité rencontrée', 'Encountered unit', 'Navire croisé pendant une mission, à trois niveaux : vue, approchée, contrôlée.', 'Vessel met during a mission, at three levels: sighted, approached, inspected.'),
        ('PV', 'Violation report', 'Procès-verbal d\'infraction, figé en PDF.', 'Infraction report, frozen as a PDF.'),
    ]
    out += table([('Terme', 'Term'), ('Définition', 'Definition')], [[f'<strong>{t(r[0], r[1])}</strong>', (r[2], r[3])] for r in rows], [24, 76])
    return out


def annex():
    perms = json.loads((pathlib.Path(__file__).parent / 'perms.json').read_text(encoding='utf-8'))
    dot = lambda ok: '<span class="tag tag-g">●</span>' if ok else '<span style="color:#b9bfd3">—</span>'
    rows = []
    for item in perms:
        label = item['fr'] if LANG == 'fr' else item['en']
        roles = item['roles']
        rows.append([f'<code>{item["code"]}</code>', label, dot('agent' in roles), dot('team_lead' in roles), dot('director' in roles), dot('admin' in roles)])
    out = h1('Annexe A — Permissions par rôle', 'Annex A — Permissions by role', numbered=False)
    out += p('Droits accordés par défaut à chaque rôle système (l\'héritage Directeur → Chef d\'équipe → Agent est appliqué). L\'Administrateur peut les modifier dans l\'application.', 'Rights granted by default to each system role (the Director → Team lead → Agent inheritance is applied). The Administrator can change them in the application.')
    out += table([('Code', 'Code'), ('Droit', 'Right'), ('Agent', 'Agent'), ('Chef', 'Lead'), ('Dir.', 'Dir.'), ('Adm.', 'Adm.')], rows, [24, 44, 8, 8, 8, 8])
    out += h1('Annexe B — Aide-mémoire des écrans par rôle', 'Annex B — Screens cheat sheet by role', numbered=False)
    out += table([('Je veux…', 'I want to…'), ('Je vais dans…', 'I go to…'), ('Rôle minimum', 'Minimum role')], [
        [('Comprendre la page où je suis', 'Understand the page I am on'), ('Bouton Guide utilisateur (menu latéral) ou icône ?', 'User guide button (side menu) or ? icon'), ('Agent', 'Agent')],
        [('Voir la situation d\'ensemble', 'See the overall situation'), ('Pilotage › Tableau de bord / Vue flotte', 'Overview › Dashboard / Fleet view'), ('Agent / Chef d\'équipe', 'Agent / Team lead')],
        [('Trouver un navire', 'Find a vessel'), ('Référentiel › Navires', 'Registry › Vessels'), ('Agent', 'Agent')],
        [('Prendre mon poste', 'Start my shift'), ('Surveillance › Tableau du matin, Rotations et relèves', 'Surveillance › Morning board, Rotations and handovers'), ('Agent', 'Agent')],
        [('Noter une observation', 'Note an observation'), ('Surveillance › Observations de quart', 'Surveillance › Shift observations'), ('Agent', 'Agent')],
        [('Tenir la main courante', 'Keep the logbook'), ('Surveillance › Main courante', 'Surveillance › Logbook'), ('Agent (clôture : Chef d\'équipe)', 'Agent (closing: Team lead)')],
        [('Traiter une alerte de balise', 'Handle a beacon alert'), ('Surveillance › Alertes', 'Surveillance › Alerts'), ('Agent', 'Agent')],
        [('Saisir une déclaration ZEE', 'Enter an EEZ declaration'), ('Surveillance › Déclarations ZEE', 'Surveillance › EEZ declarations'), ('Agent', 'Agent')],
        [('Enregistrer un signalement', 'Record a witness report'), ('Surveillance › Signalements', 'Surveillance › Witness reports'), ('Agent', 'Agent')],
        [('Trancher un doublon', 'Settle a duplicate'), ('Référentiel › Doublons possibles', 'Registry › Possible duplicates'), ('Chef d\'équipe', 'Team lead')],
        [('Gérer une licence', 'Manage a licence'), ('Référentiel › Licences', 'Registry › Licences'), ('Chef d\'équipe', 'Team lead')],
        [('Instruire une infraction', 'Investigate an infraction'), ('Contrôles › Infractions', 'Controls › Infractions'), ('Chef d\'équipe', 'Team lead')],
        [('Décider d\'une mesure conservatoire', 'Decide a precautionary measure'), ('Contrôles › Infractions › dossier', 'Controls › Infractions › case'), ('Directeur', 'Director')],
        [('Programmer / autoriser une mission', 'Schedule / authorize a mission'), ('Contrôles › Missions', 'Controls › Missions'), ('Agent / Chef d\'équipe / Directeur', 'Agent / Team lead / Director')],
        [('Exporter, programmer un rapport', 'Export, schedule a report'), ('Administration › Exports, Rapports programmés', 'Administration › Exports, Scheduled reports'), ('Chef d\'équipe', 'Team lead')],
        [('Créer un utilisateur, un rôle, un groupe', 'Create a user, role, group'), ('Administration › Utilisateurs, Rôles, Groupes', 'Administration › Users, Roles, Groups'), ('Administrateur', 'Administrator')],
        [('Changer un seuil', 'Change a threshold'), ('Administration › Paramètres', 'Administration › Settings'), ('Administrateur', 'Administrator')],
        [('Voir qui a fait quoi', 'See who did what'), ('Administration › Journal d\'audit', 'Administration › Audit log'), ('Administrateur', 'Administrator')],
    ], [34, 46, 20])
    return out
