# Hypothèses de la phase 3b (M3 VMS, AIS, ERS)

Aucune de ces hypothèses n'est une règle validée. Pour retrouver les tests qui les figent : `grep -rn "Assumes('H-3b-" modules tests`.

## H-3b-1 : V18 : positions par API pour la photo d'un créneau ; V17 : URL directe par navire dans THEMIS

| Champ | Contenu |
|---|---|
| Identifiant | `H-3b-1` |
| Question | V18 : positions par API pour la photo d'un créneau ; V17 : URL directe par navire dans THEMIS |
| Option retenue | Les positions de la photo viennent de notre propre table `positions` par une interface `PositionFeed` dont le seul pilote est `LocalPositionFeed` (source déclarée « api »). La photo contient les navires surveillés (balise affectée ou appareil installé) avec leur dernière position à l'ouverture du créneau. Le lien THEMIS est un modèle d'adresse (`{identifier}`) ; sans modèle, le bouton copie l'identifiant du navire (IMO, sinon indicatif, sinon immatriculation). |
| Alternatives | pilote API ou import réel de THEMIS ; photo de tous les navires de la flotte ; une photo par navire et par créneau figée à l'heure de début du créneau |
| Où | `App\Support\Vms\PositionFeed`, `LocalPositionFeed`, `MonitoredVessels`, `ThemisLink` ; configuration `surveillance.vms.position_feed` et `surveillance.vms.themis_vessel_url` |
| Pour changer | écrire un pilote qui implémente `PositionFeed`, l'ajouter au `match` de `AppServiceProvider`, puis régler `SURVEILLANCE_POSITION_FEED` ; renseigner `SURVEILLANCE_THEMIS_VESSEL_URL` quand l'URL par navire est connue |
| Tests | `ControlSlotTest::test_opening_a_slot_loads_the_snapshot_of_monitored_vessels_through_the_feed`, `test_the_themis_link_is_built_from_the_template_or_falls_back_to_copying_the_identifier` |

## H-3b-2 : V20 : localités à compter hors Abidjan, San Pedro et Sassandra

