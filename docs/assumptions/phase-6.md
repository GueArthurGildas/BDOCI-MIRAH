# Hypothèses de la phase 6 (M8 infractions, M9 missions, M7 signalements)

Aucune de ces hypothèses n'est une règle validée. Pour retrouver les tests qui les figent : `grep -rn "Assumes('H-6-" modules tests`. Statut de toutes les entrées : **provisoire**.

## H-6-1 : Récidive : à partir de combien de dossiers (spécification : « récidive calculée » par navire, capitaine, armateur)

| Champ | Contenu |
|---|---|
| Identifiant | `H-6-1` |
| Question | Récidive : à partir de combien de dossiers (spécification : « récidive calculée » par navire, capitaine, armateur) |
| Option retenue | Un navire, un capitaine ou un armateur est en récidive dès son deuxième dossier d'infraction ; les trois sont comptés séparément, et le décompte du triplet vient de la vue `v_repeat_offenses` du schéma |
| Alternatives | Seuil de 3 dossiers ; récidive limitée aux dossiers aboutis à une sanction ; fenêtre glissante de plusieurs années |
| Où | `config/surveillance.php` (`infractions.repeat_threshold`), réglage « Récidive des infractions » de l'Admin, classe `App\Support\Infractions\RepeatOffense` |
| Pour changer | Modifier le réglage (minimum 2) ; pour restreindre aux dossiers sanctionnés ou à une fenêtre, modifier les requêtes de `RepeatOffense::for` |
| Tests | `RepeatOffenseTest` (trois méthodes) |

## H-6-2 : V21 : qui autorise les missions (paramétrer par type de mission)

| Champ | Contenu |
|---|---|
| Identifiant | `H-6-2` |
| Question | V21 : qui autorise les missions (paramétrer par type de mission) |
| Option retenue | Le Directeur autorise toutes les missions ; pour chaque type (maritime, lagunaire, continental, portuaire, aérien ou drone) un réglage de l'Admin autorise aussi le Chef d'équipe, désactivé par défaut. L'autorisation enregistre le rôle autorisant (`director` ou `team_lead`) |
| Alternatives | Le Chef d'équipe autorise par défaut ; table de règles par type et par nature (interne ou conjointe) ; double autorisation pour les missions conjointes |
| Où | `config/surveillance.php` (`missions.team_lead_authorizes.<type>`), cinq réglages de l'Admin, classe `Modules\Missions\Support\MissionAuthorizationRule`, permissions `missions.authorize` et `missions.authorize_as_director` |
| Pour changer | Cocher ou décocher les réglages ; pour une règle plus fine, modifier `MissionAuthorizationRule` et `AuthorizeMission` |
| Tests | `MissionLifecycleTest` (autorisation par défaut, par réglage, par le Directeur) |

## H-6-3 : Nomenclature des infractions et des suites (points ouverts)

| Champ | Contenu |
|---|---|
| Identifiant | `H-6-3` |
| Question | Nomenclature des infractions et des suites (points ouverts) |
| Option retenue | Les infractions sont une liste de référence éditable par l'Admin (`infraction_types`: code, libellé, base légale, gravité mineure, grave ou très grave, sanction encourue, active) amorcée par neuf infractions de démonstration ; les types de suite sont ceux de l'énumération du schéma (avertissement, sanction administrative, transaction, transmission à la hiérarchie ou au tribunal) et ne s'éditent pas sans migration |
| Alternatives | Table paramétrable aussi pour les suites ; gravité libre |
| Où | Table `infraction_types`, écran « Nomenclature des infractions », `Database\Seeders\Phase6DemoSeeder`, énumération `FollowUpType` |
| Pour changer | Éditer la liste depuis l'écran de l'Admin ; pour rendre les suites paramétrables, remplacer la contrainte `CHECK` par une table de référence |
| Tests | `InfractionTypeTest` |

## H-6-4 : Bilan de mission : règles de calcul des compteurs

