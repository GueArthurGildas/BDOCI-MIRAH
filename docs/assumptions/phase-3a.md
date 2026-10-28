# Hypothèses de la phase 3a (M5 main courante, opérations, surveillance de quart)

Aucune de ces hypothèses n'est une règle validée. Pour retrouver les tests qui les figent : `grep -rn "Assumes('H-3a-" modules tests`.

## H-3a-1 : équipes, jours travaillés, horaires et rattachement d'une ligne à sa journée

| Champ | Contenu |
|---|---|
| Identifiant | `H-3a-1` |
| Question | E1 : nombre d'équipes, jours travaillés, heure de clôture de la main courante |
| Option retenue | quart de 08 h à 18 h (UTC), 3 équipes en rotation, tous les jours de la semaine travaillés. Une ligne appartient à la journée dont le quart a commencé avant elle : une ligne reçue à 07 h 30 est inscrite dans la journée de la veille. L'heure de clôture est l'heure de fin de quart ; la clôture est lancée à la main par le Chef d'équipe (aucune clôture automatique planifiée). |
| Alternatives | journées civiles de 00 h à 24 h ; jours ouvrés seulement ; clôture automatique à 18 h par une commande planifiée |
| Où | `config/surveillance.php` clés `watch.shift_start_hour`, `watch.shift_end_hour`, `watch.teams_count`, `watch.working_days` ; réglages de l'Admin (groupe « Surveillance de quart et main courante ») pour les trois premières ; `App\Support\Logbook\LogbookCalendar` (rattachement d'une ligne à sa journée) ; `Modules\Watch\Actions\PlanRotations` |
| Pour changer | modifier les valeurs dans les réglages (ou la configuration pour les jours travaillés) ; pour une journée civile, changer `LogbookCalendar::dayFor` seul ; pour une clôture automatique, ajouter une commande planifiée qui appelle l'action `CloseLogbookDay` avec un compte système |
| Tests | `LogbookDayTest::test_a_line_belongs_to_the_day_whose_shift_started_before_it`, `test_the_shift_hours_come_from_the_settings`, `RotationAndInstructionTest` (planification) |

## H-3a-2 : seuil de vérifications d'un même navire

| Champ | Contenu |
|---|---|
| Identifiant | `H-3a-2` |
| Question | « Au-delà de 3 vérifications » (section 8 de la spécification) : l'alerte se lève-t-elle à la 3e ou à la 4e ? Que devient le compteur ensuite ? |
| Option retenue | chaque observation de surveillance ajoute une vérification ; l'alerte se lève strictement au-delà du seuil (4e vérification pour le seuil 3). Le suivi renforcé est proposé sur le tableau du matin et confirmé par l'agent (origine `more_than_3_checks`). Quand cette ligne de veille est close, le compteur repart à zéro. |
| Alternatives | alerte à la 3e vérification ; compteur remis à zéro chaque jour ou jamais |
| Où | réglage `watch.check_threshold` (`surveillance.watch.check_threshold`) ; `RecordSurveillanceObservation::countCheck` ; `CloseWatchEntry` pour la remise à zéro |
| Pour changer | changer la valeur du réglage ; pour la remise à zéro, modifier `CloseWatchEntry` |
| Tests | `SurveillanceObservationTest::test_beyond_the_threshold_the_alert_is_raised_and_the_agent_is_told`, `test_the_threshold_is_a_setting`, `MorningBoardTest::test_closing_the_proposal_watch_entry_restarts_the_check_counter` |

## H-3a-3 : format du code d'une opération

| Champ | Contenu |
|---|---|
| Identifiant | `H-3a-3` |
| Question | `OPS-num-cat-AAAA-NOM` : largeur du numéro, casse de la catégorie, année de référence |
| Option retenue | numéro sur 3 chiffres (`OPS-007-VMS-2026-Silence_balise`), catégorie en majuscules, nom sans accents ni ponctuation avec les espaces remplacés par « _ » (casse conservée), année du début de l'opération ; compteur par catégorie et par année, donc remis à zéro le 1er janvier |
| Alternatives | numéro sans remplissage ; nom en majuscules |
| Où | `Modules\Operations\Support\OperationNumbering` (constante `NUMBER_PAD_LENGTH` et méthode `code`) |
| Pour changer | modifier cette classe uniquement |
| Tests | `OperationNumberingTest` |

## H-3a-4 : clôture de la journée et ligne rectificative