| Champ | Contenu |
|---|---|
| Identifiant | `H-3b-2` |
| Question | V20 : localités à compter hors Abidjan, San Pedro et Sassandra |
| Option retenue | Liste de référence `counting_localities` (modifiable par l'Admin dans les listes de référence), alimentée avec Abidjan, San Pedro et Sassandra. Une position compte dans une localité quand sa zone est une zone portuaire dont le nom correspond au code de la valeur active. Le comptage par zone (`zone_counts`) couvre toutes les zones des positions de la photo. |
| Alternatives | autre rattachement (port le plus proche, rayon autour du port) ; ajout de localités avec géométrie propre |
| Où | `App\Support\Vms\LocalityResolver`, liste de référence `counting_localities`, configuration `surveillance.vms.counting_localities` (valeurs initiales) |
| Pour changer | ajouter ou désactiver des valeurs dans la liste de référence (créer la zone portuaire du même nom) ; pour une autre règle de rattachement, modifier `LocalityResolver::idFor` |
| Tests | `ControlSlotTest::test_vessels_are_counted_by_zone_and_by_counting_locality`, `test_the_counting_localities_are_an_editable_reference_list_seeded_with_three_ports` |

## H-3b-3 : RG04 : période d'émission requise et taux d'émission

| Champ | Contenu |
|---|---|
| Identifiant | `H-3b-3` |
| Question | RG04 : période d'émission requise et taux d'émission |
| Option retenue | Navire ivoirien : toute la période. Navire étranger : de la première à la dernière heure où une position est dans la ZEE sur la période. On retire les arrêts techniques autorisés (`vms_cutoff_authorizations`, `emission_cutoffs` marquées arrêt technique). Taux = heures avec au moins une position / heures requises, plafonné à 100 %, calculé par heure pleine. |
| Alternatives | période étrangère = toutes les heures passées en ZEE reconstituées par interpolation ; granularité de 30 minutes ; taux en nombre de positions attendues |
| Où | `App\Support\Vms\EmissionRate` (méthodes `requiredWindow` et `authorizedCutoffHours`), commande `tracking:aggregate-monthly` |
| Pour changer | modifier cette classe uniquement |
| Tests | `EmissionAggregationTest` (quatre premiers tests) |

## H-3b-4 : RG06 : règles de cohérence et trois tests assistés

| Champ | Contenu |
|---|---|
| Identifiant | `H-3b-4` |
| Question | RG06 : règles de cohérence et trois tests assistés |
| Option retenue | Émission : OK si la dernière position est plus récente que le seuil de silence, KO sinon. Vitesse et activité : seuils lus dans la règle de cohérence `speed_activity` (amarré au plus 0,5 nœud, pêche entre 0,5 et 6, transit à partir de 6, maximum 25) ; activité inconnue hors port = à confirmer. Trajectoire : KO si la trace des 6 dernières heures traverse une zone restreinte ou si la position est plus proche de la côte que la règle de zone de son segment ; la côte est une ligne GeoJSON dans la règle de cohérence `trajectory`. Résultat par défaut : suspect si un test est KO, normal sinon ; l'opérateur peut tout corriger. |
| Alternatives | tests purement manuels ; côte stockée dans une table dédiée ; zones autorisées issues des licences (phase 4a) |
| Où | règles de cohérence `speed_activity` et `trajectory` (`consistency_rules.parameters`), `App\Support\Vms\AssistedTests`, `ConfirmSlotPosition::derivedResult` |
| Pour changer | modifier les paramètres des règles de cohérence ; remplacer la côte de démonstration par la ligne de côte réelle ; pour la règle des zones autorisées, compléter `AssistedTests::trajectory` avec les zones de licence de la phase 4a |
| Tests | `ControlSlotTest::test_the_speed_and_activity_test_follows_the_consistency_rule`, `test_a_trajectory_through_a_restricted_zone_fails_the_trajectory_test`, `test_a_trawler_inside_the_zone_rule_distance_from_the_coast_fails_the_trajectory_test` |

## H-3b-5 : Rattachement d'un navire au segment d'une règle de zone (1, 3, 4, 12 milles)

| Champ | Contenu |
|---|---|
| Identifiant | `H-3b-5` |
| Question | Rattachement d'un navire au segment d'une règle de zone (1, 3, 4, 12 milles) |
| Option retenue | Type chalutier = chalutage, senneur = petits pélagiques, pirogue ou segment artisanal = artisanal, palangrier ou thonier = thonier ; les autres types n'ont pas de règle. |
| Alternatives | segment dérivé de la catégorie de flotte ; segment saisi sur la fiche navire |
| Où | `App\Support\Vms\SegmentMapper` |
| Pour changer | modifier cette classe uniquement |
| Tests | `ControlSlotTest::test_a_trawler_inside_the_zone_rule_distance_from_the_coast_fails_the_trajectory_test` |

## H-3b-6 : Avarie VMS : fréquence des rapports (4 h ou 6 h) et délai de réparation (5, 10 ou 30 jours) par catégorie

| Champ | Contenu |
|---|---|
| Identifiant | `H-3b-6` |
| Question | Avarie VMS : fréquence des rapports (4 h ou 6 h) et délai de réparation (5, 10 ou 30 jours) par catégorie |
| Option retenue | Étranger : 5 jours, rapports toutes les 4 h ; ivoirien : 10 jours, 4 h ; affrété : 10 jours, 6 h ; thonier : 30 jours, 6 h. Valeurs modifiables à la déclaration. Le contrôle « aucun départ du port avec un VMS en avarie » n'est pas implémenté. |
| Alternatives | autre association catégorie / délai |
| Où | configuration `surveillance.vms.failure_repair_deadline_days` et `surveillance.vms.failure_report_frequency_hours`, `Modules\Vms\Actions\DeclareVmsFailure` |
| Pour changer | modifier les deux tableaux de configuration |
| Tests | `ConformityAndFailureTest::test_a_failure_gets_the_deadline_and_report_frequency_of_its_category` |

## H-3b-7 : Silence VMS : seuil de la règle d'alerte (décision D9) et coexistence avec `tracking_devices`

| Champ | Contenu |
|---|---|
| Identifiant | `H-3b-7` |
| Question | Silence VMS : seuil de la règle d'alerte (décision D9) et coexistence avec `tracking_devices` |
| Option retenue | Le seuil vient de la règle d'alerte de silence active (4 h par défaut, modifiable par l'Admin sur la page des règles) ; sans règle, la configuration `surveillance.beacon_silence_threshold_hours` (4 h) s'applique. `tracking_devices` reste la projection des affectations (carte, état courant, ancien code) : une affectation crée ou clôt l'appareil correspondant, et la migration importe les appareils VMS existants dans `beacons`. L'évaluation des règles lève au plus une alerte ouverte par navire et règle, la referme au retour des positions, ignore les arrêts techniques autorisés. |
| Alternatives | suppression de `tracking_devices` et lecture directe des affectations ; plusieurs seuils par catégorie de navire |
| Où | `App\Support\Vms\SilenceThreshold`, `BeaconMirror`, `AlertRuleEvaluator`, commande `tracking:evaluate-alert-rules` |
| Pour changer | supprimer la projection quand les modules lisant `tracking_devices` auront migré vers `beacon_assignments` |
| Tests | `AlertRulesTest` (seuil, règle, projection), `BeaconAssignmentTest::test_assignments_are_projected_onto_the_legacy_tracking_devices` |

## H-3b-8 : Conformité d'une balise : statuts et résultats

| Champ | Contenu |
|---|---|
| Identifiant | `H-3b-8` |
| Question | Conformité d'une balise : statuts et résultats |
| Option retenue | Demande d'évaluation : demandée, évaluée, close ; demande de nouvelle balise : demandée, installée, close ; résultat d'évaluation : compatible, incompatible, à compléter. Le certificat de conformité (`vms_compatibility_certificates`) relève de la phase 4a. L'installation peut affecter directement une balise au navire. |
| Alternatives | autres vocabulaires ; certificat généré à l'évaluation |
| Où | énumérations `VmsRequestStatus`, `BeaconRequestStatus`, `AssessmentResult` (contraintes CHECK des tables) |
| Pour changer | modifier l'énumération et la contrainte CHECK par une migration |
| Tests | `ConformityAndFailureTest::test_case_one_an_existing_beacon_is_assessed_for_compatibility`, `test_case_two_a_new_beacon_is_installed_by_an_approved_installer_and_assigned` |
