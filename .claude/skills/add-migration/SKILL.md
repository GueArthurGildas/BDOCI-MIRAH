---
name: add-migration
description: Write a Laravel migration, model and factory for a table of the physical schema v2 (docs/schema), respecting naming and type conventions and keeping the glossaries in sync. Use when implementing tables of the refactoring plan or adding any table or column.
---

# Migration from schema v2

Sources of truth: `docs/schema/schema_v2.sql` (reference DDL, validated on PostGIS), `docs/schema/CONVENTIONS.md` (rules), `docs/GLOSSAIRE.md` and `docs/schema/GLOSSAIRE_COLONNES.md` (names). Identifiers are English (decision D1); French spec names live in the glossaries.

## Rules

- Migration file `database/migrations/<date>_<verb>_<table>_table.php`; anonymous class; `up` and `down`.
- Table plural snake_case; pure pivot = two singulars, alphabetical. Keys `id()`; foreign keys `foreignId('<entity>_id')->constrained()`; user actor columns `<participle>_by` (no `_id`).
- Timestamps `_at`, dates `_on` (or `_from`/`_to`/`_until`), booleans `is_`/`has_`/`requires_`/`includes_`, counts `_count`, `jsonb` for JSON, `timestamps()` on every table with an `id` except `positions` and the audit log.
- Enumerations: `string` column (length 40 or less) holding English snake_case values, plus a CHECK in PostgreSQL via `DB::statement("ALTER TABLE ... ADD CONSTRAINT <table>_<col>_check CHECK (<col> IN (...))")`, and a PHP backed enum with `HasTranslatedLabel` (labels in `lang/{en,fr}/enums.php`).
- Geometry: PostGIS `geometry(..., 4326)` through `DB::statement` and a GiST index. Avoid reserved words (`order`, `check`, `trigger`, `user`...).
- **No hard deletes** of business data. Soft deletes only for technical reference data.
- **PostgreSQL functions and triggers must be replayable**: `CREATE OR REPLACE FUNCTION`, `DROP TRIGGER IF EXISTS` before create. `migrate:fresh` drops tables but not functions.

## Also do

1. Model (`#[Fillable([...])]`, casts to enums), factory, and seed data when the demo needs it.
2. If a name or type deviates from `schema_v2.sql`, edit the SQL, `GLOSSAIRE_COLONNES.md` and add a row to section 11 of `CONVENTIONS.md`. `tests/Feature/GlossaryTest.php` fails when an application table is missing from `docs/GLOSSAIRE.md`; update its "Situation actuelle" cell.
3. Run `php artisan docs:generate` (the schema reference in `docs/reference/schema.md` changes with every migration; `GeneratedDocumentationTest` fails otherwise).
4. Run the suite **twice** in a row (a second run catches migrations that are not replayable): `docker compose exec -T app php artisan test`.
5. One phase at a time; wait for Gildas' validation before the next (see the `phase-workflow` skill).
