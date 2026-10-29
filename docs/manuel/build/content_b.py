from lib import *


def agent():
    out = h1('Chapitre Agent', 'Agent chapter')
    out += p('<span class="lead">L\'Agent est les yeux et les mains du CSP : il observe, enregistre ce qu\'il voit, consulte les fiches et signale ce qui doit remonter. Il n\'a pas à clôturer, décider ni administrer : c\'est le rôle du Chef d\'équipe et du Directeur.</span>',
             '<span class="lead">The Agent is the eyes and hands of the CSP: they observe, record what they see, consult records and flag what must be escalated. They do not close, decide or administer: that is the role of the Team lead and the Director.</span>')
    out += h2('Une journée type', 'A typical day')
    out += table([('Moment', 'Moment'), ('Ce que vous faites', 'What you do'), ('Où', 'Where')], [
        [('Prise de poste', 'Start of shift'), ('Lire le tableau du matin, accuser lecture des relais de l\'équipe précédente, prendre le service.', 'Read the morning board, acknowledge the previous team\'s relays, take over the shift.'), ('Surveillance › Tableau du matin, Rotations et relèves', 'Surveillance › Morning board, Rotations and handovers')],
        [('Pendant le quart', 'During the shift'), ('Surveiller la carte et les alertes, vérifier les navires, saisir les observations et les lignes de main courante.', 'Watch the map and alerts, check vessels, enter observations and logbook lines.'), ('Surveillance › Carte, Alertes, Observations de quart, Main courante', 'Surveillance › Map, Alerts, Shift observations, Logbook')],
        [('Au fil de l\'eau', 'As things come up'), ('Enregistrer une déclaration ZEE, un mouvement portuaire, un signalement de témoin ; ouvrir une opération pour un fait complexe.', 'Record an EEZ declaration, a port movement, a witness report; open an operation for a complex fact.'), ('Surveillance › Déclarations ZEE, Mouvements, Signalements, Opérations', 'Surveillance › EEZ declarations, Movements, Witness reports, Operations')],
        [('Fin de quart', 'End of shift'), ('Relayer les observations à transmettre ; le Chef d\'équipe clôture la journée.', 'Relay the observations to pass on; the Team lead closes the day.'), ('Observations de quart, Main courante', 'Shift observations, Logbook')],
    ], [18, 52, 30])
    out += h2('Ce que vous voyez', 'What you see')
    out += p('Quatre familles de menus : <strong>Pilotage</strong> (tableau de bord, notifications), <strong>Surveillance</strong>, <strong>Référentiel</strong> (navires, licences, référentiels, navires autorisés, import, registre) et <strong>Contrôles</strong>. Vous n\'avez ni Administration, ni rapports, ni vue flotte, ni la file des doublons.',
             'Four menu families: <strong>Overview</strong> (dashboard, notifications), <strong>Surveillance</strong>, <strong>Registry</strong> (vessels, licences, registries, authorized vessels, import, register) and <strong>Controls</strong>. You have no Administration, no reports, no fleet view and no duplicates queue.')
    out += img('board', 'Le tableau du matin : navires à surveiller, observations ouvertes, relais à lire.', 'The morning board: vessels to watch, open observations, relays to read.')
    out += h2('Procédures pas à pas', 'Step-by-step procedures')
    out += proc(('Rechercher un navire et lire sa fiche', 'Find a vessel and read its record'), ('Agent et plus', 'Agent and above'), [
        ('Ouvrez <strong>Référentiel › Navires</strong>.', 'Open <strong>Registry › Vessels</strong>.'),
        ('Tapez un nom, une immatriculation, un numéro IMO ou un indicatif dans la recherche, ou filtrez par pavillon ou statut sous l\'en-tête des colonnes.', 'Type a name, registration, IMO number or call sign in the search, or filter by flag or status under the column headers.'),
        ('Cliquez l\'<strong>œil</strong> à côté du nom pour l\'aperçu rapide, ou le nom pour la fiche complète.', 'Click the <strong>eye</strong> next to the name for the quick view, or the name for the full record.'),
        ('Sur la fiche, lisez l\'en-tête (statut à feux et ses raisons, identifiants, armateur, agent, capitaine) puis parcourez les onglets : Indicateurs, Identité, Identifiants, Acteurs, Sources, Licences, Balises, Alertes, Mouvements, Déclarations ZEE, Veille, Observations, Inspections, Infractions, Captures, Missions, Communications, Documents, Commentaires, Chronologie.', 'On the record, read the header (traffic-light status and its reasons, identifiers, owner, agent, captain) then browse the tabs: Indicators, Identity, Identifiers, Actors, Sources, Licences, Beacons, Alerts, Movements, EEZ declarations, Watch, Observations, Inspections, Infractions, Catches, Missions, Communications, Documents, Comments, Timeline.'),
    ], ('Vous voyez la situation complète du navire ; les onglets affichés dépendent de vos droits.', 'You see the vessel\'s full situation; the tabs shown depend on your rights.'))
    out += img('vessel', 'La fiche d\'un navire : situation, position, puis onglets.', 'A vessel record: situation, position, then tabs.')
    out += note('tip', 'L\'onglet <em>Chronologie</em> rassemble en une seule ligne de temps tous les événements du navire (licence, alerte, inspection, infraction, message…) et se filtre par module.', 'The <em>Timeline</em> tab gathers all the vessel\'s events (licence, alert, inspection, infraction, message…) on a single timeline, filterable by module.')
    out += proc(('Lire le tableau du matin et prendre le service', 'Read the morning board and take over the shift'), ('Agent et plus', 'Agent and above'), [
        ('Ouvrez <strong>Surveillance › Tableau du matin</strong>. Trois compteurs résument la situation : navires à surveiller, observations ouvertes, relais à lire.', 'Open <strong>Surveillance › Morning board</strong>. Three counters sum up the situation: vessels to watch, open observations, relays to read.'),
        ('Pour chaque relais reçu, cliquez <strong>Accuser lecture</strong>.', 'For each relay received, click <strong>Acknowledge</strong>.'),
        ('Parcourez « Navires en veille » et les « Consignes permanentes » à appliquer.', 'Browse “Vessels on watch” and the “Standing instructions” to apply.'),
        ('Ouvrez <strong>Surveillance › Rotations et relèves</strong> et cliquez <strong>Prendre le service</strong> sur la rotation de votre équipe ; confirmez dans la fenêtre.', 'Open <strong>Surveillance › Rotations and handovers</strong> and click <strong>Take over</strong> on your team\'s rotation; confirm in the window.'),
    ], ('Le tableau de relève s\'ouvre ; le service est pris.', 'The handover board opens; the shift is taken.'))
    out += note('info', 'Quand un navire a été vérifié plus de fois que le seuil (3 par défaut, réglable par l\'Administrateur), le tableau du matin propose un <strong>suivi renforcé</strong> : cliquez <em>Confirmer le suivi renforcé</em> si vous êtes d\'accord. La décision reste humaine.', 'When a vessel has been checked more often than the threshold (3 by default, adjustable by the Administrator), the morning board suggests <strong>enhanced monitoring</strong>: click <em>Confirm enhanced monitoring</em> if you agree. The decision stays human.')
    out += proc(('Saisir une observation de quart', 'Enter a shift observation'), ('Agent et plus', 'Agent and above'), [
        ('Ouvrez <strong>Surveillance › Observations de quart</strong> puis <strong>Nouvelle observation</strong>.', 'Open <strong>Surveillance › Shift observations</strong> then <strong>New observation</strong>.'),
        ('Choisissez le navire, décrivez ce que vous avez constaté, choisissez si l\'observation doit être <strong>relayée</strong> à l\'équipe suivante (ou « Aucun relais »).', 'Choose the vessel, describe what you observed, choose whether the observation should be <strong>relayed</strong> to the next team (or “No relay”).'),
        ('Enregistrez.', 'Save.'),
    ], ('L\'application crée la ligne de main courante, augmente le compteur de vérifications du navire et transmet le relais.', 'The application creates the logbook line, increases the vessel\'s check counter and passes on the relay.'))
    out += img('observation-new', 'Le formulaire de nouvelle observation de surveillance.', 'The new surveillance observation form.')
    out += proc(('Tenir la main courante du jour', 'Keep the day\'s logbook'), ('Agent et plus (la clôture : Chef d\'équipe)', 'Agent and above (closing: Team lead)'), [
        ('Ouvrez <strong>Surveillance › Main courante</strong> : la journée en cours s\'affiche avec son quart (de 08 h à 18 h, heure UTC, par défaut).', 'Open <strong>Surveillance › Logbook</strong>: the current day shows with its shift (08:00 to 18:00 UTC by default).'),
        ('Ajoutez une ligne <strong>QUAND / COMMENT / QUOI</strong> pour chaque fait reçu : à quel moment, par quel moyen, ce qui s\'est passé. Rattachez les navires concernés.', 'Add a <strong>WHEN / HOW / WHAT</strong> line for each fact received: at what time, by what means, what happened. Attach the vessels concerned.'),
        ('Si une ligne est fausse avant la clôture, modifiez-la ; après la clôture, utilisez une <strong>ligne rectificative</strong> (la ligne d\'origine ne peut plus être changée).', 'If a line is wrong before closing, edit it; after closing, use a <strong>rectifying line</strong> (the original line can no longer be changed).'),
    ])
    out += img('logbook', 'La main courante du jour.', 'Today\'s logbook.')
    out += proc(('Traiter une alerte de balise', 'Handle a beacon alert'), ('Agent et plus', 'Agent and above'), [
        ('Ouvrez <strong>Surveillance › Alertes</strong> et cliquez <strong>Ouvrir</strong> sur l\'alerte (silence, géorepérage, vitesse…).', 'Open <strong>Surveillance › Alerts</strong> and click <strong>Open</strong> on the alert (silence, geofencing, speed…).'),
        ('Cliquez <strong>Acquitter</strong> pour indiquer que vous la prenez en charge.', 'Click <strong>Acknowledge</strong> to show you are taking it on.'),
        ('Dans « Traiter l\'alerte », cochez ce que vous avez vérifié : dernière position vérifiée, balise déclarée alimentée, arrêt technique déclaré, autre source qui confirme la position ; consignez les mesures prises.', 'In “Handle the alert”, tick what you have checked: last position verified, beacon declared powered, technical stop declared, another source confirms the position; record the measures taken.'),
        ('Si le fait est sérieux, utilisez <strong>Ouvrir une opération</strong>.', 'If the matter is serious, use <strong>Open an operation</strong>.'),
    ], ('L\'alerte est tracée ; elle se clôt au retour du signal.', 'The alert is traced; it closes when the signal comes back.'))
    out += img('alerts', 'La liste des alertes.', 'The alerts list.')
    out += proc(('Ouvrir un créneau de contrôle et confirmer les positions', 'Open a control slot and confirm positions'), ('Agent et plus', 'Agent and above'), [
        ('Ouvrez <strong>Surveillance › Créneaux de contrôle</strong> puis <strong>Ouvrir un créneau</strong> : les positions du moment sont chargées (« photo »).', 'Open <strong>Surveillance › Control slots</strong> then <strong>Open a slot</strong>: the positions of the moment are loaded (“photo”).'),
        ('Pour chaque navire, l\'application calcule trois tests (émission, vitesse et activité, trajectoire) ; vous les <strong>confirmez ou corrigez</strong>, puis enregistrez le résultat.', 'For each vessel, the application calculates three tests (emission, speed and activity, trajectory); you <strong>confirm or correct</strong> them, then save the result.'),
        ('<strong>Lancer un balayage</strong> pour repérer les navires non émetteurs et les anomalies.', '<strong>Run a sweep</strong> to spot non-emitting vessels and anomalies.'),
    ])
    out += proc(('Enregistrer une déclaration d\'entrée ou de sortie de la ZEE', 'Record an EEZ entry or exit declaration'), ('Agent et plus', 'Agent and above'), [
        ('Ouvrez <strong>Surveillance › Déclarations ZEE</strong> puis <strong>Saisir une déclaration</strong>.', 'Open <strong>Surveillance › EEZ declarations</strong> then <strong>Enter a declaration</strong>.'),
        ('Renseignez le navire, le type (entrée ou sortie), et les captures <strong>telles qu\'annoncées par le navire</strong>.', 'Fill in the vessel, the type (entry or exit), and the catches <strong>as announced by the vessel</strong>.'),
        ('Enregistrez : l\'application vérifie la déclaration, apparie les entrées et les sorties et lève une alerte d\'entrée ou de sortie si nécessaire (écran « Alertes »).', 'Save: the application checks the declaration, pairs entries and exits and raises an entry/exit alert if needed (“Alerts” screen).'),
        ('Les <strong>non-déclarations</strong> se relancent ou se qualifient dans l\'écran dédié.', '<strong>Missed declarations</strong> are chased or qualified in the dedicated screen.'),
    ])
    out += img('eez', 'La liste des déclarations d\'entrée et de sortie de la ZEE.', 'The list of EEZ entry and exit declarations.')
    out += proc(('Ouvrir une opération pour un fait complexe', 'Open an operation for a complex fact'), ('Agent et plus (le suivi et la clôture : Chef d\'équipe)', 'Agent and above (follow-up and closing: Team lead)'), [
        ('Ouvrez <strong>Surveillance › Opérations</strong> puis <strong>Nouvelle opération</strong>.', 'Open <strong>Surveillance › Operations</strong> then <strong>New operation</strong>.'),
        ('Choisissez la catégorie (VMS, ERS, MEP, INN ou DIV) et le navire : le code <kbd>OPS-numéro-catégorie-année-NOM</kbd> est généré (le compteur repart à zéro au 1er janvier).', 'Choose the category (VMS, ERS, MEP, INN or DIV) and the vessel: the code <kbd>OPS-number-category-year-NAME</kbd> is generated (the counter restarts at zero on 1 January).'),
        ('Ajoutez des événements à la chronologie au fur et à mesure.', 'Add events to the timeline as things happen.'),
    ], ('Le dossier suit le fait jusqu\'à sa clôture par le Chef d\'équipe, avec son résultat.', 'The file follows the matter until the Team lead closes it, with its outcome.'))
    out += proc(('Enregistrer un signalement de témoin', 'Record a witness report'), ('Agent et plus', 'Agent and above'), [
        ('Ouvrez <strong>Surveillance › Signalements</strong> puis <strong>Enregistrer un signalement</strong>.', 'Open <strong>Surveillance › Witness reports</strong> then <strong>Record a report</strong>.'),
        ('Saisissez le rapport reçu par e-mail, téléphone, fax, courrier ou fiche papier : le témoin n\'a jamais accès à l\'application, c\'est vous qui l\'enregistrez. Vous pouvez désigner deux navires au plus ; s\'ils correspondent au registre, ils sont rattachés à leur fiche.', 'Enter the report received by e-mail, phone, fax, mail or paper form: the witness never has access to the application, you record it. You may name up to two vessels; if they match the register, they are linked to their record.'),
    ], ('Le Chef d\'équipe évalue ensuite l\'exploitabilité, mène l\'enquête et décide (classement ou transmission).', 'The Team lead then assesses usability, investigates and decides (dismissal or transmission).'))
    out += img('witness-new', 'Le formulaire d\'enregistrement d\'un signalement de témoin.', 'The witness report recording form.')
    out += proc(('Programmer une mission', 'Schedule a mission'), ('Agent et plus (l\'autorisation : Chef d\'équipe ou Directeur)', 'Agent and above (authorization: Team lead or Director)'), [
        ('Ouvrez <strong>Contrôles › Missions</strong> puis <strong>Programmer une mission</strong>.', 'Open <strong>Controls › Missions</strong> then <strong>Schedule a mission</strong>.'),
        ('Renseignez type, nature, objectifs, zones et dates : la mission sera autorisée selon son type (le Directeur par défaut).', 'Fill in type, nature, objectives, zones and dates: the mission will be authorized according to its type (the Director by default).'),
    ])
    out += proc(('Envoyer un message ou créer une note interne', 'Send a message or create an internal note'), ('Agent et plus', 'Agent and above'), [
        ('Ouvrez <strong>Surveillance › Communications</strong> puis <strong>Envoyer un message</strong>, ou la section des notes internes.', 'Open <strong>Surveillance › Communications</strong> then <strong>Send a message</strong>, or the internal notes section.'),
        ('Choisissez le modèle, le canal (dans l\'application, e-mail ou WhatsApp) et le destinataire.', 'Choose the template, the channel (in-app, e-mail or WhatsApp) and the recipient.'),
        ('Cliquez <strong>Envoyer</strong> et confirmez dans la fenêtre qui récapitule canal et destinataire.', 'Click <strong>Send</strong> and confirm in the window that recaps channel and recipient.'),
    ], ('Le message est envoyé ou tracé comme échoué, et reste consultable sur la fiche du navire.', 'The message is sent or traced as failed, and stays viewable on the vessel\'s record.'))
    out += h2('Ce que vous ne pouvez pas faire', 'What you cannot do')
    out += ul([
        ('Clôturer la journée de main courante, une opération, une mission ou un dossier d\'infraction.', 'Close the logbook day, an operation, a mission or an infraction case.'),
        ('Créer ou modifier une fiche navire, trancher un doublon, enregistrer ou suspendre une licence.', 'Create or edit a vessel record, settle a duplicate, record or suspend a licence.'),
        ('Décider d\'une suite, d\'une sanction ou d\'une mesure ; exporter ; consulter les rapports ou la vue flotte.', 'Decide a follow-up, a sanction or a measure; export; view reports or the fleet view.'),
    ])
    out += note('info', 'Si un bouton attendu n\'apparaît pas, c\'est que votre rôle ne le permet pas. Une page interdite affiche « Accès refusé » : demandez au Chef d\'équipe ou à l\'Administrateur.', 'If an expected button does not appear, your role does not allow it. A forbidden page shows “Access denied”: ask the Team lead or the Administrator.')
    return out


