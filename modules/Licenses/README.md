# Licenses (M2 suivi des licences)

Licences de pêche déjà délivrées hors application : enregistrement, zones, engins, espèces et conditions, suspension (3 mois maximum hors force majeure), révocation, statut FNP, certificats VMS et autorisations externes, import CSV et saisie par lot avec validation en une fois, registre annuel, liste des navires autorisés (RG01) et alertes d'échéance à 30, 60 et 90 jours.

- Tables : `licenses`, `license_zone`, `gear_license`, `license_species`, `license_conditions`, `license_documents`, `fnp_statuses`, `license_suspensions`, `license_revocations`, `vms_compatibility_certificates`, `external_authorizations`, `license_imports`, `license_import_lines`, `license_registers`, `authorized_list_publications` (modèles dans `app/Models`).
- Actions : `RecordLicense`, `UpdateLicense`, `AttachLicenseDocument`, `SuspendLicense`, `LiftLicenseSuspension`, `RevokeLicense`, `SetFnpStatus`, `RecordVmsCertificate`, `RecordExternalAuthorization`, `ImportLicenseFile`, `StartLicenseBatch`, `UpdateLicenseImportLine`, `ValidateLicenseImportLine`, `ValidateLicenseImport`, `CancelLicenseImport`, `PublishAuthorizedList`, `GenerateLicenseRegister`.
- Écrans : liste et fiche d'une licence, formulaire, imports et validation par lot, conformité d'un navire (FNP, certificats, autorisations), navires autorisés et publications, registre annuel.
- Permissions : `licenses.view` (Agent compris), `licenses.manage`, `licenses.import`, `licenses.validate`, `licenses.suspend`, `licenses.revoke`, `licenses.publish` (Chef d'équipe et Directeur ; le groupe « Gestion FNP / licences » reçoit manage, import, validate et publish).
- Alertes : `licenses:notify-expiring` (quotidienne), un résumé par palier ; délais dans les réglages de l'Admin.
- Calculs partagés : `App\Support\Licenses\AuthorizedList` et `CurrentLicense`.
- L'expiration se calcule à partir de la date, jamais stockée.
- Hypothèses : H-4a-1 à H-4a-8 (`docs/assumptions/phase-4a.md`).
- Clés étrangères : `license_suspensions.infraction_case_id` et `license_revocations.infraction_case_id` pointent vers `infraction_cases` (contrainte ajoutée en phase 6 ; le formulaire accepte `?infraction_case=` depuis un dossier d'infraction) ; `vms_failure_id` reste tardive (phase 3b).
