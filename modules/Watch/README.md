# Watch

Surveillance de quart et tableau du matin (module M5 de la spécification) : rotations et relèves, observations de surveillance avec relais, compteur de vérifications, suivi renforcé, consignes permanentes, liste de veille.

- Tables : `rotations`, `shift_handovers`, `surveillance_observations`, `relays`, `standing_instructions`, `check_counters`, `watch_entries`, `consistency_rules`, `zone_counts` (modèles dans `app/Models`; `consistency_rules` et `zone_counts` sont alimentées par la phase 3b).
- Actions : `RecordSurveillanceObservation`, `SetSurveillanceObservationStatus`, `AcknowledgeRelay`, `PlaceVesselUnderEnhancedMonitoring`, `AddVesselToDirectorList`, `CloseWatchEntry`, `CreateStandingInstruction`, `EndStandingInstruction`, `PlanRotations`, `StartShiftHandover`.
- Requêtes partagées : `App\Queries\MorningBoard` (tableau du matin trié par origine puis ancienneté).
- Permissions : `watch.view`, `watch.create` (Agent compris), `watch.manage` (Chef d'équipe, Directeur).
- Réglages : groupe « Surveillance de quart et main courante » (heures de quart, nombre d'équipes, seuil de vérifications).
- Hypothèses : H-3a-1, H-3a-2, H-3a-5, H-3a-7 (`docs/assumptions/phase-3a.md`).
