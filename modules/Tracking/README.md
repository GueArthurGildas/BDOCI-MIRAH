# Tracking

Suivi des positions, alertes VMS, créneaux de contrôle et balayages (module M3 de la spécification, UC05 à UC07 et UC22, règles RG04 et RG06).

- Tables : `positions` (partitionnée par mois), `position_daily_summaries`, `monthly_aggregates`, `tracks`, `screen_captures`, `identity_anomalies`, `emission_cutoffs`, `control_slots`, `slot_positions`, `sweeps`, `sweep_anomalies`, `alert_rules`, `alerts`, `alert_handlings` (modèles dans `app/Models`).
- Pages : carte, zones, balises, alertes (traitement), créneaux de contrôle (photo, trois tests assistés, comptage par zone et localité, balayage, lien THEMIS), règles d'alerte (Admin).
- Actions : `OpenControlSlot`, `ConfirmSlotPosition`, `RunSweep`, `AcknowledgeAlert`, `HandleAlert`, `SaveAlertRule`.
- Commandes planifiées : `tracking:evaluate-alert-rules` (15 min, remplace `tracking:notify-silent-beacons`), `tracking:summarize-positions` (quotidienne), `tracking:aggregate-monthly` (mensuelle), `tracking:create-position-partitions` (mensuelle).
- Support partagé (`App\Support\Vms`) : `SilenceThreshold`, `AlertRuleEvaluator`, `PositionFeed`, `AssistedTests`, `GeoCalculator` (PostGIS), `EmissionRate`, `LocalityResolver`, `PositionPartitions`, `TrackBuilder`.
- Permissions : `tracking.view`, `tracking.create` (Agent compris) ; les règles d'alerte se modifient avec `settings.manage` (Admin).
- Hypothèses : H-3b-1 à H-3b-5 et H-3b-7 (`docs/assumptions/phase-3b.md`).

## État des balises

- **Alimentation** : les balises listées sont les `tracking_devices` non retirées, projection des affectations de balises (module Vms, `BeaconMirror`). La dernière émission est la position la plus récente de `positions` pour le navire, reçue par le `PositionFeed` (source VMS).
- **Rupture d'émission** : sans position depuis plus longtemps que le seuil (`SilenceThreshold` : règle d'alerte « silence » active, sinon `surveillance.beacon_silence_threshold_hours`). Les navires sous autorisation de coupure ou en arrêt technique en cours ne comptent pas dans `SilentBeacons`, donc ni dans les alertes ni dans les indicateurs.
- **Lecture rapide** : `App\Queries\BeaconStates` lit la dernière position de chaque navire par jointure latérale sur l'index `(vessel_id, recorded_at)`, calcule le statut en SQL, filtre, trie et pagine (10 par page) dans la base. `SilentBeacons` et `Position::latestPerVessel` suivent le même principe. Mesure sur 420 000 positions : 0,04 s contre 0,23 s pour la page.
- **Filtres** : recherche (navire ou identifiant), statut, « sans émission depuis » (2, 3, 4, 5 jours, 1 ou 2 semaines, 1 mois : `App\Support\Vms\SilencePresets`), période de la dernière émission. L'écart depuis la dernière émission s'affiche en gras (`EmissionGap`).
