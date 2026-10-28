# Hypothèses provisoires

Quand une question métier reste ouverte (`docs/QUESTIONS_GILDAS.md`, section 6 du plan), la phase retient **l'option la plus raisonnable**, la consigne ici et l'isole pour qu'une réponse de Gildas se traduise par une modification locale. **Aucune hypothèse n'est une règle validée.**

## Une hypothèse, un identifiant

`H-<phase>-<n>` (exemple : `H-3a-2`). Chaque phase tient son fichier `docs/assumptions/<phase>.md` (par exemple `phase-3a.md`) ; ce fichier-ci décrit seulement le format et l'index.

Format d'une entrée :

| Champ | Contenu |
|---|---|
| Identifiant | `H-3a-2` |
| Question | référence de la spécification (E1, V18, nomenclature…) ou point de la phase |
| Option retenue | ce que fait le code aujourd'hui |
| Alternatives | les autres choix plausibles |
| Où | fichiers, clés de configuration ou réglages concernés |
| Pour changer | les étapes exactes quand Gildas répond |
| Tests | classes ou méthodes portant `#[Assumes('H-3a-2')]` |

## Comment isoler une hypothèse

1. **Un paramètre** (seuil, délai, jour, heure, liste de valeurs) : une clé de configuration, modifiable par un réglage de l'Admin quand c'est un paramètre non sensible (compétence `add-setting`). Changer la réponse = changer la valeur.
2. **Une règle** (calcul, condition, choix d'une ligne parmi plusieurs) : une seule classe ou méthode nommée d'après l'hypothèse, appelée partout ailleurs. Changer la réponse = changer ce point.
3. **Une liste fermée** (nomenclature, types, motifs) : une liste de référence (`reference_lists`) alimentée par un seeder, modifiable par l'Admin sans code.
4. **Un test qui fige l'hypothèse** porte `#[Assumes('H-…')]` (`Tests\Attributes\Assumes`). Pour retrouver ce qu'une réponse de Gildas touche : `grep -rn "Assumes('H-3a-2'" tests modules`.
5. Jamais d'hypothèse silencieuse : tout choix non tiré de la spécification a une entrée.

## Quand Gildas répond

1. Trouver l'entrée (index ci-dessous ou `grep`).
2. Appliquer « Pour changer ».
3. Modifier les tests marqués `Assumes`, retirer l'attribut, passer l'entrée à `Statut : confirmée` ou `corrigée` avec la date.
4. Mettre à jour `docs/QUESTIONS_GILDAS.md` (colonne Réponse) et, si la règle devient définitive, l'ADR ou la matrice d'acceptation.

## Index

74 hypothèses provisoires, une par ligne ; le détail de chacune est dans `docs/assumptions/phase-<x>.md`.

| Identifiant | Phase | Sujet | Option retenue (extrait) |
|---|---|---|---|
| `H-2-1` | 2 | Priorité des sources en cas de conflit | Ordre unique pour tous les champs : saisie manuelle, THEMIS, NENO, e-inspection, DPEP, MREP (rang 1 = prioritaire). Une source ne remplace u |
| `H-2-2` | 2 | Le nom et le pavillon seuls ne fusionnent jamais | Une correspondance sur IMO, MMSI, indicatif ou immatriculation (sans contradiction) rapproche automatiquement. Une correspondance sur le seu |
| `H-2-3` | 2 | Identifiant concordant mais identifiant contradictoire | C'est un doute : la fiche entrante est créée au statut « à rapprocher » et placée dans la file des doublons possibles, avec la fiche existan |
| `H-2-4` | 2 | Règles de zone | Elles sont saisies dans `zone_rules` pour la zone économique exclusive (données de démonstration). Aucun contrôle ne les utilise encore (pha |
| `H-2-5` | 2 | Qui tranche les doublons, et ce que fait la fusion | Permission `vessels.match` réservée au Chef d'équipe et au Directeur (pas à l'Agent, pas à l'Admin). Trois décisions : fusionner (la fiche e |
| `H-2-6` | 2 | Une valeur sans provenance compte comme une saisie manuelle | Une valeur sans provenance est traitée comme une saisie manuelle (rang 1) : une source automatique ne la remplace pas, elle ne fait que comp |
| `H-2-7` | 2 | Statut d'une fiche créée depuis une source | Oui : sans correspondance, la fiche est créée au statut « validée ». `a_rapprocher` est réservé aux fiches en attente dans la file ; `brouil |
| `H-2-8` | 2 | Données de démonstration des codes FAO | Un jeu réduit et indicatif est semé pour la démonstration ; les coefficients de conversion sont indicatifs. L'Admin complète les listes dans |
| `H-3a-1` | 3a | : équipes, jours travaillés, horaires et rattachement d'une ligne à sa journée | quart de 08 h à 18 h (UTC), 3 équipes en rotation, tous les jours de la semaine travaillés. Une ligne appartient à la journée dont le quart  |
| `H-3a-2` | 3a | : seuil de vérifications d'un même navire | chaque observation de surveillance ajoute une vérification ; l'alerte se lève strictement au-delà du seuil (4e vérification pour le seuil 3) |
| `H-3a-3` | 3a | : format du code d'une opération | numéro sur 3 chiffres (`OPS-007-VMS-2026-Silence_balise`), catégorie en majuscules, nom sans accents ni ponctuation avec les espaces remplac |
| `H-3a-4` | 3a | : clôture de la journée et ligne rectificative | la clôture génère le PDF figé (SHA-256), écrit les compteurs et passe directement à `locked` (le statut `closed` n'est pas utilisé) ; compte |
| `H-3a-5` | 3a | : relais entre équipes | un relais cible une équipe ; ses membres et son chef reçoivent une notification, et seuls eux peuvent accuser lecture ; sans rotation planif |
| `H-3a-6` | 3a | : reprise des observations existantes | les pages actuelles (`/logbook`) restent et écrivent dans `observations` ; chaque observation liée à un navire est reflétée dans `surveillan |
| `H-3a-7` | 3a | : navires en veille et droits par défaut | une ligne de veille `logbook` est ouverte par navire et par ligne citée, close quand la ligne passe à « clos » (rouverte si elle est remise  |
| `H-3a-8` | 3a | : nomenclatures des canaux et de la météo | listes de référence amorcées avec : canaux téléphone, e-mail, THEMIS, radio, courrier, en personne, interne ; visibilité bonne/moyenne/mauva |
| `H-3b-1` | 3b | : V18 : positions par API pour la photo d'un créneau ; V17 : URL directe par navire dans THEMIS | Les positions de la photo viennent de notre propre table `positions` par une interface `PositionFeed` dont le seul pilote est `LocalPosition |
| `H-3b-2` | 3b | : V20 : localités à compter hors Abidjan, San Pedro et Sassandra | Liste de référence `counting_localities` (modifiable par l'Admin dans les listes de référence), alimentée avec Abidjan, San Pedro et Sassand |
| `H-3b-3` | 3b | : RG04 : période d'émission requise et taux d'émission | Navire ivoirien : toute la période. Navire étranger : de la première à la dernière heure où une position est dans la ZEE sur la période. On  |
| `H-3b-4` | 3b | : RG06 : règles de cohérence et trois tests assistés | Émission : OK si la dernière position est plus récente que le seuil de silence, KO sinon. Vitesse et activité : seuils lus dans la règle de  |
| `H-3b-5` | 3b | : Rattachement d'un navire au segment d'une règle de zone (1, 3, 4, 12 milles) | Type chalutier = chalutage, senneur = petits pélagiques, pirogue ou segment artisanal = artisanal, palangrier ou thonier = thonier ; les aut |
| `H-3b-6` | 3b | : Avarie VMS : fréquence des rapports (4 h ou 6 h) et délai de réparation (5, 10 ou 30 jours) par catégorie | Étranger : 5 jours, rapports toutes les 4 h ; ivoirien : 10 jours, 4 h ; affrété : 10 jours, 6 h ; thonier : 30 jours, 6 h. Valeurs modifiab |
| `H-3b-7` | 3b | : Silence VMS : seuil de la règle d'alerte (décision D9) et coexistence avec `tracking_devices` | Le seuil vient de la règle d'alerte de silence active (4 h par défaut, modifiable par l'Admin sur la page des règles) ; sans règle, la confi |
| `H-3b-8` | 3b | : Conformité d'une balise : statuts et résultats | Demande d'évaluation : demandée, évaluée, close ; demande de nouvelle balise : demandée, installée, close ; résultat d'évaluation : compatib |
| `H-4a-1` | 4a | : Quelle licence retenir quand un navire en a plusieurs (point ouvert du plan) | La licence courante est celle dont la date d'expiration est la plus tardive, quel que soit son statut. La liste des navires autorisés appliq |
| `H-4a-2` | 4a | : Suspension : fin prévue et levée (3 mois maximum hors force majeure) | La fin prévue est obligatoire sauf pour la force majeure ; elle ne dépasse pas trois mois après le début sauf force majeure ; une suspension |
| `H-4a-3` | 4a | : Qui propose et qui décide une suspension ou une révocation | Le schéma ne porte pas d'état de proposition : l'action enregistre la décision en une étape, `decided_by` est l'utilisateur qui détient la p |
| `H-4a-4` | 4a | : Quels navires la liste autorisée (RG01) contient | Les navires ayant au moins une licence au statut actif dont la date d'expiration n'est pas passée ; ni suspendue ni révoquée ; la date d'émi |
| `H-4a-5` | 4a | : Format du fichier d'import et rapprochement du navire | Fichier CSV avec ligne d'en-tête, séparateur `;` ou `,`, colonnes `registration_number;number;segment;issued_on;expires_on;zones;gears;speci |
| `H-4a-6` | 4a | : Alertes d'échéance à 30, 60 et 90 jours (décision D9) | Une alerte résumé par palier et par exécution quotidienne, portant sur les licences dont l'échéance tombe dans la tranche du palier (0 à 30  |
| `H-4a-7` | 4a | : Récidive à la révocation | Le champ « récidive » de la révocation est saisi par l'utilisateur ; le calcul automatique par navire, capitaine et armateur relève de la ph |
| `H-4a-8` | 4a | : Contenu du registre annuel | Toutes les licences dont la validité recoupe l'année (expiration au plus tôt le 1er janvier, émission au plus tard le 31 décembre ou inconnu |
| `H-4b-1` | 4b | Rapprochement de l'identité déclarée avec la fiche navire | Recherche dans l'ordre : IMO, puis IRCS (indicatif d'appel), puis nom exact (sans tenir compte de la casse). Le MMSI n'est pas rapproché (au |
| `H-4b-2` | 4b | Appariement entrée / sortie | Une sortie est appariée à l'entrée la plus récente du même navire reçue avant elle et pas encore appariée. Une entrée n'est appariée qu'une  |
| `H-4b-3` | 4b | Écart de captures entre entrée et sortie | Écart = total des sorties moins total des entrées (kg, signé). Au-delà de 10 % du total d'entrée (ou toute sortie sans capture à l'entrée),  |
| `H-4b-4` | 4b | Vérifications automatiques et statut de la déclaration | VMS : balise émettant (seuil de silence existant). Licence : une licence non suspendue valide à la date de passage. Port désigné : seulement |
| `H-4b-5` | 4b | Détection des non-déclarations | Un navire dont le pavillon n'est pas `CI` (l'obligation ne concerne que les étrangers) dont la dernière position date de moins de 6 h et se  |
| `H-4b-6` | 4b | Alerte d'entrée / sortie et notifications | Chaque déclaration d'un navire identifié ouvre une alerte (sens de la déclaration, date de réception). L'alerte ne se clôture que par une ac |
| `H-4b-7` | 4b | Moyens de réception | Liste de référence `eez_reception_channels` (e-mail du capitaine, e-mail de l'agent maritime, radio VHF, téléphone, interface de déclaration |
| `H-5-1` | 5 | / Champ / Contenu / | l'e-mail part par le mailer Laravel ; `email_connectors` garde le fournisseur, le domaine d'expédition et la référence du mailer (`parameter |
| `H-5-2` | 5 | / Champ / Contenu / | WhatsApp passe par l'interface `WhatsAppGateway` ; seul le pilote `fake` (enregistre les envois) existe ; un message n'est envoyé que si le  |
| `H-5-3` | 5 | / Champ / Contenu / | le destinataire est une adresse ou un numéro fixe porté par la règle (`conditions.recipient`) ; une règle sans destinataire ne fait rien |
| `H-5-4` | 5 | / Champ / Contenu / | la règle est réévaluée à chaque passage : le retour des positions arrête de lui-même le déclencheur `beacon_silent` ; `stop_condition` ajout |
| `H-5-5` | 5 | / Champ / Contenu / | le plafond est celui de la règle (`daily_cap`), compté par règle et par jour calendaire, messages à valider compris ; sans valeur, `communic |
| `H-5-6` | 5 | / Champ / Contenu / | une règle `automatic` n'envoie seule que si le canal a l'envoi automatique activé ; sinon, et pour le mode `with_validation`, le message est |
| `H-5-7` | 5 | / Champ / Contenu / | permission `communications.manage_dispatch`, accordée au groupe système `automatic_dispatch` ; le Directeur et le Chef d'équipe l'ont aussi  |
| `H-5-8` | 5 | / Champ / Contenu / | l'action `communications.create_note` est exécutée sous le compte de type `ai_agent` (groupe `ai_agent_notes`, seule cette permission), à la |
| `H-5-9` | 5 | / Champ / Contenu / | un destinataire unique, agent ou équipe (tous les membres actifs sauf l'auteur) ; chaque destinataire reçoit une notification in_app ; le re |
| `H-6-1` | 6 | : Récidive : à partir de combien de dossiers (spécification : « récidive calculée » par navire, capitaine, armateur) | Un navire, un capitaine ou un armateur est en récidive dès son deuxième dossier d'infraction ; les trois sont comptés séparément, et le déco |
| `H-6-2` | 6 | : V21 : qui autorise les missions (paramétrer par type de mission) | Le Directeur autorise toutes les missions ; pour chaque type (maritime, lagunaire, continental, portuaire, aérien ou drone) un réglage de l' |
| `H-6-3` | 6 | : Nomenclature des infractions et des suites (points ouverts) | Les infractions sont une liste de référence éditable par l'Admin (`infraction_types`: code, libellé, base légale, gravité mineure, grave ou  |
| `H-6-4` | 6 | : Bilan de mission : règles de calcul des compteurs | Calculé à partir des données de la mission : agents = participants ; durée = dates réelles (sinon prévues) ; milles et carburant = somme des |
| `H-6-5` | 6 | : Cycle de vie d'un dossier d'infraction (statuts ouvert, instruction, décision, clos) | Le dossier passe à « instruction » à la première infraction retenue, preuve, procès-verbal ou mesure proposée, à « décision » à l'ouverture  |
| `H-6-6` | 6 | : Qui propose et qui décide dans le dossier (Directeur, Chef d'équipe, groupe Instruction) | L'instruction (preuves, scellés, saisies, PV, proposition de mesure) relève de `infractions.manage` et `infractions.create` (Chef d'équipe,  |
| `H-6-7` | 6 | : Reprise des anciennes tables `infractions`, `missions`, `mission_vessel`, `mission_user` | Statuts : constatée devient ouvert, en instruction devient instruction, sanctionnée devient décision, close et abandonnée deviennent clos (q |
| `H-6-8` | 6 | : Signalement : transitions et ouverture de l'opération INN | Reçu, puis vérifié quand il est jugé exploitable, puis classé (classement sans suite) ou transmis (transmission au groupe Instruction), sans |
| `H-6-9` | 6 | : Formats de numérotation | Missions `MIS-AAAA-NNNN` (spécification), dossiers d'infraction `DI-AAAA-NNNN` et procès-verbaux `PV-AAAA-NNNN` (préfixes retenus faute de f |
| `H-6-10` | 6 | : Trace GPX et zones traversées | La trace est la suite des points `trkpt` du fichier, stockée en ligne PostGIS ; les zones traversées sont celles dont la géométrie GeoJSON c |
| `H-7-1` | 7 | : modes de connexion et accès réels (V9, V12, DPEP) | aucun accès réel n'est disponible : les modes `api` et `direct_read` lisent des charges utiles enregistrées (fichiers JSON du dépôt, un par  |
| `H-7-2` | 7 | : couleur du voyant d'un connecteur | rouge : la dernière synchronisation a échoué ; orange : données périmées ou au moins un enregistrement rejeté à la dernière synchronisation  |
| `H-7-3` | 7 | : délai de péremption des données | 24 heures ; une source jamais synchronisée est périmée ; la commande horaire `connectors:refresh-freshness` pose `is_stale` et fait passer u |
| `H-7-4` | 7 | : seuil d'écart de captures propre au CSP | écart = (constaté - déclaré) / déclaré x 100, arrondi à deux décimales ; dépassement quand la valeur absolue dépasse 10 % (tolérance de l'UE |
| `H-7-5` | 7 | : rapprochement du navire d'un enregistrement lu | rapprochement par identifiants seuls (IMO, MMSI, IRCS, immatriculation, dans cet ordre), jamais par le nom (RG02). Une inspection ou une DPE |
| `H-7-6` | 7 | : respect du délai de 72 h de la DPEP | la valeur fournie par la source (`deadline_met`) est recopiée ; à défaut, calculée : date d'arrivée prévue moins date de dépôt supérieure ou |
| `H-7-7` | 7 | : liens externes (V17) | un modèle d'adresse par source (`external_links.model_url`) où `{id}` est remplacé par l'identifiant externe ; sans adresse, ou si l'Admin l |
| `H-7-8` | 7 | : résultat d'une inspection | la source fournit `compliant`, `minor_issues` ou `non_compliant` (énumération `InspectionResult`) ; toute autre valeur est conservée telle q |
| `H-8-1` | 8 | / Champ / Contenu / | le statut le plus grave parmi les faits observés. En infraction : au moins un dossier d'infraction non clos ni classé. Suspect : licence exp |
| `H-8-2` | 8 | / Champ / Contenu / | somme de neuf composantes, chacune = nombre d'occurrences × points, plafonnée par composante, total plafonné à 100 : silence en cours (20),  |
| `H-8-3` | 8 | / Champ / Contenu / | neuf indicateurs : navires actifs dont la dernière position est en zone économique exclusive, balises silencieuses, licences qui expirent (d |
| `H-8-4` | 8 | / Champ / Contenu / | le rôle Directeur arrive sur la vue flotte (`/fleet`) à la connexion et sur `/` ; les autres rôles gardent le tableau de bord. Droit `indica |
| `H-9-1` | 9 | / Champ / Contenu / | une opération clôturée (`status = closed`) dont `ended_at` a plus de 5 ans passe en `archived`, avec une ligne dans `operation_archives` ; u |
| `H-9-2` | 9 | / Champ / Contenu / | catalogue de deux rapports (activité des opérations, activité de la main courante) sur la dernière période civile terminée ; « Excel » est u |
| `H-9-3` | 9 | / Champ / Contenu / | affiché aux détenteurs de `reports.view_health` seulement quand un connecteur n'est pas opérationnel ou a des erreurs ouvertes ou des enregi |
| `H-9-4` | 9 | / Champ / Contenu / | trois exports (opérations, navires, main courante) en CSV ou PDF, chacun sous sa permission, accordée par défaut au Directeur et au Chef d'é |

