# Plan de refonte — alignement sur la spécification BOID MIRAH

Ce plan suit l'alignement de l'application sur la spécification de `database/docs/` (modules M1 à M12, règles RG01–RG10, cas d'usage UC01–UC29, schéma SQL v1). Le détail du « pourquoi » est dans `docs/decisions/` ; les questions métier ouvertes dans `docs/QUESTIONS_GILDAS.md` ; les hypothèses retenues en attendant dans `docs/assumptions/`.

| Source | Rôle |
|---|---|
| `database/docs/BOID_MIRAH_specification_conception (1).md` | Référence métier : modules, règles RG, cas d'usage UC, phases de construction |
| `database/docs/BOID_MIRAH_MCD.md` | Modèle conceptuel par module |
| `database/docs/BOID_MIRAH_schema.sql` | Schéma physique v1 (MySQL 8, noms en français), traduit en `docs/schema/schema_v2.sql` |

---

## État d'avancement (fin de la nuit du 2026-10-28)

Toutes les phases sont livrées en **version fonctionnelle** : tables migrées, modèles, actions, écrans des flux principaux, données de démonstration et tests (637 tests verts). Les questions métier restées ouvertes ont été tranchées par l'option la plus raisonnable et consignées comme **hypothèses provisoires** dans `docs/assumptions/` (74 entrées, `H-<phase>-<n>`), isolées pour qu'une réponse de Gildas soit une modification locale (voir son README). Les phases ont été menées en parallèle (ADR 0012) ; la validation de Gildas reste à obtenir phase par phase.

| Phase | État | Hypothèses | Lacunes connues (voir le README du module et le fichier d'hypothèses) |
|---|---|---|---|
| 0 | validée | — | — |
| 1 | livrée | — | étiquettes (question A4), hiérarchie de zones |
| 2 | livrée | H-2-1 à H-2-8 | import par fichier ou API (phase 7), recherche dans les référentiels |
| 3a | livrée | H-3a-1 à H-3a-8 | clôture automatique de la main courante, test « suspect » |
| 3b | livrée | H-3b-1 à H-3b-8 | départ de port avec VMS en panne, écran des captures d'écran et des anomalies d'identité |
| 4a | livrée | H-4a-1 à H-4a-8 | champs extensibles sur les licences, circuit proposition puis décision |
| 4b | livrée | H-4b-1 à H-4b-7 | scraping, accusés d'e-mail, sorties non déclarées |
| 5 | livrée | H-5-1 à H-5-9 | pilote WhatsApp réel, écran d'activation de l'agent IA |
| 6 | livrée | H-6-1 à H-6-10 | rejet d'une mesure proposée, effet automatique sur la licence |
| 7 | livrée | H-7-1 à H-7-8 | accès réels (API, scraping, DPEP), alerte connecteur rouge |
| 8 | livrée | H-8-1 à H-8-4 | onglets MREP et main courante, séries par période et zone |
| 9 | livrée | H-9-1 à H-9-4 | listes et seuils par module (UC24), qualité des données, sauvegardes |

---

## Décisions

Chaque décision devient un **ADR** (`docs/decisions/NNNN-titre.md`, format : contexte, décision, conséquences). Les propositions s'appuient sur l'état actuel du projet.

| # | Décision | Options | Recommandation | Conséquence si on ne tranche pas |
|---|---|---|---|---|
| D1 | **Langue des identifiants** (tables, colonnes, classes) | A. français sans accents comme la spec ; B. anglais comme le code actuel, avec glossaire FR↔EN | **Décidé : B.** Règle déjà posée pour tout le code ; glossaire en annexe A et dans le guide | Deux conventions mélangées dans le même schéma |
| D2 | **SGBD** | A. MySQL 8 (schéma v1) ; B. rester sur PostgreSQL 16 | **Décidé : B.** Déjà en place, `jsonb`, partitionnement déclaratif, triggers ; traduction du schéma à faire | Réécriture de l'infrastructure sans bénéfice |
| D3 | **Géométrie** | A. JSON/GeoJSON comme aujourd'hui ; B. PostGIS | **Décidé : B**, déjà en place (image `postgis/postgis:16-3.5`, extension activée par migration) : zones traversées, distance aux côtes, milles par zone (test de trajectoire, `regle_de_zone`) | Calculs géographiques faits à la main en PHP |
| D4 | **Droits** | A. garder Spatie ; B. tables `role`, `groupe`, `action_catalog`, `permission` de la spec derrière les `Gate` | **B**, introduit en phase 1 en gardant les appels `can()` existants. **Livré en amont** : l'Admin crée, modifie et supprime des rôles et choisit leurs permissions (voir 3, point 4) | Droits flexibles (§3.3) impossibles : pas de groupes, de portée zone ni de période |
| D5 | **Rôles** | adopter Directeur / Chef d'équipe / Agent / Admin ; Admin sans droit métier ; inspecteur = Agent + habilitation | **Décidé : adopter les quatre rôles** avec leurs noms directs (`admin`, `director`, `team_lead`, `agent`). `supervisor` devient Chef d'équipe, et la description du rôle le précise ; `inspector` disparaît (un inspecteur est un Agent titulaire d'une habilitation). Livré, voir 3, point 4 | Contradiction avec S-004 |
| D6 | **Données existantes** | A. migrer ; B. repartir des seeders | **Décidé : B** (hypothèse H1 : aucune donnée réelle) ; la base de démo est reconstruite par `migrate:fresh --seed` | Migrations de données lourdes et inutiles |
| D7 | **Branche** | `g` (spec) ou `y-branch` (usage actuel) | **Décidé : on conserve `g-branch`** (design du collègue, déjà fusionnée dans `y-branch`, qui n'évolue plus). Flux : le développement se fait sur `y-branch` ; une fois poussée, le collègue la récupère et la teste ; quand elle est validée, elle est fusionnée dans `main`, la **branche de déploiement** | Divergence silencieuse |
| D8 | **Validateur des étapes** | qui valide chaque phase (« validation explicite de l'utilisateur ») | **Gildas** pour le métier, vous pour la technique (recommandation acceptée) | Phases enchaînées sans validation, contre la méthode imposée |
| D9 | **Seuils par défaut** | silence VMS 4 h, alertes licence 30/60/90 | **Spec**, modifiables dans Paramètres (recommandation acceptée ; à appliquer en phases 3b et 4a) | Deux valeurs en circulation |

**Hypothèses** : H1 aucune donnée de production à conserver (confirmé : aucune base n'est encore déployée, on reconstruit avec `migrate:fresh --seed`) ; H2 la validation métier de la spec par Gildas est acquise (« validée » dans son en-tête) ; H3 les points ouverts de la section 6 sont traités au fil des phases, pas avant.

---

## Phases

| Phase | Contenu | Tables | Couvre |
|---|---|---|---|
| 0 | Décisions et documentation | — | — |
| 1 | Noyau : actions, journal, droits, génériques | 21 | UC24 (partiel) |
| 2 | M1 Référentiels et navires | 27 | UC01 · RG02 |
| 3a | M5 Main courante, opérations, surveillance de quart | 21 | UC05, UC08, UC09, UC11, UC12, UC26 · RG08, RG10 |
| 3b | M3 VMS, AIS, ERS | 29 | UC04 à UC07, UC22 · RG03 à RG06 |
| 4a | M2 Licences | 15 | UC02, UC03, UC28 · RG01, RG07 |
| 4b | M4 Déclarations d'entrée et de sortie de la ZEE | 6 | UC10 |
| 5 | M12 Communications et alertes | 7 | UC20, UC21 |
| 6 | M8 Infractions, M9 Missions, M7 Signalements | 33 | UC15 à UC18 |
| 7 | Connecteurs et miroirs en lecture seule | 17 | UC13, UC14, UC19, UC23 |
| 8 | Vue 360° et indicateurs | — | UC25, UC29 |
| 9 | Pilotage complet et agent IA | 3 | UC24, UC27 |

Le détail de ce que chaque phase livre est dans le README de son module (`modules/<Nom>/README.md`) et dans `docs/assumptions/phase-<x>.md`.

---

## Exécution en parallèle (ADR 0012, appliqué)

Cette règle assouplit l'ADR 0008 (une phase à la fois) et reste à confirmer par Gildas.

**Dépendances de données réelles**, calculées sur les clés étrangères de `docs/schema/schema_v2.sql` (nombre de clés d'une phase vers une autre ; le noyau, phase 1, est requis par toutes) :

| Phase | Dépend de | Remarque |
|---|---|---|
| 2 | noyau | 10 clés ; tout le reste référence `vessels` |
| 3a | 2 (6) ; 3b (2), 4b (1) | `surveillance_observations.slot_position_id`, `zone_counts.control_slot_id`, `watch_entries.entry_exit_alert_id` |
| 3b | 2 (21) ; 3a (3) | `alerts` et `vms_failures` vers `operations` et `logbook_entries` |
| 4a | 2 (9) ; 3a (1), 3b (1), 6 (2) | seules les suspensions et révocations pointent vers `operations`, `vms_failures`, `infraction_cases` |
| 4b | 2 (4) | indépendante des autres |
| 5 | 2 (2) ; 3a (3) | `sent_messages` et `internal_notes` vers `operations` et `logbook_entries` |
| 6 | 2 (8) ; 3a (3), 3b (2), 4a (1), 7 (2) | `external_inspections` (miroir de la phase 7) : à livrer avant, ou en premier dans la 6 |
| 7 | 2 (11) | les miroirs ne dépendent que du noyau et de la phase 2 |
| 8 | toutes | lecture seule |
| 9 | noyau (2) ; 3a (1) | `operation_archives.operation_id` |

**Techniques pour décorréler**

- **Clés étrangères tardives** : la colonne existe dans la migration de sa phase, sans contrainte ; la phase qui livre la table cible ajoute la contrainte dans une migration de jonction. Les cycles 3a ↔ 3b se résolvent ainsi.
- **Connecteur factice** dès la première phase d'un module (déjà prévu), pour tester sans accès réel.
- **Questions bloquantes** (section 6) : une phase démarre sans la réponse et s'arrête avant le point qui en dépend.

**Vagues proposées** (une phase naît quand ses dépendances sont validées) :

| Vague | Phases | Condition |
|---|---|---|
| 0 | Fin de la phase 1 | validation de Gildas |
| 1 | 2 | chemin critique ; en parallèle : questions de la section 6 et `docs/QUESTIONS_GILDAS.md` |
| 2 | 3a, 3b, 4a puis 4b, 5, miroirs de la 7 | phase 2 validée ; trois flux au plus : A = 3a, B = 3b, C = 4a puis 4b puis 5 ; les miroirs 7 prennent le premier flux libre |
| 3 | 6 | 3a, 3b, 4a, 5 et `external_inspections` validés |
| 4 | 7 (connecteurs réels), puis 8, puis 9 | accords externes (V9, V12, V17, DPEP) ; 8 lit tous les modules |

**Règles de travail**

1. Un flux = un arbre de travail Git distinct (`git worktree`) sur une branche dédiée, créée depuis `y-branch` ; la décision D7 est complétée en conséquence. Intégration dans `y-branch` phase par phase, après validation, par fusion rebasée.
2. Fichiers partagés, sources de conflits : énumération `App\Support\Authorization\Permission` et `Role::permissions()`, traductions `administration.permissions.*`, `docs/GLOSSAIRE.md`, `docs/schema/GLOSSAIRE_COLONNES.md`, `docs/ACCEPTATION.md`, `docs/reference/` (généré), `DatabaseSeeder`, ce plan. Chaque flux ajoute ses lignes en fin de bloc ; après chaque intégration on exécute `php artisan docs:generate` et `docs:acceptance`, jamais de fusion manuelle des fichiers générés.
3. Migrations : un préfixe d'horodatage par flux (par exemple `2026_11_<flux>...`) pour éviter les collisions d'ordre ; une migration de jonction par paire de phases liées.
4. Base de données de développement et tests : une base par arbre de travail (voir la compétence `dev-env`).
5. Chaque phase garde sa définition de « terminé » et sa validation par Gildas ; aucune règle métier n'est inventée pour avancer.

---

## Définition de « terminé » (toute phase)

1. Migrations avec retour arrière, factories et seeders.
2. Écritures via des `Action` ; événement et état courant vérifiés par test.
3. Textes dans `lang/{en,fr}` ; test de complétude vert.
4. Un test par règle RG et par cas d'usage de la phase, rattaché à la matrice d'acceptation.
5. README du module, glossaire et référence générée à jour (section 5).
6. Validation par Gildas, consignée dans le journal de décisions.
