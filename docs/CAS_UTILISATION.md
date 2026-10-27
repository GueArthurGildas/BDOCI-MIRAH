# Cas d'utilisation (UML) — Système d'Information du CSP

> **Rôles : mise à jour en attente.** Ce document décrit les anciens rôles (`admin`, `supervisor`, `surveillance_agent`, `inspector`). Depuis le 2026-10-27 les rôles sont `admin`, `director`, `team_lead` (ancien `supervisor`) et `agent` (qui inclut l'ancien `inspector`), et l'`admin` n'a plus de droit métier : voir `docs/TECHNICAL.md` (Droits d'accès). La réécriture de ce document est prévue dans `docs/PLAN_REFONTE.md` (matrice d'acceptation).

Diagrammes de cas d'utilisation par module et diagramme global de la plateforme PRO-SURVEILLANCE PÊCHES.

**Méthode et notation.** Les acteurs et cas d'utilisation sont dérivés de `docs/CAHIER_DES_CHARGES.pdf` (F-001 à F-014) et `docs/CADRAGE.pdf` (§II « Périmètre métier », §IV « Matrice des activités », §V « Acteurs et responsabilités »), puis confrontés au RBAC et aux routes réellement codés (`app/Support/Authorization/{Role,Permission}.php`, `modules/*/routes/web.php`) pour obtenir des cas d'utilisation exacts (pas d'approximation sur qui peut faire quoi). Les cas d'utilisation décrits par les documents mais non routés dans le code sont marqués **[cible]** et dessinés en traits pointillés, suivant la même règle méthodologique que `docs/MODELE_DONNEES.md` ([[feedback-spec-driven-modeling]]) : le périmètre vient des documents, le code sert à vérifier la couverture.

Mermaid n'a pas de diagramme de cas d'utilisation UML natif (pas d'acteur « bonhomme », pas d'ovale) : on approxime avec `flowchart` — rectangle pour l'acteur, forme « stade » (`([...])`) pour le cas d'utilisation, sous-graphe pour la frontière du système/module. C'est la même convention que l'ERD de `docs/MODELE_DONNEES.md` : rendu natif sur GitHub, sans outillage supplémentaire (PlantUML).

- [1. Acteurs](#1-acteurs)
- [2. Cas d'utilisation par module](#2-cas-dutilisation-par-module)
- [3. Diagramme global](#3-diagramme-global)
- [4. Écarts identifiés](#4-écarts-identifiés)

## 1. Acteurs

| Acteur | Rôle applicatif (`Role`) | Source documentaire | Responsabilités |
|---|---|---|---|
| Administrateur technique | `admin` | F-013, S-002, S-003 (non nommé par le cadrage — acteur technique déduit des exigences d'administration) | Toutes les permissions ; seul à gérer les comptes utilisateurs |
| Responsable du CSP | `supervisor` | Cadrage §V « Responsable du CSP » | Référentiels, licences, règles métier, supervision et validation — toutes les permissions sauf la gestion des comptes |
| Équipe de surveillance (+ équipe suivante, par rotation) | `surveillance_agent` | Cadrage §V « Équipe de surveillance » / « Équipe suivante » | Suivi quotidien, contrôle, observations, main courante, mouvements ; pas de droit d'administration ni de décision sur licences/infractions/missions |
| Inspection / contrôle | `inspector` | Cadrage §V « Inspection / contrôle » | Consultation de l'historique, des inspections et des infractions ; pas de suivi temps réel (Tracking/Logbook/Movements hors périmètre de ce rôle) |

## 2. Cas d'utilisation par module

Remarque transversale : l'historisation (F-004) et la journalisation (S-005) s'appliquent automatiquement à toute création/modification via `spatie/laravel-activitylog` — ce n'est pas un cas d'utilisation déclenché par un acteur, donc absent des diagrammes ci-dessous (voir `docs/MODELE_DONNEES.md` §2.1 `activity_log`).

### Auth

Connexion/déconnexion, sans permission dédiée (accès public au formulaire de connexion).

```mermaid
flowchart LR
    Visiteur["Visiteur non authentifié"]
    Connecte["Utilisateur authentifié, tout rôle"]

    subgraph SYS["Module Auth"]
        UC1(["Se connecter"])
        UC2(["Se déconnecter"])
        UC3(["Activer l'authentification à deux facteurs"])
    end

    Visiteur --- UC1
    Connecte --- UC2

    style UC3 stroke-dasharray: 4 4
```

| Cas d'utilisation | Acteurs | Exigence | Statut |
|---|---|---|---|
| Se connecter | Visiteur | F-001 | Couvert |
| Se déconnecter | Tout utilisateur authentifié | F-001, S-011 | Couvert |
| Activer l'authentification à deux facteurs | — | Cadrage/cahier des charges §13 | **[cible]** — évolution ultérieure |

### Dashboard

```mermaid
flowchart LR
    Tous["Tout utilisateur authentifié (dashboard.view)"]

    subgraph SYS["Module Dashboard"]
        UC1(["Consulter les indicateurs de pilotage"])
        UC2(["Consulter la situation par zone (synthèse)"])
        UC3(["Consulter la fraîcheur des sources"])
        UC4(["Consulter des tableaux de bord interactifs par profil"])
    end

    Tous --- UC1
    Tous --- UC2
    Tous --- UC3

    style UC4 stroke-dasharray: 4 4
```

| Cas d'utilisation | Acteurs | Exigence | Statut |
|---|---|---|---|
| Consulter les indicateurs de pilotage | admin, supervisor, surveillance_agent, inspector | F-010 | Couvert |
| Consulter la situation par zone (synthèse) | idem | A4 | Couvert |
| Consulter la fraîcheur des sources | idem | F-012 | Couvert |
| Consulter des tableaux de bord interactifs par profil, rapports planifiés | — | cahier des charges §13 | **[cible]** |

### Tracking

```mermaid
flowchart LR
    Agent["admin / supervisor / surveillance_agent (tracking.view)"]

    subgraph SYS["Module Tracking"]
        UC1(["Afficher la carte de situation"])
        UC2(["Consulter la situation par zone à un instant donné"])
        UC3(["Consulter l'état des balises"])
        UC4(["Consulter l'historique des traces (vitesse, cap, filtres temporels)"])
    end

    Agent --- UC1
    Agent --- UC2
    Agent --- UC3

    style UC4 stroke-dasharray: 4 4
```

`inspector` n'a pas la permission `tracking.view` : le suivi temps réel n'est pas dans son périmètre (cadrage §V).

| Cas d'utilisation | Acteurs | Exigence | Statut |
|---|---|---|---|
| Afficher la carte de situation | admin, supervisor, surveillance_agent | F-009 | Couvert |
| Consulter la situation par zone à un instant donné | idem | A4 | Couvert |
| Consulter l'état des balises | idem | A2, RG04 | Couvert |
| Consulter l'historique des traces détaillées (vitesse, cap, filtres temporels) | — | cahier des charges §13 | **[cible]** |

### Logbook (main courante)

```mermaid
flowchart LR
    Agent["admin / supervisor / surveillance_agent"]

    subgraph SYS["Module Logbook"]
        UC1(["Consulter la main courante"])
        UC2(["Créer une observation"])
        UC3(["Consulter le détail d'une observation"])
        UC4(["Mettre à jour / clôturer une observation"])
        UC5(["Être notifié d'un relais entre équipes"])
    end

    Agent --- UC1
    Agent --- UC2
    Agent --- UC3
    Agent --- UC4

    style UC5 stroke-dasharray: 4 4
```

`inspector` n'a aucune permission `logbook.*` — la main courante n'est pas dans son périmètre.

| Cas d'utilisation | Acteurs | Exigence | Statut |
|---|---|---|---|
| Consulter la main courante | admin, supervisor, surveillance_agent | A6 | Couvert |
| Créer une observation | idem | A6, RG08 | Couvert |
| Consulter le détail d'une observation | idem | RG08 | Couvert |
| Mettre à jour / clôturer une observation | idem | RG10 | Couvert |
| Être notifié d'un relais entre équipes | — | cadrage §II (« Administration : …notifications ») | **[cible]** — statut/visibilité partagée existent, pas de notification dédiée |

### Movements

```mermaid
flowchart LR
    Agent["admin / supervisor / surveillance_agent"]

    subgraph SYS["Module Movements"]
        UC1(["Consulter les entrées/sorties"])
        UC2(["Enregistrer un mouvement (entrée/sortie)"])
    end

    Agent --- UC1
    Agent --- UC2
```

| Cas d'utilisation | Acteurs | Exigence | Statut |
|---|---|---|---|
| Consulter les entrées/sorties | admin, supervisor, surveillance_agent | A5 | Couvert |
| Enregistrer un mouvement | idem | A5 | Couvert |

### Vessels

```mermaid
flowchart LR
    Tous["Tout utilisateur authentifié (vessels.view)"]
    Gest["admin / supervisor (vessels.manage)"]

    subgraph SYS["Module Vessels"]
        UC1(["Consulter la liste des navires"])
        UC2(["Consulter la fiche navire (vue 360°)"])
        UC3(["Créer un navire"])
        UC4(["Modifier un navire"])
        UC5(["Gérer les documents liés à la fiche navire"])
    end

    Tous --- UC1
    Tous --- UC2
    Gest --- UC3
    Gest --- UC4

    style UC5 stroke-dasharray: 4 4
```

| Cas d'utilisation | Acteurs | Exigence | Statut |
|---|---|---|---|
| Consulter la liste des navires | admin, supervisor, surveillance_agent, inspector | F-002 | Couvert |
| Consulter la fiche navire (vue 360°) | idem | F-003 | Couvert |
| Créer un navire | admin, supervisor | A1, RG01, RG02 | Couvert |
| Modifier un navire | admin, supervisor | A1 | Couvert |
| Gérer les documents liés à la fiche navire | — | cahier des charges §13 | **[cible]** |

### Licenses

```mermaid
flowchart LR
    Tous["Tout utilisateur authentifié (licenses.view)"]

    subgraph SYS["Module Licenses"]
        UC1(["Consulter les licences et échéances"])
        UC2(["Créer / renouveler / suspendre une licence"])
    end

    Tous --- UC1

    style UC2 stroke-dasharray: 4 4
```

| Cas d'utilisation | Acteurs | Exigence | Statut |
|---|---|---|---|
| Consulter les licences et échéances | admin, supervisor, surveillance_agent, inspector | A7, RG07 | Couvert |
| Créer / renouveler / suspendre une licence | admin, supervisor (permission déclarée) | A7, RG07 | **[cible]** — `Permission::LicensesManage` existe et est attribuée, mais aucune route ne l'utilise (consultation seule aujourd'hui) |

### Inspections

```mermaid
flowchart LR
    Tous["Tout utilisateur authentifié (inspections.view)"]

    subgraph SYS["Module Inspections"]
        UC1(["Consulter la liste des inspections"])
        UC2(["Consulter le détail d'une inspection"])
        UC3(["Importer périodiquement les inspections (e-inspection)"])
    end

    Tous --- UC1
    Tous --- UC2

    style UC3 stroke-dasharray: 4 4
```

| Cas d'utilisation | Acteurs | Exigence | Statut |
|---|---|---|---|
| Consulter la liste des inspections | admin, supervisor, surveillance_agent, inspector | F-006 | Couvert |
| Consulter le détail d'une inspection | idem | F-006 | Couvert |
| Importer périodiquement les inspections depuis e-inspection | — | F-006 | **[cible]** — connecteur non branché, données de seeders |

### Infractions

```mermaid
flowchart LR
    Tous["Tout utilisateur authentifié (infractions.view)"]
    Gest["admin / supervisor (infractions.manage)"]

    subgraph SYS["Module Infractions"]
        UC1(["Consulter la liste des infractions"])
        UC2(["Consulter le détail d'une infraction"])
        UC3(["Mettre à jour le statut / la décision d'une infraction"])
    end

    Tous --- UC1
    Tous --- UC2
    Gest --- UC3
```

| Cas d'utilisation | Acteurs | Exigence | Statut |
|---|---|---|---|
| Consulter la liste des infractions | admin, supervisor, surveillance_agent, inspector | F-007 | Couvert |
| Consulter le détail d'une infraction | idem | F-007 | Couvert |
| Mettre à jour le statut / la décision | admin, supervisor | A10, RG09 | Couvert |

### Catches

```mermaid
flowchart LR
    Tous["Tout utilisateur authentifié (catches.view)"]

    subgraph SYS["Module Catches"]
        UC1(["Consulter les déclarations et totaux par espèce"])
    end

    Tous --- UC1
```

| Cas d'utilisation | Acteurs | Exigence | Statut |
|---|---|---|---|
| Consulter les déclarations de captures et totaux par espèce | admin, supervisor, surveillance_agent, inspector | F-008 | Couvert |

### Missions

```mermaid
flowchart LR
    Tous["Tout utilisateur authentifié (missions.view)"]

    subgraph SYS["Module Missions"]
        UC1(["Consulter la liste des missions"])
        UC2(["Consulter le détail d'une mission"])
        UC3(["Créer / suivre une mission (moyens, agents, résultats, pièces)"])
    end

    Tous --- UC1
    Tous --- UC2

    style UC3 stroke-dasharray: 4 4
```

| Cas d'utilisation | Acteurs | Exigence | Statut |
|---|---|---|---|
| Consulter la liste des missions | admin, supervisor, surveillance_agent, inspector | F-005 | Couvert |
| Consulter le détail d'une mission | idem | F-005 | Couvert |
| Créer / suivre une mission (moyens, agents, résultats, pièces) | admin, supervisor (permission déclarée) | F-005 | **[cible]** — `Permission::MissionsManage` existe et est attribuée, mais aucune route ne l'utilise (consultation seule aujourd'hui) |

### Search

```mermaid
flowchart LR
    Tous["Tout utilisateur authentifié"]

    subgraph SYS["Module Search"]
        UC1(["Rechercher par navire, immatriculation, période, mission, inspection, infraction, zone, agent"])
    end

    Tous --- UC1
```

| Cas d'utilisation | Acteurs | Exigence | Statut |
|---|---|---|---|
| Recherche transversale | admin, supervisor, surveillance_agent, inspector | F-002 | Couvert (résultats filtrés selon les permissions de l'utilisateur) |

### Administration

```mermaid
flowchart LR
    Admin["admin (users.manage)"]
    AdminSup["admin / supervisor (connectors.view, audit.view)"]

    subgraph SYS["Module Administration"]
        UC1(["Gérer les comptes utilisateurs (créer, modifier, désactiver)"])
        UC2(["Consulter la matrice des rôles et permissions"])
        UC3(["Consulter l'état des connecteurs"])
        UC4(["Consulter le journal d'audit"])
        UC5(["Importer des fichiers contrôlés"])
        UC6(["Exporter les données (Excel/CSV)"])
        UC7(["Gérer les notifications"])
    end

    Admin --- UC1
    Admin --- UC2
    AdminSup --- UC3
    AdminSup --- UC4

    style UC5 stroke-dasharray: 4 4
    style UC6 stroke-dasharray: 4 4
    style UC7 stroke-dasharray: 4 4
```

| Cas d'utilisation | Acteurs | Exigence | Statut |
|---|---|---|---|
| Gérer les comptes utilisateurs (créer, modifier, désactiver) | admin | F-013, S-011 | Couvert (désactivation via `is_active`, pas de suppression — cohérent avec S-011) |
| Consulter la matrice des rôles et permissions | admin | S-002 | Couvert (rôles/permissions définis en code, non éditables depuis l'UI — voir `[[csp-domain-decisions]]`) |
| Consulter l'état des connecteurs | admin, supervisor | F-012, S-012 | Couvert (`DataSource` — synchronisation réelle non branchée) |
| Consulter le journal d'audit | admin, supervisor | S-005 | Couvert |
| Importer des fichiers contrôlés | — | F-014 | **[cible]** |
| Exporter les données (Excel/CSV) | — | F-011 | **[cible]** — transversal, absent de tous les modules |
| Gérer les notifications | — | cadrage §II | **[cible]** |

## 3. Diagramme global

Vue d'ensemble de l'application : les quatre acteurs et, pour chaque module, son cas d'utilisation représentatif. Le détail exact des permissions est au §2 ; la matrice ci-dessous résume l'accès module par module.

```mermaid
flowchart LR
    Admin["Administrateur technique (admin)"]
    Sup["Responsable du CSP (supervisor)"]
    Agent["Équipe de surveillance (surveillance_agent)"]
    Insp["Inspection / contrôle (inspector)"]

    subgraph CSP["Système d'Information du CSP"]
        Auth(["Se connecter / se déconnecter"])
        Dash(["Consulter les indicateurs de pilotage"])
        Track(["Suivre la situation maritime (carte, zones, balises)"])
        Log(["Tenir la main courante"])
        Mov(["Enregistrer les entrées / sorties"])
        Ves(["Gérer le référentiel navires et la fiche 360°"])
        Lic(["Suivre les licences et échéances"])
        Insc(["Consulter les inspections"])
        Inf(["Suivre les infractions"])
        Catch(["Consulter les captures / déclarations"])
        Miss(["Suivre les missions"])
        Search(["Rechercher transversalement"])
        Adm(["Administrer : utilisateurs, rôles, connecteurs, audit"])
    end

    Admin --- Auth & Dash & Track & Log & Mov & Ves & Lic & Insc & Inf & Catch & Miss & Search & Adm
    Sup --- Auth & Dash & Track & Log & Mov & Ves & Lic & Insc & Inf & Catch & Miss & Search & Adm
    Agent --- Auth & Dash & Track & Log & Mov & Ves & Lic & Insc & Inf & Catch & Miss & Search
    Insp --- Auth & Dash & Ves & Lic & Insc & Inf & Catch & Miss & Search
```

**Lecture du diagramme :** une ligne vers un module signifie que l'acteur y a au moins un cas d'utilisation (voir §2 pour le détail « consultation » vs « gestion »). `supervisor` touche à `Adm` uniquement pour les connecteurs et l'audit, pas la gestion des comptes (`admin` seul).

### Matrice d'accès synthétique

| Module | admin | supervisor | surveillance_agent | inspector |
|---|---|---|---|---|
| Auth | ✓ | ✓ | ✓ | ✓ |
| Dashboard | ✓ | ✓ | ✓ | ✓ |
| Tracking | ✓ | ✓ | ✓ | — |
| Logbook | ✓ | ✓ | ✓ | — |
| Movements | ✓ | ✓ | ✓ | — |
| Vessels (consultation) | ✓ | ✓ | ✓ | ✓ |
| Vessels (gestion) | ✓ | ✓ | — | — |
| Licenses | ✓ | ✓ | ✓ | ✓ |
| Inspections | ✓ | ✓ | ✓ | ✓ |
| Infractions (consultation) | ✓ | ✓ | ✓ | ✓ |
| Infractions (décision) | ✓ | ✓ | — | — |
| Catches | ✓ | ✓ | ✓ | ✓ |
| Missions | ✓ | ✓ | ✓ | ✓ |
| Search | ✓ | ✓ | ✓ | ✓ |
| Administration (connecteurs, audit) | ✓ | ✓ | — | — |
| Administration (comptes) | ✓ | — | — | — |

## 4. Écarts identifiés

Synthèse des cas d'utilisation décrits par les documents mais non routés dans le code à ce jour :

| Cas d'utilisation | Exigence | Remarque |
|---|---|---|
| Authentification à deux facteurs | cahier des charges §13 | Évolution ultérieure explicitement hors périmètre initial |
| Tableaux de bord interactifs par profil, rapports planifiés | cahier des charges §13 | Évolution ultérieure |
| Historique des traces de navires (filtres temporels, vitesse, cap) | cahier des charges §13 | Évolution ultérieure |
| Notification/relais explicite entre équipes | cadrage §II | Dans le périmètre « Administration » du cadrage, pas de table ni de route dédiée |
| Documents liés à la fiche navire | cahier des charges §13 | Évolution ultérieure — voir entité cible `documents` dans `docs/MODELE_DONNEES.md` §2.5 |
| Créer / renouveler / suspendre une licence | A7, RG07 | Permission `licenses.manage` déclarée et attribuée, aucune route ne l'utilise |
| Créer / suivre une mission (moyens, agents, résultats, pièces) | F-005 | Permission `missions.manage` déclarée et attribuée, aucune route ne l'utilise |
| Importer périodiquement les inspections (e-inspection) | F-006 | Connecteur non branché ; données de seeders |
| Importer des fichiers contrôlés | F-014 | Hors périmètre actuel (voir `[[csp-domain-decisions]]`) |
| Exporter les données en Excel/CSV | F-011 | Absent de tous les modules — aucune route ni bouton d'export trouvés dans le code |
| Gérer les notifications | cadrage §II | Périmètre « Administration » du cadrage, non implémenté |

Points également signalés dans `docs/MODELE_DONNEES.md` §4, toujours ouverts : fusion Navire/Vedette, rôles non éditables depuis l'UI malgré S-002, connecteurs réels (THEMIS, NEMO, e-inspection, Access) non branchés.
