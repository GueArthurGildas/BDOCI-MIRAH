# 0003 — Géométrie : PostGIS

**Statut** : accepté le 2026-10-27, décision de l'utilisateur (option B de D3).

## Contexte

- Les zones sont aujourd'hui stockées en JSON/GeoJSON (`zones.geometry`, colonne `json` non nulle) ; aucun calcul géographique n'est possible en base.
- La spécification en a besoin : zones traversées calculées pour une trace (`trace_patrouille`), test de trajectoire conforme aux zones autorisées et à la distance des côtes (module 5), règles de zone en milles (`regle_de_zone` : 1, 3, 4 et 12 milles), comptage des navires par zone et localité, zone calculée de chaque position. Le schéma v1 emploie `GEOMETRY` et `LINESTRING`.

## Décision

PostGIS est adopté. Il est **déjà en place** :

- l'image du service `postgres` est `postgis/postgis:16-3.5` (`compose.yaml`) ;
- la migration `enable_postgis_extension` exécute `CREATE EXTENSION IF NOT EXISTS postgis`, y compris dans la base `testing` ;
- les index et la version de tri des bases `laravel` et `testing` ont été reconstruits après le changement d'image (`REINDEX DATABASE`, `ALTER DATABASE ... REFRESH COLLATION VERSION`).

Les colonnes géométriques utiliseront le SRID 4326.

## Conséquences

- Positives : calculs de zones, distances et traces faits par la base ; index spatiaux ; pas de géométrie recalculée en PHP.
- Négatives : image plus lourde (environ 634 Mo) ; sur un volume existant, changer d'image impose de reconstruire les index et de rafraîchir la version de tri (sans objet tant qu'aucune base n'est déployée, voir 0006).
- Travail induit : convertir `zones.geometry` en type PostGIS et ajouter les colonnes de position en phase 3b ; traces de patrouille en phase 6. Aucune fonctionnalité actuelle ne dépend encore de l'extension.

## Alternatives écartées

- **A. Conserver le JSON/GeoJSON** : aucun calcul de distance ni d'intersection en base ; ces calculs seraient à écrire à la main en PHP, pour chaque test de trajectoire.

## Application

Le schéma v2 utilise `geometry(Geometry, 4326)` et `geometry(LineString, 4326)` avec des index GiST (`zones`, `tracks`, `patrol_tracks`). Une requête d'essai (point dans une zone, distance en kilomètres) a été exécutée avec succès.
