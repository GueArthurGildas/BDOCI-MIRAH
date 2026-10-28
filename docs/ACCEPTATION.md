# Matrice d'acceptation

Cette matrice relie chaque **cas d'usage** (UC01 à UC29) et chaque **règle de gestion** (RG01 à RG10) de la spécification (`database/docs/BOID_MIRAH_specification_conception (1).md`, sections 8 et 14) aux tests qui les couvrent. La spécification pose la règle : un test par règle et par cas d'usage, chacun étant un critère d'acceptation.

**Relevé fait à la date de rédaction** (début de la phase 0) : aucun test n'est encore rattaché explicitement à un identifiant. La colonne « Couverture actuelle » cite le test existant dont le comportement recouvre une partie du cas ; elle est donc une lecture humaine, pas une garantie. Les noms de méthodes sont repris tels quels, même quand ils parlent d'anciens rôles (`supervisors`, `inspectors`).

| Statut | Signification |
|---|---|
| à faire | aucun comportement actuel ne recouvre le cas |
| partiel | une partie du cas est couverte (lecture seule, version simplifiée, un seul seuil…) |
| fait | le cas est couvert comme la spécification le décrit |

Le statut de la colonne de droite est écrit à la main et peut dater ; le statut calculé à partir des tests est dans `docs/reference/acceptance.md`.

Les phases sont celles de `docs/PLAN_REFONTE.md`, section 4. Aucun cas n'est « fait » à ce jour : le modèle actuel est une version simplifiée de la cible.

---

## 1. Cas d'usage

