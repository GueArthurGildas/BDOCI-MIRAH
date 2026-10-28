# Reports (M10 Pilotage)

Rapports programmés, exports journalisés par droit, archivage à 5 ans en lecture seule, supervision de l'agent IA. Le bandeau « santé du système » est dans le noyau (`App\Support\Health\SystemHealth`, partiel `layouts/partials/health-banner`). Couvre UC24 (partiel) et UC27 (partiel, la première action « créer une note » est dans Communications).

## Tables

`scheduled_reports`, `export_log` (colonnes ajoutées : `permission`, `format`, `filters`, `row_count`, ajout seul par trigger), `operation_archives` (triggers : une opération archivée et ses tables filles sont en lecture seule).

## Actions (ADR 0010)

| Code | Permission |
|---|---|
| `reports.save_scheduled_report` | `reports.manage` |
| `reports.generate` | `reports.generate` |
| `reports.export_operations`, `export_vessels`, `export_logbook` | `reports.export_operations`, `reports.export_vessels`, `reports.export_logbook` |
| `reports.archive_operation` | `reports.archive` |

Autres permissions : `reports.view` (écrans), `reports.view_health` (bandeau : Directeur, Chef d'équipe, Admin).

## Tâches planifiées

`reports:run-scheduled` (05:00) produit pour chaque rapport actif le document figé de la dernière période terminée (une fois par période) et prévient le destinataire ; `reports:archive-operations` (02:00). Elles s'exécutent sous le compte `system` (groupe `scheduled_reports`).

## Réglages

`archive.after_years` (défaut 5) et `alerts.scheduled_reports.enabled`.

## Hypothèses

H-9-1 à H-9-4 : voir `docs/assumptions/phase-9.md`.
