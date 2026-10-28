# Vms

Conformité des balises, avaries et déclarations ERS (module M3 de la spécification, cas d'usage UC04 et UC07, règles RG03 et RG05).

- Tables : `beacons`, `beacon_assignments`, `approved_installers`, `vms_assessment_requests`, `new_beacon_requests`, `compatibility_assessments`, `beacon_installations`, `vessel_reporting_obligations`, `vms_failures`, `vms_cutoff_authorizations`, `manual_position_reports`, `working_order_attestations`, `cessation_orders`, `ers_declarations`, `ers_catch_lines` (modèles dans `app/Models`).
- Actions : `CreateBeacon`, `AssignBeacon` (remplacement historisé), `SetReportingObligations`, `CreateApprovedInstaller`, `RequestVmsAssessment`, `RecordCompatibilityAssessment`, `RequestNewBeacon`, `RecordBeaconInstallation`, `DeclareVmsFailure`, `RecordManualPositionReport`, `RecordWorkingOrderAttestation`, `IssueCessationOrder`, `AuthorizeEmissionCutoff`, `RecordErsDeclaration`.
- Support partagé (`App\Support\Vms`) : `BeaconAssigner`, `BeaconCompliance` (RG03), `BeaconMirror` (projection sur `tracking_devices`).
- Permissions : `vms.view`, `vms.create` (Agent compris), `vms.manage` (Chef d'équipe, Directeur).
- Hypothèses : H-3b-6, H-3b-7, H-3b-8 (`docs/assumptions/phase-3b.md`).
- Écarts : le contrôle « aucun départ du port avec un VMS en avarie » et l'écart ERS / constat e-inspection ne sont pas implémentés.
