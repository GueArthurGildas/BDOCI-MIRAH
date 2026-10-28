# Hypothèses de la phase 7 (connecteurs, miroirs MREP/DPEP et e-inspection)

Aucune de ces hypothèses n'est une règle validée. Pour retrouver les tests qui les figent : `grep -rn "Assumes('H-7-" modules tests`.

## H-7-1 : modes de connexion et accès réels (V9, V12, DPEP)

| Champ | Contenu |
|---|---|
| Identifiant | `H-7-1` |
| Question | V9 (champs de l'API NENO), V12 (accord écrit pour le scraping de THEMIS), V17, la DPEP expose-t-elle tous ses champs (import contrôlé F-018 sinon) |
| Option retenue | aucun accès réel n'est disponible : les modes `api` et `direct_read` lisent des charges utiles enregistrées (fichiers JSON du dépôt, un par source) ; le mode `import` lit un fichier JSON déposé par l'Admin ; le mode `scraping` n'est pas implémenté et produit une erreur de connecteur visible (voyant rouge). Le mode se choisit par source depuis l'Admin. |
| Alternatives | brancher un client HTTP réel par source dès que l'accès est accordé ; lecture SQL directe de la base MREP |
| Où | `config/connectors.php` (`fixtures_path`, `sources`), `database/fixtures/connectors/*.json`, `App\Support\Connectors\RecordedConnector::fetch` |
| Pour changer | remplacer `fetch` dans le connecteur concerné (`App\Support\Connectors\Sources\*`) par le client réel ; la normalisation, l'écriture et le suivi des rejets ne changent pas |
| Tests | `ConnectorSyncTest::test_the_recorded_payloads_feed_the_inspection_mirror`, `test_an_unavailable_mode_turns_the_light_red_and_records_the_error` |

## H-7-2 : couleur du voyant d'un connecteur

| Champ | Contenu |
|---|---|
| Identifiant | `H-7-2` |
| Question | « voyant (mode, dernière synchro, erreurs) » : quand est-il vert, orange, rouge ? |
| Option retenue | rouge : la dernière synchronisation a échoué ; orange : données périmées ou au moins un enregistrement rejeté à la dernière synchronisation ; vert sinon |
| Alternatives | orange selon le nombre de rejets en attente (toutes synchronisations) ; rouge après N échecs consécutifs |
| Où | `App\Support\Connectors\IndicatorRule::evaluate` |
| Pour changer | modifier cette seule méthode |
| Tests | `ConnectorSyncTest::test_the_light_is_orange_with_rejections_and_green_without`, `test_an_unavailable_mode_turns_the_light_red_and_records_the_error` |

## H-7-3 : délai de péremption des données

| Champ | Contenu |
|---|---|
| Identifiant | `H-7-3` |
| Question | « voyant données périmées » : après combien de temps sans synchronisation ? |
| Option retenue | 24 heures ; une source jamais synchronisée est périmée ; la commande horaire `connectors:refresh-freshness` pose `is_stale` et fait passer un voyant vert à orange |
| Alternatives | délai propre à chaque source (THEMIS toutes les heures, DPEP quotidien) |
| Où | réglage `connectors.stale_after_hours` (`surveillance.connectors.stale_after_hours`), groupe « Connecteurs » de l'Admin |
| Pour changer | modifier le réglage |
| Tests | `ConnectorSyncTest::test_old_data_is_flagged_stale_and_the_light_leaves_green` |

## H-7-4 : seuil d'écart de captures propre au CSP

| Champ | Contenu |
|---|---|
| Identifiant | `H-7-4` |
| Question | « écart % comparé au seuil du CSP » : valeur du seuil et formule |
| Option retenue | écart = (constaté - déclaré) / déclaré x 100, arrondi à deux décimales ; dépassement quand la valeur absolue dépasse 10 % (tolérance de l'UE, la valeur du CSP n'étant pas donnée) ; pas de calcul si le déclaré est nul ou absent |
| Alternatives | 20 % (tolérance ORGP) ; écart calculé sur le poids net ; seuil par espèce |
| Où | réglage `connectors.catch_variance_threshold_percent` ; `App\Support\Connectors\CatchVariance` |
| Pour changer | modifier le réglage, ou la formule dans `CatchVariance::percent` |
| Tests | `ConnectorSyncTest::test_catch_variance_is_compared_with_the_csp_threshold`, `test_the_threshold_is_a_setting` |

## H-7-5 : rapprochement du navire d'un enregistrement lu

| Champ | Contenu |
|---|---|
| Identifiant | `H-7-5` |
| Question | que faire d'un enregistrement dont le navire est inconnu ? |
| Option retenue | rapprochement par identifiants seuls (IMO, MMSI, IRCS, immatriculation, dans cet ordre), jamais par le nom (RG02). Une inspection ou une DPEP sans navire connu est conservée sans navire (colonne facultative du schéma) et signalée « non rapprochée » ; un débarquement, un transbordement ou un refus GIES (navire obligatoire) est rejeté pour examen. Le rapprochement est refait à chaque synchronisation. |
| Alternatives | créer une fiche « connu, non autorisé » ; tout rejeter |
| Où | `App\Support\Connectors\VesselLocator` ; `normalise` des connecteurs |
| Pour changer | modifier `VesselLocator::find` ou les connecteurs concernés |
| Tests | `ConnectorSyncTest::test_an_inspection_of_an_unknown_vessel_is_kept_unmatched_and_a_landing_is_rejected`, `test_the_vessel_feeds_land_in_the_source_links_with_matching` |

## H-7-6 : respect du délai de 72 h de la DPEP

| Champ | Contenu |
|---|---|
| Identifiant | `H-7-6` |
| Question | comment savoir si la DPEP a été déposée dans le délai ? |
| Option retenue | la valeur fournie par la source (`deadline_met`) est recopiée ; à défaut, calculée : date d'arrivée prévue moins date de dépôt supérieure ou égale à 72 heures |
| Alternatives | toujours recalculer ; délai différent selon l'objet (débarquement, transbordement) |
| Où | `App\Support\Connectors\Sources\MrepConnector::deadlineMet` |
| Pour changer | modifier cette méthode |
| Tests | `ConnectorSyncTest::test_the_mrep_feed_fills_the_dpep_mirror` |

## H-7-7 : liens externes (V17)

| Champ | Contenu |
|---|---|
| Identifiant | `H-7-7` |
| Question | V17 : THEMIS accepte-t-il une adresse par navire ? |
| Option retenue | un modèle d'adresse par source (`external_links.model_url`) où `{id}` est remplacé par l'identifiant externe ; sans adresse, ou si l'Admin l'a décidé, un bouton copie l'identifiant (`is_copy_fallback`) |
| Alternatives | adresse construite avec un autre paramètre ; lien unique par navire saisi à la main |
| Où | table `external_links` (page « Liens externes » de l'Admin) ; `App\Models\ExternalLink::urlFor` |
| Pour changer | éditer le modèle d'adresse dans l'Admin |
| Tests | `ExternalLinkTest` (Vessels), `ConnectorSyncTest::test_external_links_are_edited_by_connector_managers` |

## H-7-8 : résultat d'une inspection

| Champ | Contenu |
|---|---|
| Identifiant | `H-7-8` |
| Question | valeurs du résultat d'une inspection e-inspection (colonne libre `result` du schéma) |
| Option retenue | la source fournit `compliant`, `minor_issues` ou `non_compliant` (énumération `InspectionResult`) ; toute autre valeur est conservée telle quelle et affichée brute |
| Alternatives | liste de référence administrable une fois les valeurs réelles connues |
| Où | `App\Models\ExternalInspection::resultCase` |
| Pour changer | adapter l'énumération ou passer à une liste de référence |
| Tests | `InspectionPagesTest::test_inspections_can_be_filtered_by_result` |
