# Hypothèses de la phase 2 (M1 Référentiels et navires)

Format décrit dans `README.md`. Aucune de ces hypothèses n'est une règle validée par Gildas. Statut de toutes les entrées : provisoire.

## H-2-1 Priorité des sources en cas de conflit

| Champ | Contenu |
|---|---|
| Identifiant | H-2-1 |
| Question | Section 5 de la spécification : « priorité des sources paramétrable » sans ordre indiqué. Quelle source l'emporte quand deux sources donnent une valeur différente pour un même champ ? |
| Option retenue | Ordre unique pour tous les champs : saisie manuelle, THEMIS, NENO, e-inspection, DPEP, MREP (rang 1 = prioritaire). Une source ne remplace une valeur que si son rang est inférieur ou égal à celui de la source qui l'a fournie. Un champ vide est toujours complété. |
| Alternatives | Ordre différent par champ (par exemple NENO d'abord pour les vedettes, THEMIS d'abord pour les identifiants) ; la date de mise à jour la plus récente l'emporte. |
| Où | `config/registry.php` (`default_source_order`, valeur par défaut), table `source_priorities` (rangs modifiés, écran Référentiels > Priorité des sources), `App\Support\Vessels\SourcePriorityResolver` |
| Pour changer | Modifier les rangs dans l'écran, ou l'ordre par défaut dans `config/registry.php`. Une règle plus fine que « un rang par champ et par source » ne s'écrit que dans `SourcePriorityResolver::mayOverride`. |
| Tests | `VesselRegistryTest::test_a_lower_priority_source_does_not_overwrite_a_field_set_by_a_higher_one` |

## H-2-2 Le nom et le pavillon seuls ne fusionnent jamais

| Champ | Contenu |
|---|---|
| Identifiant | H-2-2 |
| Question | Section 5 : « Tout concorde : l'information est ajoutée à la fiche existante. Doute : file des doublons possibles. » Que veut dire « tout concorde » pour le dernier critère (nom + pavillon) ? |
| Option retenue | Une correspondance sur IMO, MMSI, indicatif ou immatriculation (sans contradiction) rapproche automatiquement. Une correspondance sur le seul nom normalisé (majuscules, sans espaces ni ponctuation) et le pavillon met toujours la fiche dans la file. |
| Alternatives | Rapprocher automatiquement sur nom + pavillon ; exiger plusieurs identifiants concordants. |
| Où | `Modules\Vessels\Support\VesselMatcher::matchByNameAndFlag` |
| Pour changer | Faire retourner `MatchingStatus::Matched` à cette méthode, ou ajouter une condition. |
| Tests | `VesselMatchingTest::test_name_and_flag_alone_never_merge_and_go_to_the_duplicates_queue` |

## H-2-3 Identifiant concordant mais identifiant contradictoire