| Id | Intitulé | Acteur | Modules | Phase | Couverture actuelle | Statut |
|---|---|---|---|---|---|---|
| UC01 | Créer ou rapprocher un navire | Agent, Chef d'équipe | M1, M10 | 2 | `VesselPagesTest::test_supervisors_can_create_and_update_vessels`, `test_registration_number_must_be_unique` (création et unicité, sans rapprochement ni file de doublons) | partiel |
| UC02 | Enregistrer une licence délivrée | Groupe FNP/licences | M2 | 1 (tranche verticale), 4a | `RecordLicenseTest` (saisie unitaire avec événement, état courant et audit) ; l'import et la validation par lot restent à faire | partiel |
| UC03 | Suivre les expirations, suspendre ou révoquer | Groupe FNP/licences, Directeur | M2, M8 | 4a | `tests/Feature/Domain/LicenseStatusTest::test_status_is_derived_from_validity_dates_and_suspension` (statut calculé), `ExpiringLicensesNotificationTest` (alerte d'échéance, un seul seuil) | partiel |
| UC04 | Vérifier la conformité d'une balise | CSP, armateur, installateur | M3 | 3b | aucune | à faire |
| UC05 | Prendre son service et surveiller à chaque créneau | Équipe de quart | M5, M3 | 3a, 3b | `TrackingPagesTest::test_zone_situation_counts_vessels_present_at_a_given_time`, `test_map_shows_the_last_position_of_each_vessel` (situation et comptage par zone, sans créneau ni tests assistés) | partiel |
| UC06 | Traiter une alerte ou un silence VMS | Opérateur | M3, M5, M12 | 3b | `TrackingPagesTest::test_silent_beacons_are_detected`, `SilentBeaconsNotificationTest::test_tracking_viewers_are_notified_once_per_cooldown_when_beacons_are_silent` (détection et alerte, seuil 2 h au lieu de 4 h, sans traitement ni opération) | partiel |
| UC07 | Gérer une avarie VMS | Opérateur, superviseur | M3, M5, M8 | 3b | aucune | à faire |
| UC08 | Créer une observation et la relayer | Agent | M5 | 3a | `LogbookPagesTest::test_agents_record_observations_with_their_team` (observation avec équipe, sans relais ni compteur de vérifications) | partiel |
| UC09 | Passer un navire en suivi renforcé | Agent, Chef d'équipe | M4, M5 | 3a | aucune | à faire |
| UC10 | Enregistrer une déclaration ZEE | Opérateur | M4, M12 | 4b | aucune (`MovementPagesTest` couvre des entrées et sorties de port, notion différente) | à faire |
| UC11 | Ouvrir, suivre, clôturer une opération | Opérateur, Chef d'équipe | M5 | 3a | aucune (l'objet opération `OPS-…` n'existe pas) | à faire |
| UC12 | Tenir la main courante et passer le relais | Équipes de quart | M5 | 3a | `LogbookPagesTest::test_pending_filter_only_lists_items_to_hand_over`, `test_closing_an_observation_records_its_closing_time_and_history` (suivi et clôture des observations, sans journée figée en PDF) | partiel |
| UC13 | Consulter une DPEP et la décision d'entrée | Chef d'équipe, Directeur | M6, M12 | 7 | aucune | à faire |
| UC14 | Suivre les notifications aux États et organismes | Opérateur | M6 | 7 | aucune | à faire |
| UC15 | Recevoir un signalement de témoin | Opérateur, Chef d'équipe | M7 | 6 | aucune | à faire |
| UC16 | Constituer un dossier d'infraction | Inspecteur, groupe Instruction | M8 | 6 | `InfractionPagesTest::test_infractions_can_be_filtered_by_status` (consultation d'une infraction simple) | à faire |
| UC17 | Décider des suites | Directeur, Chef d'équipe | M8, M2 | 6 | `InfractionPagesTest::test_supervisors_record_decisions`, `test_agents_cannot_update_infractions` (décision et statut, sans PV, mesures ni effet sur la licence) | partiel |
| UC18 | Planifier et conduire une mission | Chef d'équipe, chef de mission | M9 | 6 | `MissionPagesTest::test_mission_detail_shows_vessels_and_agents` (consultation seule) | partiel |
| UC19 | Consulter inspection et écarts de captures | Chef d'équipe, Directeur | M11 | 7 | `InspectionPagesTest::test_inspections_can_be_filtered_by_result`, `test_inspection_detail_lists_related_infractions`, `CatchPagesTest::test_declarations_are_totalled_for_the_selected_species` (sans écart comparé au seuil du CSP) | partiel |
| UC20 | Envoyer un message préconçu ou programmer un envoi | Agent, groupe Envois auto. | M12 | 5 | aucune | à faire |
| UC21 | Laisser une note à un collègue | Agent | M12 | 5 | aucune | à faire |
| UC22 | Ouvrir THEMIS sur la fiche du navire | Agent | M3, M10 | 3b | aucune | à faire |
| UC23 | Piloter les connecteurs | Admin | M10 | 7 | `ConnectorFollowUpTest` (suivi des erreurs, rejets, synchronisations et commentaires avec notification des administrateurs), `MonitoringPagesTest::test_connectors_page_shows_each_data_source` (connecteurs réels et relance absents) | partiel |
| UC24 | Gérer utilisateurs, droits, paramètres | Admin | M10 | 9 | `UserAdministrationTest`, `RoleAdministrationTest`, `SettingsTest`, `MonitoringPagesTest::test_sign_ins_appear_in_the_audit_log`, `GroupAdministrationTest`, `PermissionResolverTest` (groupes et héritage faits ; listes paramétrables et seuils par module restent à faire) | partiel |
| UC25 | Obtenir la vue 360° d'un navire ou de la flotte | Directeur | tous | 8 | `VesselPagesTest::test_vessel_profile_gathers_its_history`, `DashboardTest::test_dashboard_shows_key_indicators` (fiche et indicateurs, sans événements persistés ni vue flotte) | partiel |
| UC26 | Voir les navires à surveiller à l'ouverture | Tous | M5, M4, M10 | 3a | aucune (la cloche de `NotificationsTest` n'est pas le tableau du matin) | à faire |
| UC27 | Confier une tâche à l'agent IA | Opérateur autorisé | M10, M12 | 9 | aucune | à faire |
| UC28 | Valider des licences en lot | Groupe FNP/licences | M2 | 4a | aucune | à faire |
| UC29 | Ouvrir le détail d'un indicateur | Directeur | tous | 8 | `LicensePagesTest::test_licenses_can_be_filtered_by_status` (les cartes de la page des licences ouvrent la liste filtrée ; pas les indicateurs du tableau de bord) | partiel |

---

## 2. Règles de gestion

