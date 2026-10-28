# Hypothèses de la phase 4b (M4 Déclarations d'entrée et de sortie de la ZEE)

Statut de toutes les entrées : **provisoire** (livrée le 2026-11-04, à confirmer par Gildas). Format défini dans `README.md`.

## H-4b-1 Rapprochement de l'identité déclarée avec la fiche navire

| Champ | Contenu |
|---|---|
| Identifiant | `H-4b-1` |
| Question | La spécification dit que l'identité déclarée (nom, IMO, MMSI, IRCS, pavillon, propriétaire) est « rapprochée de la fiche » sans dire comment |
| Option retenue | Recherche dans l'ordre : IMO, puis IRCS (indicatif d'appel), puis nom exact (sans tenir compte de la casse). Le MMSI n'est pas rapproché (aucune colonne sur `vessels`). Sans correspondance, la déclaration est conservée sans navire, sans alerte ni événement |
| Alternatives | Ajouter le MMSI, le numéro d'immatriculation, une approche floue sur le nom, ou une file de validation manuelle |
| Où | `Modules\Movements\Services\VesselMatcher` |
| Pour changer | Modifier `VesselMatcher::find` ; le formulaire permet déjà de choisir le navire à la main |
| Tests | `RecordEezDeclarationTest::test_the_vessel_is_identified_from_the_declared_identity_by_imo_then_call_sign_then_name` |

## H-4b-2 Appariement entrée / sortie

| Champ | Contenu |
|---|---|
| Identifiant | `H-4b-2` |
| Question | Quelle entrée apparier à une sortie ? |
| Option retenue | Une sortie est appariée à l'entrée la plus récente du même navire reçue avant elle et pas encore appariée. Une entrée n'est appariée qu'une fois. Une sortie sans entrée reste sans lien |
| Alternatives | Appariement manuel, fenêtre de temps maximale, appariement sur la marée |
| Où | `Modules\Movements\Services\EntryExitPairing::pairingEntryFor` |
| Pour changer | Modifier cette méthode |
| Tests | `RecordEezDeclarationTest::test_an_exit_is_paired_with_the_latest_unpaired_entry...`, `test_an_entry_is_paired_only_once` |

## H-4b-3 Écart de captures entre entrée et sortie

| Champ | Contenu |
|---|---|
| Identifiant | `H-4b-3` |
| Question | Seuil d'écart propre au CSP (`parametre_seuil`), non chiffré par la spécification |
| Option retenue | Écart = total des sorties moins total des entrées (kg, signé). Au-delà de 10 % du total d'entrée (ou toute sortie sans capture à l'entrée), le lien est « non expliqué » et la déclaration de sortie passe au statut « écart ». Sous le seuil, aucune explication n'est posée. Le seuil est un réglage de l'Admin |
| Alternatives | Seuil en kg, seuil par espèce, tout écart non nul |
| Où | Clé `surveillance.eez_variance_tolerance_percent` (réglage `eez.variance_tolerance_percent`), `EntryExitPairing::exceedsTolerance` |
| Pour changer | Modifier la valeur du réglage, ou la méthode si la règle change de nature |
| Tests | `RecordEezDeclarationTest::test_an_exit_is_paired...`, `test_a_variance_within_the_tolerance_is_not_flagged` |

## H-4b-4 Vérifications automatiques et statut de la déclaration