| Champ | Contenu |
|---|---|
| Identifiant | `H-6-4` |
| Question | Bilan de mission : règles de calcul des compteurs |
| Option retenue | Calculé à partir des données de la mission : agents = participants ; durée = dates réelles (sinon prévues) ; milles et carburant = somme des moyens ; unités par niveau = nombre d'enregistrements ; conformes = inspections liées dont le résultat est « conforme », non conformes = les autres ; infractions, PV, saisies, sanctions = dossiers de la mission (ou issus d'une inspection rencontrée) ; déroutements et immobilisations = mesures **décidées** ; arrestations = 0 (aucune donnée dans le modèle) |
| Alternatives | Compter aussi les mesures seulement proposées ; ajouter une table des arrestations |
| Où | Classe `Modules\Missions\Support\MissionReviewCalculator` (constante `ARRESTS_COUNT`) |
| Pour changer | Modifier `MissionReviewCalculator` |
| Tests | `MissionLifecycleTest::test_the_review_is_computed_from_the_mission_data_never_typed` |

## H-6-5 : Cycle de vie d'un dossier d'infraction (statuts ouvert, instruction, décision, clos)

| Champ | Contenu |
|---|---|
| Identifiant | `H-6-5` |
| Question | Cycle de vie d'un dossier d'infraction (statuts ouvert, instruction, décision, clos) |
| Option retenue | Le dossier passe à « instruction » à la première infraction retenue, preuve, procès-verbal ou mesure proposée, à « décision » à l'ouverture d'une suite, et à « clos » par une action explicite qui clôt aussi les suites ouvertes ; il ne revient jamais en arrière |
| Alternatives | Changement de statut libre ; clôture bloquée tant qu'une suite n'a pas de décision |
| Où | Classe `Modules\Infractions\Support\CaseWorkflow`, action `CloseInfractionCase` |
| Pour changer | Modifier `CaseWorkflow` et les actions qui l'appellent |
| Tests | `CaseFileTest::test_upholding_evidence_and_seals_move_the_case_to_instruction`, `test_follow_ups_decisions_payments_appeals_and_closing_are_recorded_by_people_who_decide` |

## H-6-6 : Qui propose et qui décide dans le dossier (Directeur, Chef d'équipe, groupe Instruction)

| Champ | Contenu |
|---|---|
| Identifiant | `H-6-6` |
| Question | Qui propose et qui décide dans le dossier (Directeur, Chef d'équipe, groupe Instruction) |
| Option retenue | L'instruction (preuves, scellés, saisies, PV, proposition de mesure) relève de `infractions.manage` et `infractions.create` (Chef d'équipe, Directeur, groupe Instruction) ; les suites, décisions, paiements, recours et la clôture de `infractions.decide` (Chef d'équipe et Directeur) ; les mesures conservatoires de `infractions.decide_measures` (**Directeur seul**). Une proposition de mesure ne peut pas être refusée (le schéma ne porte pas d'état de refus) : elle reste en attente. Aucun effet automatique sur la licence : la suspension se décide dans le module Licences |
| Alternatives | Réserver les décisions au Directeur seul ; état « refusée » pour les propositions ; effet automatique sur la licence |
| Où | Permissions `infractions.*`, `Role::DIRECTOR_ONLY_PERMISSIONS`, actions du module Infractions |
| Pour changer | Retirer ou accorder les permissions depuis l'écran des rôles ; ajouter un état de refus par nouvelle migration |
| Tests | `CaseFileTest::test_precautionary_measures_are_proposed_by_the_team_lead_and_decided_by_the_director_only`, `test_decisions_are_never_taken_automatically_and_need_the_decide_permission` |

## H-6-7 : Reprise des anciennes tables `infractions`, `missions`, `mission_vessel`, `mission_user`

