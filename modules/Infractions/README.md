# Infractions (M8 infractions et suites)

Dossier d'infraction complet : constat, infractions retenues (nomenclature), preuves, scellés, saisies, mesures conservatoires, procès-verbal figé en PDF, suites contentieuses, décisions, paiements, recours et récidive. Les décisions restent humaines : aucune suite, sanction ou mesure n'est décidée automatiquement.

- Tables : `infraction_types`, `infraction_cases`, `upheld_infractions`, `evidence_items`, `seals`, `precautionary_measures`, `seizures`, `violation_reports`, `litigation_follow_ups`, `decisions`, `payments`, `appeals` et la vue `v_repeat_offenses` (modèles dans `app/Models`). Les anciennes tables `infractions` et `inspections` sont reprises par la migration puis supprimées.
- Actions : `OpenInfractionCase`, `UpdateInfractionCase`, `UpholdInfraction`, `AddEvidenceItem`, `RecordSeal`, `RecordSeizure`, `ProposePrecautionaryMeasure`, `DecidePrecautionaryMeasure`, `RecordViolationReport`, `OpenLitigationFollowUp`, `RecordDecision`, `RecordPayment`, `FileAppeal`, `RecordAppealOutcome`, `CloseInfractionCase`, `SaveInfractionType`.
- Écrans : liste filtrable, ouverture d'un dossier (depuis une inspection, une mission, un signalement ou un navire), fiche complète, nomenclature (Admin).
- Documents : le PV et chaque décision sont des PDF figés (`DocumentStore`, empreinte contrôlable).
- Permissions : `infractions.view` (Agent compris), `infractions.create` et `infractions.manage` (instruction : Chef d'équipe, Directeur, groupe Instruction), `infractions.decide` (suites, décisions, paiements, recours, clôture), `infractions.decide_measures` (**Directeur seul**), `infractions.manage_types` (Admin).
- Partagé : `App\Support\Infractions\CaseParties` (capitaine et armateur actuels du navire), `RepeatOffense` (récidive lue dans la vue `v_repeat_offenses`).
- Effet sur la licence : la suspension et la révocation de licence se décident dans le module Licences (`infraction_case_id` accepté, contraintes ajoutées en phase 6) ; la fiche du dossier y renvoie.
- Hypothèses : H-6-1, H-6-3, H-6-5, H-6-6, H-6-7, H-6-9 (`docs/assumptions/phase-6.md`).
