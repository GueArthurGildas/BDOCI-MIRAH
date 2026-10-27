# 0006 — Données existantes : repartir des seeders

**Statut** : accepté le 2026-10-27, décision de l'utilisateur (option B de D6). Il n'existe encore aucune base déployée (confirmé par l'utilisateur).

## Contexte

- Les données actuelles proviennent uniquement des seeders : `DatabaseSeeder`, `ReferenceDataSeeder`, `DemoDataSeeder`, `ConnectorActivitySeeder` et `RolesAndPermissionsSeeder`. Les connecteurs réels (THEMIS, NENO, e-inspection, DPEP) ne sont pas branchés.
- Aucun serveur n'héberge de base avec des données réelles ; `main` est la branche de déploiement mais rien n'est déployé.
- La refonte change profondément le modèle (navire, identifiants, personnes, journal d'événements) et les rôles.

## Décision

Aucune migration de données : après chaque phase, la base est reconstruite avec `php artisan migrate:fresh --seed`. Les seeders et les factories restent la source des données de démonstration et doivent évoluer avec chaque phase.

## Conséquences

- Positives : pas de migrations de données lourdes ni de passerelles entre ancien et nouveau modèle ; les migrations peuvent être réécrites ou réordonnées tant que rien n'est déployé.
- Négatives : toute base locale est effacée à chaque reconstruction ; quand un premier déploiement aura eu lieu, cette liberté disparaît (il faudra alors des migrations incrémentales et une ADR de remplacement).
- Travail induit : maintenir les seeders et les factories dans la définition de « terminé » de chaque phase ; revoir cette décision avant le premier déploiement réel.

## Alternatives écartées

- **A. Migrer les données existantes** : coûteux et inutile, puisqu'il s'agit de données de démonstration.