| Champ | Contenu |
|---|---|
| Identifiant | `H-6-7` |
| Question | Reprise des anciennes tables `infractions`, `missions`, `mission_vessel`, `mission_user` |
| Option retenue | Statuts : constatée devient ouvert, en instruction devient instruction, sanctionnée devient décision, close et abandonnée deviennent clos (qualification établie pour sanctionnée et close) ; une infraction ancienne devient un dossier avec une infraction retenue, sa preuve, et une suite et une décision si elle portait une décision ou une sanction (décideur = premier utilisateur) ; missions : planifiée devient programmée, annulée devient clôturée, patrouille en mer devient maritime, contrôle portuaire devient portuaire, opération conjointe devient maritime de nature conjointe ; les navires d'une mission deviennent des unités « approchées » (les vedettes de patrouille deviennent des moyens) ; les agents deviennent des participants |
| Alternatives | Rejeter les anciennes données (la base n'est pas déployée, `migrate:fresh --seed` suffit) |
| Où | Migrations `2026_11_09_100002_replace_missions_tables` et `2026_11_09_100003_create_infraction_case_tables` |
| Pour changer | Modifier les tables de correspondance (constantes `STATUS_MAP`, `TYPE_MAP`) avant d'exécuter les migrations |
| Tests | Aucun test automatisé : vérifié à la main sur la base de démonstration de l'ancienne version |

## H-6-8 : Signalement : transitions et ouverture de l'opération INN

| Champ | Contenu |
|---|---|
| Identifiant | `H-6-8` |
| Question | Signalement : transitions et ouverture de l'opération INN |
| Option retenue | Reçu, puis vérifié quand il est jugé exploitable, puis classé (classement sans suite) ou transmis (transmission au groupe Instruction), sans retour ; l'opération INN se crée dans le module Opérations puis se lie au signalement (seule une opération de catégorie INN est acceptée) ; un signalement transmis propose d'ouvrir un dossier d'infraction ou une mission préremplis |
| Alternatives | Création automatique de l'opération INN à l'enregistrement ; transmission possible sans exploitabilité |
| Où | Actions du module Witnesses, vues `witnesses::show` |
| Pour changer | Modifier `DecideWitnessReport`, `SaveReportUsability` et `LinkReportOperation` |
| Tests | `WitnessReportTest::test_the_team_lead_qualifies_investigates_and_forwards_a_report`, `test_only_an_inn_operation_can_be_linked_to_a_report` |

## H-6-9 : Formats de numérotation

| Champ | Contenu |
|---|---|
| Identifiant | `H-6-9` |
| Question | Formats de numérotation |
| Option retenue | Missions `MIS-AAAA-NNNN` (spécification), dossiers d'infraction `DI-AAAA-NNNN` et procès-verbaux `PV-AAAA-NNNN` (préfixes retenus faute de format défini), compteur annuel par verrou consultatif PostgreSQL |
| Alternatives | Autres préfixes ; numéro de PV saisi à la main (autorisé : le champ est facultatif) |
| Où | Classes `App\Support\Numbering\YearlySequence`, `Modules\Infractions\Support\Numbering`, `Modules\Missions\Support\MissionNumbering` |
| Pour changer | Modifier les préfixes dans ces classes |
| Tests | `CaseFileTest::test_opening_a_case_numbers_it_picks_the_parties_and_writes_the_journal_and_audit`, `MissionLifecycleTest::test_scheduling_numbers_the_mission_and_records_zones_points_team_and_assets` |

## H-6-10 : Trace GPX et zones traversées

| Champ | Contenu |
|---|---|
| Identifiant | `H-6-10` |
| Question | Trace GPX et zones traversées |
| Option retenue | La trace est la suite des points `trkpt` du fichier, stockée en ligne PostGIS ; les zones traversées sont celles dont la géométrie GeoJSON coupe la ligne (calcul à l'import, pas de recalcul) |
| Alternatives | Une trace par segment `trkseg` ; durée et vitesse déduites des horodatages |
| Où | Classes `Modules\Missions\Support\GpxTrack` et `ImportPatrolTrack` |
| Pour changer | Modifier `GpxTrack::parse` ou la requête d'intersection |
| Tests | `MissionLifecycleTest::test_a_gpx_track_is_stored_as_a_postgis_line_with_the_zones_it_crosses` |
