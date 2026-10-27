---
name: dev-env
description: Operate the Docker development environment (containers, database, tests, PostGIS, scheduler) and avoid the known traps. Use when running artisan, tests or psql, when the app returns 502, when a database needs resetting, or when touching compose.yaml and .env.
---

# Dev environment

Services (`compose.yaml`): `app` (PHP-FPM), `nginx` (http://localhost:8000), `postgres` (`postgis/postgis:16-3.5`, bases `laravel` and `testing`, port 5435), `scheduler` (`schedule:work`), `node` (Vite, http://localhost:5173). Redis, queue worker and Mailpit were removed (sessions and cache in the database, queue `sync`, mail to the log): bring back the queue with `QUEUE_CONNECTION=database` when a phase needs background jobs.

## Commands (non-interactive, from the repo root)

- `docker compose exec -T app php artisan <cmd>`; tests: `docker compose exec -T app php artisan test [--filter=...]` (about 40 s, uses the `testing` database through `RefreshDatabase`).
- Database client: `docker compose exec -T postgres psql -U laravel -d laravel -At -c "..."`.
- Humans use `make test`, `make fresh`, `make shell` (see `make help`).
- Demo data: `docker compose exec -T app php artisan migrate:fresh --seed` (also rebuilds module registry and vessel states).

## Traps

1. **Never run `artisan ... --env=testing`**: there is no `.env.testing`, so it targets the development database and wipes it. The suite already uses the `testing` database via `phpunit.xml`.
2. **502 Bad Gateway** after `app` was recreated: nginx cached the old container IP. `docker restart bdoci-mirah-nginx-1`.
3. **Config or `.env` changes** are not seen by the long-running `scheduler`: `docker compose restart scheduler`. `app` is PHP-FPM: restart it after changing PHP ini or the image.
4. **Stale CSS**: restart `bdoci-mirah-node-1` (see the `ui-verify` skill).
5. **PostGIS image swap on an existing volume** reports a collation version mismatch: for each database run `REINDEX DATABASE <db>; ALTER DATABASE <db> REFRESH COLLATION VERSION;` (also `template1` and `postgres`).
6. **PostgreSQL functions survive `migrate:fresh`**: migrations that create them must be replayable (`CREATE OR REPLACE`).
7. `activity_log` is append-only (triggers): code and tests can insert but never update, delete or truncate it.
8. Scratch databases for validation (for example executing `docs/schema/schema_v2.sql`): create `schema_check`, run with `psql -v ON_ERROR_STOP=1 -q < file.sql`, then drop it.
9. After `migrate` on a shared or deployed database run `php artisan permissions:sync` (action catalog) and `php artisan modules:sync` (module registry).
10. Long commands (image pulls, big runs): `run_in_background` and wait for the notification instead of sleeping.
