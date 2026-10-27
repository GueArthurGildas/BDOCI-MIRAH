# 0004 — Droits flexibles derrière les `Gate`

**Statut** : accepté le 2026-10-27, décision de l'utilisateur (option B de D4). En cours : une première partie est livrée.

## Contexte

- Les droits reposent sur Spatie Permission (tables `roles`, `permissions`, `model_has_roles`, `role_has_permissions`, `model_has_permissions`). Les permissions sont déclarées dans l'énumération `App\Support\Authorization\Permission` (format `<module>.<action>`), les rôles de base dans `App\Support\Authorization\Role`, et le code les vérifie avec `can()`, `->can(...)` sur les routes et le paramètre `permission:` de la navigation.
- La spécification (§3.3) demande des rôles hérités, des groupes, un catalogue d'actions par module et des permissions limitables à un module, une zone ou une période ; l'Admin doit pouvoir les modifier sans changer le code. Le schéma v1 prévoit `role`, `groupe`, `action_catalogue`, `permission`, `utilisateur_role`, `utilisateur_groupe`.
- Spatie ne gère ni groupes, ni héritage, ni portée par zone ou période.

## Décision

Le modèle de la spécification sera introduit en phase 1, **derrière les `Gate` existantes** : les appels `can()` ne changent pas, seule la résolution des droits change.

Déjà livré, sur Spatie :

- une page Administration › Rôles et permissions : création, modification et suppression de rôles, choix des permissions par module, journal d'audit des changements (événements `created`, `updated`, `permissions_changed`, `deleted`) ;
- permission `roles.manage`, réservée à l'Admin ;
- rôles système non renommables ni supprimables, rôle `admin` à jeu fixe, suppression refusée tant qu'un utilisateur porte le rôle ;
- description des rôles (traduite pour les rôles système, saisie pour les rôles personnalisés) ;
- seeder qui ne réécrit plus les permissions modifiées depuis l'interface (seul `admin` est resynchronisé).

Les permissions elles-mêmes forment le **catalogue d'actions** : chaque module les déclare dans le code, car une permission que rien ne vérifie n'a aucun effet. L'Admin les accorde aux rôles ; une nouvelle permission apparaît quand un module livre une nouvelle action.

Reste à faire : groupes, héritage de rôles, portée zone et période, table `action_catalog` alimentée par les modules.

## Conséquences

- Positives : les droits sont déjà modifiables sans toucher au code ; l'interface construite se prolonge vers le catalogue d'actions ; pas de réécriture des contrôles d'accès.
- Négatives : deux niveaux coexisteront un temps (énumération + base) ; la résolution des droits devra gérer la portée, au risque de coûts de requête.
- Travail induit : phase 1 (tables `action_catalog`, groupes, héritage, portée, résolveur de `Gate`) ; ajouter la portée seulement si une phase l'exige.

## Alternatives écartées

- **A. Garder Spatie tel quel** : groupes, héritage et portée zone/période impossibles, ce qui contredit §3.3 de la spécification.
- **Remplacer les `Gate` par un système maison** : forcerait à réécrire tous les contrôles d'accès existants.
