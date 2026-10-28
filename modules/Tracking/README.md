# Tracking

Suivi des positions, alertes VMS, créneaux de contrôle et balayages (module M3 de la spécification, UC05 à UC07 et UC22, règles RG04 et RG06).

- Tables : `positions` (partitionnée par mois), `position_daily_summaries`, `monthly_aggregates`, `tracks`, `screen_captures`, `identity_anomalies`, `emission_cutoffs`, `control_slots`, `slot_positions`, `sweeps`, `sweep_anomalies`, `alert_rules`, `alerts`, `alert_handlings` (modèles dans `app/Models`).
- Pages : carte, zones, balises, alertes (traitement), créneaux de contrôle (photo, trois tests assistés, comptage par zone et localité, balayage, lien THEMIS), règles d'alerte (Admin).
- Actions : `OpenControlSlot`, `ConfirmSlotPosition`, `RunSweep`, `AcknowledgeAlert`, `HandleAlert`, `SaveAlertRule`.
- Commandes planifiées : `tracking:evaluate-alert-rules` (15 min, remplace `tracking:notify-silent-beacons`), `tracking:summarize-positions` (quotidienne), `tracking:aggregate-monthly` (mensuelle), `tracking:create-position-partitions` (mensuelle).
- Support partagé (`App\Support\Vms`) : `SilenceThreshold`, `AlertRuleEvaluator`, `PositionFeed`, `AssistedTests`, `GeoCalculator` (PostGIS), `EmissionRate`, `LocalityResolver`, `PositionPartitions`, `TrackBuilder`.
- Permissions : `tracking.view`, `tracking.create` (Agent compris) ; les règles d'alerte se modifient avec `settings.manage` (Admin).
- Hypothèses : H-3b-1 à H-3b-5 et H-3b-7 (`docs/assumptions/phase-3b.md`).