| Champ | Contenu |
|---|---|
| Identifiant | `H-4b-4` |
| Question | Comment chaque vérification (VMS, AIS, licence, port désigné, DPEP 72 h, accès, listes INN, cohérence) est-elle évaluée ? Le cycle de statut est-il automatique ? |
| Option retenue | VMS : balise émettant (seuil de silence existant). Licence : une licence non suspendue valide à la date de passage. Port désigné : seulement pour le motif « transit vers un port ivoirien », destination contenant une localité désignée (Abidjan, San Pedro). Cohérence : chaque champ d'identité déclaré renseigné égale celui de la fiche. AIS, DPEP 72 h, accès et listes INN : « sans objet » tant que leur source n'existe pas (connecteurs de la phase 7, listes INN). Statuts : « reçue » puis « vérifiée » dès les contrôles faits, « accusée » (action manuelle), « écart » (posé par l'appariement), « clôturée » (action manuelle). Les contrôles ne sont pas rejoués ni modifiables à la main |
| Alternatives | Résultat saisi à la main par l'agent, contrôles rejoués, port désigné sur table dédiée |
| Où | `Modules\Movements\Services\EezChecker` ; clé `surveillance.eez_designated_localities` ; `RecordEezDeclaration` |
| Pour changer | Modifier l'évaluation concernée dans `EezChecker` ; la liste des ports désignés se change dans la configuration |
| Tests | `RecordEezDeclarationTest::test_checks_reflect_beacon_license_and_declared_identity`, `test_a_transit_to_a_port_checks_that_the_destination_is_designated` |

## H-4b-5 Détection des non-déclarations

| Champ | Contenu |
|---|---|
| Identifiant | `H-4b-5` |
| Question | Quand une entrée non déclarée est-elle constatée ? |
| Option retenue | Un navire dont le pavillon n'est pas `CI` (l'obligation ne concerne que les étrangers) dont la dernière position date de moins de 6 h et se trouve dans une zone de type ZEE, mer territoriale ou zone protégée, et dont la dernière déclaration n'est pas une entrée, donne lieu à une non-déclaration (une seule en cours par navire). Elle est résolue par une déclaration d'entrée reçue ensuite. Seule l'entrée non déclarée est détectée ; la sortie non déclarée n'est pas traitée. La détection tourne toutes les heures avec la commande de notification |
| Alternatives | Fenêtre de position différente, détection des sorties non déclarées, délai de grâce avant constat |
| Où | `Modules\Movements\Services\MissedDeclarationDetector` ; réglage `alerts.eez_declarations.position_window_hours` |
| Pour changer | Modifier le détecteur ou le réglage de fenêtre |
| Tests | `NotifyEezDeclarationsTest::test_a_foreign_vessel_in_the_eez_without_entry_declaration...`, `test_a_vessel_with_a_current_entry_declaration...` |

## H-4b-6 Alerte d'entrée / sortie et notifications

| Champ | Contenu |
|---|---|
| Identifiant | `H-4b-6` |
| Question | Cycle de vie de l'alerte d'entrée ou de sortie qui alimente la veille ; destinataires des notifications |
| Option retenue | Chaque déclaration d'un navire identifié ouvre une alerte (sens de la déclaration, date de réception). L'alerte ne se clôture que par une action manuelle ; l'enregistrement de la sortie ne clôture pas celle de l'entrée. Les notifications (non-déclarations en cours, alertes ouvertes) vont aux détenteurs de `movements.view`, au plus une fois par délai de 12 h (réglage). Une seule permission d'écriture `movements.declare` couvre saisie, accusé, clôture, explication, qualification et relance ; les Agents, Chefs d'équipe et Directeurs la détiennent |
| Alternatives | Fermeture automatique à la sortie, permissions distinctes pour le Directeur seul, notification par navire |
| Où | `RecordEezDeclaration`, `CloseEntryExitAlert`, `Modules\Movements\Console\NotifyEezDeclarations`, `Permission::MovementsDeclare` |
| Pour changer | Adapter ces classes ; les droits se règlent depuis l'administration des rôles |
| Tests | `RecordEezDeclarationTest::test_an_agent_records_an_entry...`, `EezDeclarationFollowUpTest`, `NotifyEezDeclarationsTest` |

## H-4b-7 Moyens de réception

| Champ | Contenu |
|---|---|
| Identifiant | `H-4b-7` |
| Question | Liste des moyens de réception d'une déclaration (spécification : liste de référence « canaux ») |
| Option retenue | Liste de référence `eez_reception_channels` (e-mail du capitaine, e-mail de l'agent maritime, radio VHF, téléphone, interface de déclaration), alimentée par le seeder de démonstration de la phase et modifiable par l'Admin sans code |
| Alternatives | Autres valeurs ; liste commune à tous les modules de communication |
| Où | Liste de référence `eez_reception_channels` (`Phase4bDemoSeeder`) |
| Pour changer | Modifier les valeurs dans l'écran des listes de référence |
| Tests | Aucun (donnée de référence) |