| Id | Règle | Garantie visée par le modèle | Phase | Couverture actuelle | Statut |
|---|---|---|---|---|---|
| RG01 | Le référentiel des navires autorisés est maintenu à jour | liste calculée à partir des licences actives, avec indicateur de fraîcheur | 4a | `tests/Feature/Domain/LicenseStatusTest::test_licenses_can_be_filtered_by_status` (statuts calculés, sans liste des navires autorisés) | partiel |
| RG02 | Un navire suivi est identifiable de manière unique | identifiants uniques contrôlés, file de validation des doublons | 2 | `VesselPagesTest::test_registration_number_must_be_unique` (un seul identifiant, sans rapprochement) | partiel |
| RG03 | Un navire soumis au VMS dispose d'un dispositif associé | balise affectée obligatoire, sinon anomalie | 3b | aucune | à faire |
| RG04 | La balise émet pendant la période requise | période requise calculée et taux d'émission | 3b | `TrackingPagesTest::test_silent_beacons_are_detected` (silence au-delà d'un seuil, sans période requise ni taux) | partiel |
| RG05 | Le remplacement d'une balise est historisé | historique des affectations avec périodes | 3b | aucune (les dates d'installation et de retrait existent sur `tracking_devices`, sans test) | à faire |
| RG06 | La surveillance prend en compte position, vitesse et trajectoire | `position_creneau`, règles de cohérence paramétrables | 3b | `TrackingPagesTest::test_zone_situation_counts_vessels_present_at_a_given_time` (position et zone, sans règle de cohérence) | partiel |
| RG07 | Les licences sont suivies sur validité et renouvellement | échéances calculées, alertes paramétrables | 4a | `LicenseStatusTest::test_status_is_derived_from_validity_dates_and_suspension`, `ExpiringLicensesNotificationTest`, `SettingsTest::test_administrators_can_change_settings_and_they_apply_immediately` (un seul seuil de 30 jours, à porter à 30/60/90) | partiel |
| RG08 | Les observations sont conservées et rattachées au navire | observation liée au navire | 3a | `LogbookPagesTest::test_agents_record_observations_with_their_team`, `VesselPagesTest::test_vessel_profile_gathers_its_history` (sans lien avec un créneau) | partiel |
| RG09 | Inspections et infractions historisées | versions et historique | 1, 6 | `InfractionPagesTest::test_supervisors_record_decisions` et le composant d'historique `x-ui.audit-history` (historique automatique via `Audited`, sans versions) | partiel |
| RG10 | Les informations importantes restent accessibles aux équipes successives | relais, consignes permanentes, main courante | 3a | `LogbookPagesTest::test_pending_filter_only_lists_items_to_hand_over` (filtre des éléments à transmettre, sans relais ni consigne) | partiel |

---

## 3. Convention `#[Covers('UC05')]`

Le lien entre un test et un cas d'usage est porté par le test lui-même, pour qu'il ne puisse pas se périmer en silence.

**Attribut.** `Tests\Attributes\Covers` s'applique à une classe ou à une méthode de test, de façon répétable, et accepte un ou plusieurs identifiants. `partial: true` déclare que le test ne couvre qu'une partie du cas ou de la règle :

```php
#[Covers('UC05', 'RG04', partial: true)]
public function test_zone_situation_counts_vessels_present_at_a_given_time(): void
```

Il ne remplace pas `#[CoversClass]` de PHPUnit, qui sert à la couverture de code.

**Commande `php artisan docs:acceptance`.** Elle lit les attributs par réflexion et écrit `docs/reference/acceptance.md` : pour chaque identifiant de ce document, le statut (« à faire » sans test, « partiel » si tous les tests déclarent `partial: true`, « fait » sinon) et les tests qui le couvrent. Elle signale les cas sans test et échoue si un test cite un identifiant absent de ce document. Avec `--check`, elle échoue aussi si le rapport n'est plus à jour ; `GeneratedDocumentationTest` l'exécute à chaque lancement des tests.

**Ce document.** Il reste la source des identifiants, des intitulés, des phases et de l'analyse (colonnes « Garantie visée » et « Couverture actuelle »). Le statut qui fait foi est celui de `docs/reference/acceptance.md`. Quand vous écrivez un test qui recouvre un cas d'usage ou une règle, ajoutez `#[Covers]` puis relancez `php artisan docs:acceptance`.
