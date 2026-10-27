# 0001 — Langue des identifiants : anglais avec glossaire

**Statut** : accepté le 2026-10-27, décision de l'utilisateur (option B de D1).

## Contexte

- La spécification (`database/docs/`, §1) impose des noms de tables et de colonnes en français sans accents (`navire`, `licence`, `evenement_navire`) ; le schéma SQL v1 est écrit ainsi (175 tables).
- Le code actuel est entièrement en anglais : tables (`vessels`, `licenses`, `tracking_devices`, `positions`, `data_sources`), modèles, énumérations, routes, clés de traduction.
- Une règle du projet impose un code en anglais, lisible sans commentaires ; l'interface, elle, passe par `lang/{en,fr}` et `modules/*/lang/{en,fr}`, avec un test de complétude (`TranslationCompletenessTest`).
- Les relecteurs métier travaillent à partir de la spécification française.

## Décision

Tous les identifiants techniques (tables, colonnes, classes, routes, permissions, clés de traduction) sont en anglais. Un **glossaire** français ↔ anglais (`docs/GLOSSAIRE.md`, point de départ : annexe A du plan) fait le lien avec la spécification. L'interface reste traduite en français et en anglais.

## Conséquences

- Positives : une seule convention dans tout le dépôt ; pas de mélange dans les modèles ni dans les migrations ; cohérence avec l'existant (rien à renommer aujourd'hui).
- Négatives : chaque table et colonne du schéma v1 doit être traduite (travail de la phase 0, schéma physique v2) ; risque de dérive de traduction entre la spécification et le code ; un relecteur métier doit passer par le glossaire.
- Travail induit : rédiger le glossaire en phase 0 ; ajouter un test qui vérifie que chaque table y figure ; citer le nom français de la spécification dans le README de chaque module.

## Alternatives écartées

- **A. Identifiants en français sans accents**, comme la spécification : fidèle au document de référence, mais contredit la règle du projet et obligerait à renommer l'existant.
- **Mélange** (français pour les nouvelles tables, anglais pour les anciennes) : pire que les deux options, écarté d'emblée.

## Application

Le schéma physique v2 (`docs/schema/schema_v2.sql`) applique cette décision aux 175 tables de la spécification : règles de nommage dans `docs/schema/CONVENTIONS.md`, correspondances dans `docs/GLOSSAIRE.md` et `docs/schema/GLOSSAIRE_COLONNES.md`. Un test (`tests/Feature/GlossaryTest.php`) vérifie que chaque table y figure.