| Champ | Contenu |
|---|---|
| Identifiant | `H-3a-4` |
| Question | statuts `open`/`closed`/`locked`, contenu des compteurs de clôture, journée qui reçoit la ligne rectificative |
| Option retenue | la clôture génère le PDF figé (SHA-256), écrit les compteurs et passe directement à `locked` (le statut `closed` n'est pas utilisé) ; compteurs : lignes, reçues, émises, ordres ou décisions, rectifications, par statut de traitement, par canal, opérations ouvertes par catégorie ; la ligne rectificative est inscrite dans la journée en cours (ouverte), la ligne d'origine ne change pas ; une rectification ne se rectifie pas |
| Alternatives | clôture en deux temps (close puis verrouillée) ; rectification dans la journée d'origine après réouverture |
| Où | `Modules\Logbook\Actions\CloseLogbookDay`, `Modules\Logbook\Support\LogbookCounters`, `RectifyLogbookEntry` |
| Pour changer | modifier ces classes ; le déclencheur de base de données ne change pas |
| Tests | `LogbookDayTest::test_closing_the_day_freezes_an_unmodifiable_pdf_and_locks_the_lines`, `test_a_locked_line_cannot_change_status_but_can_be_rectified_in_the_current_day` |

## H-3a-5 : relais entre équipes

| Champ | Contenu |
|---|---|
| Identifiant | `H-3a-5` |
| Question | qui reçoit un relais, qui accuse lecture, que faire sans rotation planifiée |
| Option retenue | un relais cible une équipe ; ses membres et son chef reçoivent une notification, et seuls eux peuvent accuser lecture ; sans rotation planifiée pour l'équipe de l'auteur le jour même, la rotation et la relève sont créées à la volée ; l'auteur doit appartenir à une équipe |
| Alternatives | relais vers « l'équipe suivante » calculée à partir des rotations ; accusé de lecture réservé au Chef d'équipe |
| Où | `Modules\Watch\Actions\RecordSurveillanceObservation::relay`, `AcknowledgeRelay`, `Modules\Watch\Notifications\RelayAlert` |
| Pour changer | modifier ces classes |
| Tests | `SurveillanceObservationTest::test_a_relayed_observation_creates_a_handover_and_notifies_the_next_team`, `test_only_the_next_team_acknowledges_a_relay` |

## H-3a-6 : reprise des observations existantes

| Champ | Contenu |
|---|---|
| Identifiant | `H-3a-6` |
| Question | que deviennent les observations de l'ancienne main courante (`observations`) |
| Option retenue | les pages actuelles (`/logbook`) restent et écrivent dans `observations` ; chaque observation liée à un navire est reflétée dans `surveillance_observations` (colonne `legacy_observation_id`), par copie à la migration puis à chaque écriture ; statut `closed` reflété, les autres deviennent `monitoring` ; niveau, zone, coordonnées et suite à donner ne sont pas reflétés |
| Alternatives | basculer les pages sur la nouvelle table et supprimer l'ancienne |
| Où | `App\Support\Logbook\ObservationMirror`, migration `2026_11_03_100004`, actions `OpenObservation` et `UpdateObservation` |
| Pour changer | supprimer le miroir quand les pages utilisent `surveillance_observations` |
| Tests | `ObservationMirrorTest` |

## H-3a-7 : navires en veille et droits par défaut

| Champ | Contenu |
|---|---|
| Identifiant | `H-3a-7` |
| Question | quand un navire cité en main courante sort-il de la veille ; qui peut faire quoi |
| Option retenue | une ligne de veille `logbook` est ouverte par navire et par ligne citée, close quand la ligne passe à « clos » (rouverte si elle est remise en traitement) ou quand l'opération liée est clôturée ; l'Agent consulte, saisit lignes, observations et suivis renforcés et ouvre des opérations ; le Chef d'équipe et le Directeur clôturent les journées et les opérations, gèrent la liste du Directeur, les rotations et les consignes permanentes |
| Alternatives | veille fermée seulement par le Chef d'équipe ; Agent autorisé à clôturer une opération |
| Où | `App\Support\Watch\WatchList`, `Role::permissions()` (Agent), `Permission` (cas ajoutés en fin) |
| Pour changer | modifier `WatchList` ; les droits se changent dans la page Rôles et permissions |
| Tests | `LogbookDayTest::test_a_cited_vessel_stays_on_watch_until_the_line_is_closed`, `OperationFlowTest::test_closing_an_operation_removes_its_vessels_from_the_watch_list` |

## H-3a-8 : nomenclatures des canaux et de la météo

| Champ | Contenu |
|---|---|
| Identifiant | `H-3a-8` |
| Question | valeurs des listes « Canal », visibilité, nébulosité, état de la mer (la spécification cite les listes sans les détailler) |
| Option retenue | listes de référence amorcées avec : canaux téléphone, e-mail, THEMIS, radio, courrier, en personne, interne ; visibilité bonne/moyenne/mauvaise/brouillard ; nébulosité dégagé/partiellement nuageux/couvert ; état de la mer calme à très forte ; vent en échelle de Beaufort (0 à 12) |
| Alternatives | autres valeurs ou autres échelles |
| Où | listes `logbook_channels`, `weather_visibility`, `weather_cloud_cover`, `weather_sea_state` ; `App\Support\Logbook\LogbookChannels`, `App\Support\Operations\WeatherScales` |
| Pour changer | l'Admin modifie les valeurs des listes de référence sans code |
| Tests | `OperationFlowTest::test_an_agent_opens_an_operation_with_its_context_vessels_and_journal` |
