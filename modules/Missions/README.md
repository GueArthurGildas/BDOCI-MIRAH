# Missions (M9 missions et moyens)

Programmation, autorisation, équipe et moyens, briefing et préparation, exécution avec trace GPX, unités rencontrées à trois niveaux (vue, approchée, contrôlée), évènements, débriefing et bilan **calculé** (jamais saisi).

- Tables : `missions`, `mission_authorizations`, `mission_participants`, `observers`, `observer_boardings`, `assets`, `mission_assets`, `mission_briefings`, `mission_preparations`, `patrol_tracks`, `observed_units`, `mission_events`, `mission_debriefings`, `mission_reviews` (modèles dans `app/Models`). Les anciennes tables `missions`, `mission_vessel` et `mission_user` sont reprises par la migration puis supprimées.
- Actions : `ScheduleMission`, `AuthorizeMission`, `StartMission`, `CompleteMission`, `CloseMission`, `ComputeMissionReview`, `AddMissionParticipant`, `AssignMissionAsset`, `RecordAssetUsage`, `SaveMissionBriefing`, `SaveMissionDebriefing`, `AddMissionEvent`, `RecordObservedUnit`, `ImportPatrolTrack`, `RecordObserverBoarding`, `SaveAsset`.
- Cycle : programmée, autorisée, en cours, terminée, clôturée. Le bilan est recalculé à la fin et à la clôture (`MissionReviewCalculator`) et à la demande.
- Unité rencontrée : un seul enregistrement, trois niveaux ; le niveau « contrôlée » exige le lien vers l'inspection e-inspection (contrainte `CHECK` en base). Une unité dont les marques correspondent à une immatriculation est rattachée au navire et écrit un évènement dans son journal.
- Trace : import GPX, stockée en `geometry(LineString, 4326)` avec index GiST ; les zones traversées sont calculées par intersection (PostGIS).
- Autorisation (V21) : le Directeur autorise par défaut ; un réglage de l'Admin par type de mission autorise aussi le Chef d'équipe (`surveillance.missions.team_lead_authorizes.<type>`, classe `MissionAuthorizationRule`).
- Permissions : `missions.view`, `missions.create` (Agent compris), `missions.manage` (exécution), `missions.authorize` (Directeur, Chef d'équipe), `missions.authorize_as_director` (Directeur seul).
- Hypothèses : H-6-2, H-6-4, H-6-7, H-6-9, H-6-10 (`docs/assumptions/phase-6.md`).