def teamlead():
    out = h1('Chapitre Chef d\'équipe', 'Team lead chapter')
    out += p('<span class="lead">Le Chef d\'équipe pilote le quart : il distribue le travail, valide ce que les agents saisissent, clôture, instruit les dossiers et autorise. Il dispose de tous les droits de l\'Agent, plus ceux de gestion. Seules deux décisions lui échappent : décider d\'une mesure conservatoire et autoriser une mission réservée au Directeur.</span>',
             '<span class="lead">The Team lead runs the shift: they assign work, validate what agents enter, close, investigate files and authorize. They hold all the Agent\'s rights, plus management rights. Only two decisions are out of reach: deciding a precautionary measure and authorizing a mission reserved for the Director.</span>')
    out += h2('Une journée type', 'A typical day')
    out += table([('Moment', 'Moment'), ('Ce que vous faites', 'What you do'), ('Où', 'Where')], [
        [('Début de quart', 'Start of shift'), ('Relire le tableau du matin, planifier les rotations, créer ou lever des consignes permanentes, tenir la liste du Directeur.', 'Review the morning board, plan rotations, create or end standing instructions, maintain the Director\'s list.'), ('Tableau du matin, Rotations, Consignes', 'Morning board, Rotations, Instructions')],
        [('Pendant le quart', 'During the shift'), ('Superviser alertes et opérations, trancher les doublons, instruire les dossiers, autoriser les missions, suivre les licences.', 'Supervise alerts and operations, settle duplicates, investigate files, authorize missions, follow licences.'), ('Alertes, Opérations, Doublons, Infractions, Missions, Licences', 'Alerts, Operations, Duplicates, Infractions, Missions, Licences')],
        [('Fin de quart', 'End of shift'), ('Clôturer la main courante (PDF figé), clore les opérations terminées, passer le relais.', 'Close the logbook (frozen PDF), close completed operations, hand over.'), ('Main courante, Opérations', 'Logbook, Operations')],
        [('Périodiquement', 'Periodically'), ('Consulter la vue flotte, les rapports et exports, publier la liste des navires autorisés.', 'Consult the fleet view, reports and exports, publish the list of authorized vessels.'), ('Vue flotte, Rapports, Navires autorisés', 'Fleet view, Reports, Authorized vessels')],
    ], [18, 52, 30])
    out += h2('Procédures pas à pas', 'Step-by-step procedures')
    out += proc(('Clôturer la journée de main courante', 'Close the logbook day'), ('Chef d\'équipe, Directeur', 'Team lead, Director'), [
        ('Ouvrez <strong>Surveillance › Main courante</strong>, journée en cours.', 'Open <strong>Surveillance › Logbook</strong>, current day.'),
        ('Vérifiez les lignes puis, dans « Clôturer la journée », cliquez <strong>Clôturer et verrouiller</strong>.', 'Check the lines then, in “Close the day”, click <strong>Close and lock</strong>.'),
        ('Confirmez dans la fenêtre.', 'Confirm in the window.'),
    ], ('Un PDF non modifiable est généré (son intégrité reste contrôlable : « Intègre » ou « Altéré ») ; les lignes sont verrouillées, toute correction passe par une ligne rectificative.', 'An unalterable PDF is generated (its integrity remains checkable: “Intact” or “Altered”); the lines are locked, any correction goes through a rectifying line.'))
    out += proc(('Suivre puis clôturer une opération', 'Follow then close an operation'), ('Chef d\'équipe, Directeur', 'Team lead, Director'), [
        ('Ouvrez <strong>Surveillance › Opérations</strong> et l\'opération concernée.', 'Open <strong>Surveillance › Operations</strong> and the operation concerned.'),
        ('Mettez à jour les détails, ajoutez des événements et des instructions.', 'Update the details, add events and instructions.'),
        ('Quand le fait est traité, renseignez le résultat dans « Clôturer l\'opération », cliquez <strong>Clôturer</strong> et confirmez.', 'When the matter is handled, fill in the outcome in “Close the operation”, click <strong>Close</strong> and confirm.'),
    ], ('L\'opération est close et ses lignes de veille se ferment ; un message de réussite s\'affiche.', 'The operation is closed and its watch lines close; a success message appears.'))
    out += img('operations', 'La liste des opérations.', 'The operations list.')
    out += proc(('Trancher une file de doublons', 'Settle the duplicates queue'), ('Chef d\'équipe, Directeur', 'Team lead, Director'), [
        ('Ouvrez <strong>Référentiel › Doublons possibles</strong> (le rond orange indique le nombre à traiter).', 'Open <strong>Registry › Possible duplicates</strong> (the orange circle shows the number to handle).'),
        ('Comparez la fiche existante et la fiche entrante, et le critère qui les rapproche (IMO, MMSI, indicatif, immatriculation…).', 'Compare the existing record and the incoming record, and the criterion that matched them (IMO, MMSI, call sign, registration…).'),
        ('Choisissez <strong>Fusionner avec la fiche existante</strong>, <strong>Garder séparées</strong> ou <strong>Rejeter</strong> ; confirmez dans la fenêtre.', 'Choose <strong>Merge into the existing record</strong>, <strong>Keep separate</strong> or <strong>Reject</strong>; confirm in the window.'),
    ], ('La décision est tracée avec votre nom. La fusion est irréversible.', 'The decision is traced with your name. Merging is irreversible.'))
    out += img('duplicates', 'La file des doublons possibles.', 'The possible duplicates queue.')
    out += proc(('Suspendre ou révoquer une licence', 'Suspend or revoke a licence'), ('Chef d\'équipe, Directeur', 'Team lead, Director'), [
        ('Ouvrez <strong>Référentiel › Licences</strong> puis la licence concernée.', 'Open <strong>Registry › Licences</strong> then the licence concerned.'),
        ('Dans la section de suspension, renseignez le motif, la date de début et la <strong>fin prévue</strong> (obligatoire sauf force majeure, trois mois au plus sinon). Pour une révocation, renseignez le motif.', 'In the suspension section, fill in the reason, the start date and the <strong>planned end</strong> (mandatory except for force majeure, three months at most otherwise). For a revocation, fill in the reason.'),
        ('Cliquez le bouton d\'action et confirmez dans la fenêtre : la révocation est définitive.', 'Click the action button and confirm in the window: revocation is final.'),
        ('Pour lever une suspension, utilisez <strong>Lever</strong> sur la suspension concernée.', 'To lift a suspension, use <strong>Lift</strong> on the suspension concerned.'),
    ], ('Le statut de la licence et du navire est mis à jour ; l\'événement entre dans la chronologie du navire.', 'The licence and vessel status are updated; the event enters the vessel\'s timeline.'))
    out += note('info', 'Une suspension reste en vigueur jusqu\'à sa levée explicite : dépasser la fin prévue la signale « échue » sans la lever automatiquement.', 'A suspension stays in force until it is explicitly lifted: going past the planned end flags it “overdue” without lifting it automatically.')
    out += img('license', 'La fiche d\'une licence.', 'A licence record.')
    out += proc(('Importer un lot de licences', 'Import a batch of licences'), ('Chef d\'équipe, Directeur', 'Team lead, Director'), [
        ('Ouvrez <strong>Référentiel › Import et validation par lot</strong>.', 'Open <strong>Registry › Batch import and validation</strong>.'),
        ('Importez un fichier CSV ou démarrez une saisie par lot ; corrigez les lignes signalées.', 'Import a CSV file or start a batch entry; correct the lines flagged.'),
        ('Cliquez <strong>Valider toutes les lignes</strong> (confirmation) pour créer ou mettre à jour toutes les licences en une fois, ou <strong>Annuler l\'import</strong> pour tout abandonner.', 'Click <strong>Validate all lines</strong> (confirmation) to create or update all the licences at once, or <strong>Cancel the import</strong> to abandon everything.'),
    ])
    out += proc(('Publier la liste des navires autorisés', 'Publish the list of authorized vessels'), ('Chef d\'équipe, Directeur', 'Team lead, Director'), [
        ('Ouvrez <strong>Référentiel › Navires autorisés</strong> : la liste applique la règle d\'autorisation (un navire par ligne, licence retenue parmi celles autorisées).', 'Open <strong>Registry › Authorized vessels</strong>: the list applies the authorization rule (one vessel per line, licence retained among the authorized ones).'),
        ('Cliquez <strong>Publier une nouvelle version</strong> et confirmez ; la version publiée est conservée.', 'Click <strong>Publish a new version</strong> and confirm; the published version is kept.'),
        ('Dans <strong>Surveillance › Diffusion de la liste</strong>, consignez à qui et quand la version a été diffusée.', 'In <strong>Surveillance › List distribution</strong>, record to whom and when the version was distributed.'),
    ])
    out += img('authorized', 'La liste des navires autorisés.', 'The list of authorized vessels.')
    out += proc(('Ouvrir et instruire un dossier d\'infraction', 'Open and investigate an infraction case'), ('Chef d\'équipe, Directeur', 'Team lead, Director'), [
        ('Ouvrez <strong>Contrôles › Infractions</strong> puis <strong>Ouvrir un dossier</strong> (ou utilisez <em>Ouvrir un dossier d\'infraction</em> depuis un signalement, une mission ou un navire, qui préremplit le dossier).', 'Open <strong>Controls › Infractions</strong> then <strong>Open a case</strong> (or use <em>Open an infraction case</em> from a witness report, a mission or a vessel, which prefills the case).'),
        ('Sur la fiche du dossier, ajoutez successivement : les <strong>infractions retenues</strong> (nomenclature), les <strong>preuves</strong>, les <strong>scellés</strong>, les <strong>saisies</strong>.', 'On the case record, add in turn: the <strong>upheld infractions</strong> (nomenclature), the <strong>evidence</strong>, the <strong>seals</strong>, the <strong>seizures</strong>.'),
        ('Proposez une <strong>mesure conservatoire</strong> (la décision revient au Directeur) et établissez le <strong>procès-verbal</strong>, qui est figé en PDF.', 'Propose a <strong>precautionary measure</strong> (the decision belongs to the Director) and draw up the <strong>violation report</strong>, which is frozen as a PDF.'),
        ('Ouvrez la <strong>suite</strong> et enregistrez la décision, les paiements et les recours.', 'Open the <strong>follow-up</strong> and record the decision, payments and appeals.'),
        ('Quand tout est soldé, cliquez <strong>Clore le dossier</strong> et confirmez.', 'When everything is settled, click <strong>Close the case</strong> and confirm.'),
    ], ('Le dossier est clos ; la récidive est repérée automatiquement pour les dossiers suivants du même navire.', 'The case is closed; repeat offences are flagged automatically for the vessel\'s next cases.'))
    out += img('infraction', 'Le dossier d\'infraction : constat, infractions retenues, preuves, mesures, procès-verbal, suites.', 'The infraction case file: finding, upheld infractions, evidence, measures, violation report, follow-ups.')
    out += note('warn', 'Les décisions (suites, décisions, paiements, recours, clôture) sont prises par des personnes. L\'effet sur la licence — suspension ou révocation — se décide dans le module Licences, sur lien depuis le dossier ; il n\'est jamais automatique.', 'Decisions (follow-ups, decisions, payments, appeals, closing) are taken by people. The effect on the licence — suspension or revocation — is decided in the Licences module, linked from the case; it is never automatic.')
    out += proc(('Autoriser et suivre une mission', 'Authorize and follow a mission'), ('Chef d\'équipe, Directeur', 'Team lead, Director'), [
        ('Ouvrez <strong>Contrôles › Missions</strong> et la mission programmée.', 'Open <strong>Controls › Missions</strong> and the scheduled mission.'),
        ('Si le type de mission le permet, cliquez <strong>Autoriser</strong> et confirmez (par défaut, c\'est le Directeur qui autorise ; l\'Administrateur peut, par type, déléguer au Chef d\'équipe).', 'If the mission type allows it, click <strong>Authorize</strong> and confirm (by default the Director authorizes; the Administrator can, per type, delegate to the Team lead).'),
        ('Ajoutez l\'équipe, les moyens, le briefing ; au départ, cliquez <strong>Démarrer</strong>.', 'Add the team, the assets, the briefing; at departure, click <strong>Start</strong>.'),
        ('Pendant la mission : importez la trace GPX, enregistrez les unités rencontrées (vue, approchée, contrôlée) et les événements.', 'During the mission: import the GPX track, record the units encountered (sighted, approached, inspected) and the events.'),
        ('Au retour : <strong>Terminer</strong>, saisissez le débriefing, puis <strong>Clôturer</strong>. Le bilan est <strong>calculé</strong>, jamais saisi.', 'On return: <strong>Complete</strong>, enter the debriefing, then <strong>Close</strong>. The review is <strong>calculated</strong>, never entered.'),
    ])
    out += img('mission', 'La fiche d\'une mission.', 'A mission record.')
    out += proc(('Traiter un signalement de témoin', 'Process a witness report'), ('Chef d\'équipe, Directeur', 'Team lead, Director'), [
        ('Ouvrez le signalement dans <strong>Surveillance › Signalements</strong>.', 'Open the report in <strong>Surveillance › Witness reports</strong>.'),
        ('Évaluez l\'<strong>exploitabilité</strong>, puis consignez l\'<strong>enquête</strong>.', 'Assess <strong>usability</strong>, then record the <strong>investigation</strong>.'),
        ('Décidez : <em>classer sans suite</em> ou <em>transmettre</em> ; une transmission propose d\'ouvrir un dossier d\'infraction ou une mission préremplis, ou de lier une opération INN.', 'Decide: <em>dismiss</em> or <em>transmit</em>; a transmission offers to open a prefilled infraction case or mission, or to link an INN operation.'),
    ])
    out += proc(('Gérer les messages automatiques', 'Manage automatic messages'), ('Chef d\'équipe, Directeur', 'Team lead, Director'), [
        ('Dans <strong>Surveillance › Communications</strong>, ouvrez les modèles de messages, les règles d\'envoi, les canaux et les consentements.', 'In <strong>Surveillance › Communications</strong>, open message templates, dispatch rules, channels and consents.'),
        ('Une règle définit quand un message part tout seul (par exemple avant l\'échéance d\'une licence) ; le plafond d\'envois par jour est un réglage de l\'Administrateur.', 'A rule defines when a message goes out by itself (for example before a licence expires); the daily cap on sends is an Administrator setting.'),
    ])
    out += img('messages', 'La liste des messages.', 'The messages list.')
    out += proc(('Exporter une liste ou programmer un rapport', 'Export a list or schedule a report'), ('Chef d\'équipe, Directeur', 'Team lead, Director'), [
        ('Pour un export ponctuel : <strong>Administration › Exports</strong>, ou les boutons <em>Export CSV / PDF</em> au-dessus de la liste des navires.', 'For a one-off export: <strong>Administration › Exports</strong>, or the <em>Export CSV / PDF</em> buttons above the vessel list.'),
        ('Pour un rapport régulier : <strong>Administration › Rapports programmés</strong>, définissez le rapport, sa période et son destinataire ; il est produit automatiquement à 05 h pour la dernière période terminée.', 'For a regular report: <strong>Administration › Scheduled reports</strong>, define the report, its period and its recipient; it is produced automatically at 05:00 for the last completed period.'),
    ])
    out += img('exports', 'L\'écran des exports et leur journal.', 'The exports screen and their log.')
    out += note('info', 'Chaque export est inscrit dans un journal en ajout seul : utilisateur, droit utilisé, filtres, nombre de lignes. Personne ne peut l\'effacer.', 'Every export is written to an append-only log: user, right used, filters, number of rows. Nobody can erase it.')
    out += h2('Ce que vous ne pouvez pas faire', 'What you cannot do')
    out += ul([
        ('Décider d\'une mesure conservatoire proposée dans un dossier d\'infraction : réservé au Directeur.', 'Decide a precautionary measure proposed in an infraction case: reserved for the Director.'),
        ('Autoriser les missions que le Directeur s\'est réservées.', 'Authorize the missions the Director has reserved.'),
        ('Administrer les comptes, les rôles, les paramètres, les connecteurs : réservé à l\'Administrateur.', 'Administer accounts, roles, settings, connectors: reserved for the Administrator.'),
    ])
    return out
