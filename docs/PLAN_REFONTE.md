# Suivi des phases — alignement sur la spécification BOID MIRAH

Ce document était le plan de la refonte de l'application sur la spécification de `database/docs/` (modules M1 à M12, règles RG01–RG10, cas d'usage UC01–UC29, schéma SQL v1). **Toutes les phases sont livrées** : il sert désormais de **suivi** (état, décisions prises, ce qui reste à valider). Le détail du « pourquoi » est dans `docs/decisions/` ; les questions métier ouvertes dans `docs/QUESTIONS_GILDAS.md` ; les hypothèses retenues en attendant dans `docs/assumptions/`. Le nom du fichier est historique.

| Source | Rôle |
|---|---|
| `database/docs/BOID_MIRAH_specification_conception (1).md` | Référence métier : modules, règles RG, cas d'usage UC, phases de construction |
| `database/docs/BOID_MIRAH_MCD.md` | Modèle conceptuel par module |
| `database/docs/BOID_MIRAH_schema.sql` | Schéma physique v1 (MySQL 8, noms en français), traduit en `docs/schema/schema_v2.sql` |

---

## État d'avancement (2026-10-29)

Toutes les phases sont livrées en **version fonctionnelle** : tables migrées, modèles, actions, écrans des flux principaux, données de démonstration et tests (plus de 670 tests verts). Les questions métier restées ouvertes ont été tranchées par l'option la plus raisonnable et consignées comme **hypothèses provisoires** dans `docs/assumptions/` (74 entrées, `H-<phase>-<n>`), isolées pour qu'une réponse de Gildas soit une modification locale (voir son README). Les phases ont été menées en parallèle (ADR 0012) ; **la validation de Gildas reste à obtenir phase par phase**.

Depuis le 28 octobre : l'interface suit la référence GESICA (menu en pastilles avec compteurs, tableaux encadrés avec barre d'outils et filtres par colonne, onglets orange et gris, voir `docs/DESIGN.md`), les actions importantes demandent une confirmation, un aperçu rapide d'un navire et un message de réussite complètent les fenêtres, une visite guidée explique chaque page principale, et un manuel d'utilisation en français puis en anglais est livré dans `docs/manuel/`.

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

## Ce qui reste à faire

1. **Validation par Gildas, phase par phase**, dans le format de `docs/assumptions/` (une réponse change un seul endroit).
2. **Réponses aux questions ouvertes** de `docs/QUESTIONS_GILDAS.md` : quatre réponses partielles ont été reçues le 2026-10-29 (statut « à vérifier », infractions ouvertes, licence de référence, étiquettes), les autres bloquent encore la fin de certaines phases.
3. **Accès réels aux sources externes** (THEMIS, e-inspection, DPEP/MREP, NENO), fournisseur d'e-mail et pilote WhatsApp : aujourd'hui les connecteurs lisent des jeux de données enregistrés.
4. **Lacunes connues** par phase (dernière colonne du tableau ci-dessus).
5. **Avant la mise en production** : remplacer les comptes et mots de passe de démonstration, choisir l'hébergement, les sauvegardes et la supervision.

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

## Phases (livrées)

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

## Exécution en parallèle (appliquée, historique)

Les phases n'ont pas été menées l'une après l'autre : une fois la phase 2 faite, cinq phases n'avaient plus de dépendance de données entre elles (calculée sur les clés étrangères de `docs/schema/schema_v2.sql`). La règle est consignée dans l'ADR [0012](decisions/0012-parallel-phases.md) ; elle assouplit l'ADR 0008 et **reste à confirmer par Gildas**. Les techniques utilisées pour décorréler les phases (clés étrangères tardives, connecteur factice dès la première phase d'un module, questions bloquantes traitées par hypothèse) sont décrites dans cet ADR et dans les README de module.

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

Les fichiers partagés, sources de conflits (`Permission`, `Role::permissions()`, traductions de permissions, glossaires, matrice d'acceptation, `DatabaseSeeder`), se mettent à jour ainsi : après chaque intégration on exécute `php artisan docs:generate` et `docs:acceptance`, jamais de fusion manuelle des fichiers générés.

---

## Définition de « terminé » (toute phase)

1. Migrations avec retour arrière, factories et seeders.
2. Écritures via des `Action` ; événement et état courant vérifiés par test.
3. Textes dans `lang/{en,fr}` ; test de complétude vert.
4. Un test par règle RG et par cas d'usage de la phase, rattaché à la matrice d'acceptation.
5. README du module, glossaire et référence générée à jour (section 5).
6. Validation par Gildas, consignée dans le journal de décisions.
