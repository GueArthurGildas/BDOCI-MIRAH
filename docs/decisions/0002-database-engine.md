# 0002 — SGBD : rester sur PostgreSQL 16

**Statut** : accepté le 2026-10-27, décision de l'utilisateur (option B de D2).

## Contexte

- Le schéma SQL v1 est écrit pour **MySQL 8 / InnoDB** et son auteur demande de le signaler si le projet utilise PostgreSQL (MCD, §15).
- Le projet tourne déjà sur **PostgreSQL 16** (`compose.yaml`, `DB_CONNECTION=pgsql`) ; toutes les migrations existantes sont écrites pour lui. Les énumérations PHP sont stockées en colonnes `string`, les données semi-structurées en `json` ou `jsonb` (`notifications.data`, `data_source_rejections.payload`).
- La spécification demande une table `position` partitionnée par mois, un journal d'audit en ajout seul (triggers) et des vues calculées.

## Décision

PostgreSQL 16 reste le SGBD. Le schéma v1 est traduit en schéma physique v2 selon la correspondance ci-dessous.

| MySQL 8 (schéma v1) | PostgreSQL 16 |
|---|---|
| `ENUM(...)` | colonne `string` + contrainte `CHECK`, la liste de valeurs étant portée par l'énumération PHP (convention actuelle du projet) |
| `JSON` | `jsonb` |
| `TINYINT(1)` | `boolean` |
| `DATETIME`, `DATETIME(3)` | `timestamp` (précision milliseconde si nécessaire) |
| `GEOMETRY`, `LINESTRING` | types PostGIS avec SRID 4326 (voir 0003) |
| `BIGINT UNSIGNED AUTO_INCREMENT` | `bigint` identity (`$table->id()`) |
| `PARTITION BY RANGE (YEAR()*100 + MONTH())` sur `position` | partitionnement déclaratif `PARTITION BY RANGE (date_emission)`, une partition par mois, créées par une tâche planifiée |
| triggers `SIGNAL SQLSTATE` (journal d'audit en ajout seul, main courante verrouillée) | fonctions et triggers PL/pgSQL qui lèvent une exception |
| `SET FOREIGN_KEY_CHECKS` | inutile : les migrations Laravel ordonnent les créations |
| `CHECK` MySQL 8 | `CHECK` PostgreSQL, sans changement |

## Conséquences

- Positives : aucune réécriture d'infrastructure ; `jsonb` indexable ; partitionnement déclaratif, triggers et PostGIS disponibles ; cohérence avec l'existant.
- Négatives : le schéma v1 ne peut pas être exécuté tel quel ; toutes les contraintes d'énumération deviennent des `CHECK` à maintenir en parallèle des énumérations PHP.
- Travail induit : schéma physique v2 en phase 0 ; partitionnement de `positions` en phase 3b ; triggers d'audit en phase 1.

## Alternatives écartées

- **A. Passer à MySQL 8**, pour suivre le schéma v1 : réécrire l'infrastructure et toutes les migrations sans bénéfice, et renoncer à PostGIS.

## Application

La traduction complète du schéma v1 en PostgreSQL est dans `docs/schema/schema_v2.sql` ; elle a été exécutée sur une base PostGIS vide (175 tables, 285 clés étrangères, 105 contraintes `CHECK`, 6 vues, 3 triggers). Les correspondances de types sont détaillées dans `docs/schema/CONVENTIONS.md`, point 2.