| Champ | Contenu |
|---|---|
| Identifiant | H-2-3 |
| Question | Que faire quand un enregistrement concorde sur un identifiant (par exemple l'IMO) mais porte un autre identifiant (par exemple un MMSI) différent de celui de la fiche ? Même cas quand les identifiants désignent deux fiches différentes. |
| Option retenue | C'est un doute : la fiche entrante est créée au statut « à rapprocher » et placée dans la file des doublons possibles, avec la fiche existante comme candidate. Les identifiants déjà actifs sur une autre fiche ne sont pas recopiés sur la fiche entrante (ils restent dans les données brutes de la source), car un identifiant actif est unique (RG02). |
| Alternatives | Rapprocher quand même et signaler le conflit ; rejeter l'enregistrement. |
| Où | `VesselMatcher::contradicts`, `VesselRecordWriter::clashingIdentifiers` |
| Pour changer | Adapter `contradicts` (par exemple ne comparer que certains types). |
| Tests | `VesselMatchingTest::test_an_identifier_hit_with_a_contradicting_identifier_goes_to_the_queue` |

## H-2-4 Règles de zone

| Champ | Contenu |
|---|---|
| Identifiant | H-2-4 |
| Question | Les distances de la spécification (artisanale 1, petits pélagiques 3, chalut 4, thonier 12 milles) s'appliquent à quelle zone ? |
| Option retenue | Elles sont saisies dans `zone_rules` pour la zone économique exclusive (données de démonstration). Aucun contrôle ne les utilise encore (phases 3b et 4a). |
| Alternatives | Une règle par zone côtière de l'ouest, du centre et de l'est. |
| Où | `database/seeders/Phase2DemoSeeder.php`, écran Référentiels > Règles de zone |
| Pour changer | Modifier les lignes dans l'écran. |
| Tests | `RegistryPagesTest::test_every_registry_list_renders_for_a_reader` (affichage seulement) |

## H-2-5 Qui tranche les doublons, et ce que fait la fusion

| Champ | Contenu |
|---|---|
| Identifiant | H-2-5 |
| Question | La section 5 dit « l'Agent/le Chef d'équipe tranche », le module 1 dit « Chef d'équipe valide les doublons ». Que devient la fiche entrante ? |
| Option retenue | Permission `vessels.match` réservée au Chef d'équipe et au Directeur (pas à l'Agent, pas à l'Admin). Trois décisions : fusionner (la fiche existante reçoit les identifiants, sources, acteurs, historique et listes de la fiche entrante, qui est désactivée par suppression logique ; les valeurs déjà présentes sont conservées selon H-2-1), garder séparées (la fiche entrante devient validée), rejeter (la fiche entrante est désactivée, la source marquée rejetée). |
| Alternatives | Ouvrir la file aux Agents ; fusion qui remplace les valeurs de la fiche existante. |
| Où | `App\Support\Authorization\Permission::VesselsMatch`, `Role::permissions()`, `Modules\Vessels\Actions\SettleVesselDuplicate` |
| Pour changer | Accorder la permission à un rôle ou un groupe (écran Rôles) ; adapter `SettleVesselDuplicate::merge`. |
| Tests | `DuplicateQueueTest` (fusion, séparation, rejet, droits) |

## H-2-6 Une valeur sans provenance compte comme une saisie manuelle

| Champ | Contenu |
|---|---|
| Identifiant | H-2-6 |
| Question | Une fiche créée avant la phase 2 n'a pas de provenance par champ : qui a le dernier mot ? |
| Option retenue | Une valeur sans provenance est traitée comme une saisie manuelle (rang 1) : une source automatique ne la remplace pas, elle ne fait que compléter les champs vides. |
| Alternatives | Laisser toute source remplacer les valeurs sans provenance. |
| Où | `SourcePriorityResolver::mayOverride` |
| Pour changer | Changer la source par défaut utilisée quand aucune provenance n'existe. |
| Tests | `VesselRegistryTest::test_a_lower_priority_source_does_not_overwrite_a_field_set_by_a_higher_one` |

## H-2-7 Statut d'une fiche créée depuis une source

| Champ | Contenu |
|---|---|
| Identifiant | H-2-7 |
| Question | Section 5 : statuts `brouillon`, `a_rapprocher`, `validee`. Une fiche qui ne correspond à rien est-elle validée d'emblée ? |
| Option retenue | Oui : sans correspondance, la fiche est créée au statut « validée ». `a_rapprocher` est réservé aux fiches en attente dans la file ; `brouillon` n'est pas utilisé aujourd'hui (la saisie manuelle crée une fiche validée). |
| Alternatives | Créer en brouillon jusqu'à validation par le Chef d'équipe (« valide la saisie des agents »). |
| Où | `IngestVesselSource::createNew`, `VesselRecordWriter::create` |
| Pour changer | Passer `RecordStatus::Draft` à `create` dans `createNew` et ajouter l'écran de validation. |
| Tests | `VesselMatchingTest::test_a_record_matching_nothing_creates_a_new_validated_vessel_with_its_source` |

## H-2-8 Données de démonstration des codes FAO

| Champ | Contenu |
|---|---|
| Identifiant | H-2-8 |
| Question | Les codes d'engins, types de navire et présentations ainsi que les coefficients de conversion viennent de listes FAO complètes que la spécification ne reproduit pas. |
| Option retenue | Un jeu réduit et indicatif est semé pour la démonstration ; les coefficients de conversion sont indicatifs. L'Admin complète les listes dans Référentiels. |
| Alternatives | Importer les nomenclatures FAO complètes. |
| Où | `database/seeders/Phase2DemoSeeder.php` |
| Pour changer | Éditer les lignes dans l'écran Référentiels ou importer les nomenclatures officielles. |
| Tests | aucun (données de démonstration) |
