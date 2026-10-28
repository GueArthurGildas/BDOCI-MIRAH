<!-- Fichier généré par `php artisan docs:generate` : ne pas le modifier à la main. -->

# Actions et permissions

## Permissions du catalogue

| Code | Module | Verbe | Libellé |
|---|---|---|---|
| `audit.view` | `administration` | `view` | Consulter le journal d'audit |
| `catches.view` | `catches` | `view` | Consulter les captures |
| `communications.create_note` | `communications` | `update` | Laisser des notes internes |
| `communications.manage_dispatch` | `communications` | `update` | Gérer les règles d'envoi automatique |
| `communications.manage_templates` | `communications` | `update` | Gérer les modèles de message |
| `communications.send` | `communications` | `update` | Envoyer des messages |
| `communications.trigger_ai_agent` | `communications` | `update` | Déclencher l'agent IA pour les communications |
| `communications.view` | `communications` | `view` | Consulter les communications |
| `connectors.manage` | `administration` | `update` | Gérer les connecteurs |
| `connectors.sync` | `administration` | `update` | Synchroniser les connecteurs |
| `connectors.view` | `administration` | `view` | Consulter les connecteurs |
| `dashboard.view` | `dashboard` | `view` | Consulter le tableau de bord |
| `infractions.manage` | `infractions` | `update` | Gérer les infractions |
| `infractions.view` | `infractions` | `view` | Consulter les infractions |
| `inspections.view` | `inspections` | `view` | Consulter les inspections |
| `licenses.import` | `licenses` | `update` | Importer et saisir des licences par lot |
| `licenses.manage` | `licenses` | `update` | Gérer les licences |
| `licenses.publish` | `licenses` | `update` | Publier la liste des navires autorisés et le registre annuel |
| `licenses.revoke` | `licenses` | `update` | Révoquer des licences |
| `licenses.suspend` | `licenses` | `update` | Suspendre et lever la suspension des licences |
| `licenses.validate` | `licenses` | `update` | Valider des licences en lot |
| `licenses.view` | `licenses` | `view` | Consulter les licences |
| `logbook.close` | `logbook` | `update` | Clôturer la main courante du jour |
| `logbook.create` | `logbook` | `create` | Saisir des observations |
| `logbook.manage` | `logbook` | `update` | Suivre les observations |
| `logbook.view` | `logbook` | `view` | Consulter la main courante |
| `missions.manage` | `missions` | `update` | Gérer les missions |
| `missions.view` | `missions` | `view` | Consulter les missions |
| `movements.create` | `movements` | `create` | Saisir des mouvements |
| `movements.declare` | `movements` | `update` | Saisir et suivre les déclarations ZEE |
| `movements.view` | `movements` | `view` | Consulter les mouvements |
| `mrep.view` | `mrep` | `view` | Consulter les DPEP, décisions d'entrée et notifications |
| `operations.create` | `operations` | `create` | Ouvrir des opérations |
| `operations.manage` | `operations` | `update` | Suivre et clôturer les opérations |
| `operations.view` | `operations` | `view` | Consulter les opérations |
| `registry.manage` | `registry` | `update` | Gérer les référentiels |
| `registry.view` | `registry` | `view` | Consulter les référentiels |
| `reports.archive` | `reports` | `update` | Archiver les anciennes opérations |
| `reports.export_logbook` | `reports` | `update` | Exporter les lignes de main courante |
| `reports.export_operations` | `reports` | `update` | Exporter les opérations |
| `reports.export_vessels` | `reports` | `update` | Exporter les navires |
| `reports.generate` | `reports` | `update` | Produire les rapports programmés |
| `reports.manage` | `reports` | `update` | Gérer les rapports programmés |
| `reports.view` | `reports` | `view` | Consulter les rapports, exports et archives |
| `reports.view_health` | `reports` | `update` | Voir le bandeau de santé du système |
| `roles.manage` | `administration` | `update` | Gérer les rôles |
| `settings.manage` | `administration` | `update` | Gérer les paramètres |
| `tracking.create` | `tracking` | `create` | Traiter les alertes, ouvrir des créneaux et confirmer les tests |
| `tracking.view` | `tracking` | `view` | Consulter le suivi des positions |
| `users.manage` | `administration` | `update` | Gérer les utilisateurs |
| `vessels.manage` | `vessels` | `update` | Gérer les navires |
| `vessels.match` | `vessels` | `update` | Trancher les doublons de navires |
| `vessels.view` | `vessels` | `view` | Consulter les navires |
| `vms.create` | `vms` | `create` | Affecter des balises, déclarer des avaries et saisir des déclarations ERS |
| `vms.manage` | `vms` | `update` | Gérer balises, installateurs, obligations, arrêts techniques et ordres de cessation |
| `vms.view` | `vms` | `view` | Voir les balises, avaries et déclarations ERS |
| `watch.create` | `watch` | `create` | Saisir des observations, relais et suivis renforcés |
| `watch.manage` | `watch` | `update` | Planifier les rotations, consignes permanentes et listes de veille |
| `watch.view` | `watch` | `view` | Consulter le tableau du matin et la surveillance de quart |

## Actions nommées

Actions exécutées par l'`ActionRunner` (transaction, droit, événement, état courant, audit).

| Code | Permission | Classe |
|---|---|---|
| `administration.change_connector_mode` | `connectors.manage` | `Modules\Administration\Actions\ChangeConnectorMode` |
| `administration.synchronise_connector` | `connectors.sync` | `Modules\Administration\Actions\SynchroniseConnector` |
| `administration.update_external_link` | `connectors.manage` | `Modules\Administration\Actions\UpdateExternalLink` |
| `communications.acknowledge_message` | `communications.send` | `Modules\Communications\Actions\AcknowledgeMessage` |
| `communications.create_note` | `communications.create_note` | `Modules\Communications\Actions\CreateNote` |
| `communications.dispatch_rule` | `communications.manage_dispatch` | `Modules\Communications\Actions\DispatchRuleMessages` |
| `communications.mark_note_read` | `communications.view` | `Modules\Communications\Actions\MarkNoteRead` |
| `communications.record_whatsapp_consent` | `communications.send` | `Modules\Communications\Actions\RecordWhatsAppConsent` |
| `communications.request_ai_note` | `communications.trigger_ai_agent` | `Modules\Communications\Actions\RequestAiNote` |
| `communications.revoke_whatsapp_consent` | `communications.send` | `Modules\Communications\Actions\RevokeWhatsAppConsent` |
| `communications.save_dispatch_rule` | `communications.manage_dispatch` | `Modules\Communications\Actions\SaveDispatchRule` |
| `communications.save_email_connector` | `connectors.manage` | `Modules\Communications\Actions\SaveEmailConnector` |
| `communications.save_template` | `communications.manage_templates` | `Modules\Communications\Actions\SaveMessageTemplate` |
| `communications.send_message` | `communications.send` | `Modules\Communications\Actions\SendMessage` |
| `communications.send_prepared_message` | `communications.send` | `Modules\Communications\Actions\SendPreparedMessage` |
| `communications.update_channel` | `communications.manage_dispatch` | `Modules\Communications\Actions\UpdateCommunicationChannel` |
| `infractions.update` | `infractions.manage` | `Modules\Infractions\Actions\UpdateInfraction` |
| `licenses.attach_document` | `licenses.manage` | `Modules\Licenses\Actions\AttachLicenseDocument` |
| `licenses.cancel_import` | `licenses.import` | `Modules\Licenses\Actions\CancelLicenseImport` |
| `licenses.generate_register` | `licenses.publish` | `Modules\Licenses\Actions\GenerateLicenseRegister` |
| `licenses.import_file` | `licenses.import` | `Modules\Licenses\Actions\ImportLicenseFile` |
| `licenses.lift_suspension` | `licenses.suspend` | `Modules\Licenses\Actions\LiftLicenseSuspension` |
| `licenses.publish_authorized_list` | `licenses.publish` | `Modules\Licenses\Actions\PublishAuthorizedList` |
| `licenses.record` | `licenses.manage` | `Modules\Licenses\Actions\RecordLicense` |
| `licenses.record_external_authorization` | `licenses.manage` | `Modules\Licenses\Actions\RecordExternalAuthorization` |
| `licenses.record_vms_certificate` | `licenses.manage` | `Modules\Licenses\Actions\RecordVmsCertificate` |
| `licenses.revoke` | `licenses.revoke` | `Modules\Licenses\Actions\RevokeLicense` |
| `licenses.set_fnp_status` | `licenses.manage` | `Modules\Licenses\Actions\SetFnpStatus` |
| `licenses.start_batch` | `licenses.import` | `Modules\Licenses\Actions\StartLicenseBatch` |
| `licenses.suspend` | `licenses.suspend` | `Modules\Licenses\Actions\SuspendLicense` |
| `licenses.update` | `licenses.manage` | `Modules\Licenses\Actions\UpdateLicense` |
| `licenses.update_import_line` | `licenses.import` | `Modules\Licenses\Actions\UpdateLicenseImportLine` |
| `licenses.validate_import` | `licenses.validate` | `Modules\Licenses\Actions\ValidateLicenseImport` |
| `licenses.validate_import_line` | `licenses.validate` | `Modules\Licenses\Actions\ValidateLicenseImportLine` |
| `logbook.add_entry` | `logbook.create` | `Modules\Logbook\Actions\AddLogbookEntry` |
| `logbook.close_day` | `logbook.close` | `Modules\Logbook\Actions\CloseLogbookDay` |
| `logbook.open` | `logbook.create` | `Modules\Logbook\Actions\OpenObservation` |
| `logbook.rectify_entry` | `logbook.create` | `Modules\Logbook\Actions\RectifyLogbookEntry` |
| `logbook.set_entry_handling` | `logbook.manage` | `Modules\Logbook\Actions\SetLogbookEntryHandling` |
| `logbook.update` | `logbook.manage` | `Modules\Logbook\Actions\UpdateObservation` |
| `movements.acknowledge_eez_declaration` | `movements.declare` | `Modules\Movements\Actions\AcknowledgeEezDeclaration` |
| `movements.close_eez_declaration` | `movements.declare` | `Modules\Movements\Actions\CloseEezDeclaration` |
| `movements.close_entry_exit_alert` | `movements.declare` | `Modules\Movements\Actions\CloseEntryExitAlert` |
| `movements.explain_entry_exit_variance` | `movements.declare` | `Modules\Movements\Actions\ExplainEntryExitVariance` |
| `movements.qualify_missed_declaration` | `movements.declare` | `Modules\Movements\Actions\QualifyMissedDeclaration` |
| `movements.record` | `movements.create` | `Modules\Movements\Actions\RecordMovement` |
| `movements.record_eez_declaration` | `movements.declare` | `Modules\Movements\Actions\RecordEezDeclaration` |
| `movements.remind_missed_declaration` | `movements.declare` | `Modules\Movements\Actions\RemindMissedDeclaration` |
| `operations.add_event` | `operations.create` | `Modules\Operations\Actions\AddOperationEvent` |
| `operations.close` | `operations.manage` | `Modules\Operations\Actions\CloseOperation` |
| `operations.give_instruction` | `operations.manage` | `Modules\Operations\Actions\GiveOperationInstruction` |
| `operations.open` | `operations.create` | `Modules\Operations\Actions\OpenOperation` |
| `operations.update` | `operations.manage` | `Modules\Operations\Actions\UpdateOperation` |
| `registry.create_record` | `registry.manage` | `Modules\Registry\Actions\CreateRegistryRecord` |
| `registry.update_record` | `registry.manage` | `Modules\Registry\Actions\UpdateRegistryRecord` |
| `registry.update_source_priorities` | `registry.manage` | `Modules\Registry\Actions\UpdateSourcePriorities` |
| `reports.archive_operation` | `reports.archive` | `Modules\Reports\Actions\ArchiveOperation` |
| `reports.export_logbook` | `reports.export_logbook` | `Modules\Reports\Actions\ExportLogbook` |
| `reports.export_operations` | `reports.export_operations` | `Modules\Reports\Actions\ExportOperations` |
| `reports.export_vessels` | `reports.export_vessels` | `Modules\Reports\Actions\ExportVessels` |
| `reports.generate` | `reports.generate` | `Modules\Reports\Actions\GenerateScheduledReport` |
| `reports.save_scheduled_report` | `reports.manage` | `Modules\Reports\Actions\SaveScheduledReport` |
| `tracking.acknowledge_alert` | `tracking.create` | `Modules\Tracking\Actions\AcknowledgeAlert` |
| `tracking.confirm_slot_position` | `tracking.create` | `Modules\Tracking\Actions\ConfirmSlotPosition` |
| `tracking.handle_alert` | `tracking.create` | `Modules\Tracking\Actions\HandleAlert` |
| `tracking.open_slot` | `tracking.create` | `Modules\Tracking\Actions\OpenControlSlot` |
| `tracking.run_sweep` | `tracking.create` | `Modules\Tracking\Actions\RunSweep` |
| `tracking.save_alert_rule` | `settings.manage` | `Modules\Tracking\Actions\SaveAlertRule` |
| `vessels.add_actor` | `vessels.manage` | `Modules\Vessels\Actions\AddVesselActor` |
| `vessels.add_identifier` | `vessels.manage` | `Modules\Vessels\Actions\AddVesselIdentifier` |
| `vessels.ingest_source` | `vessels.manage` | `Modules\Vessels\Actions\IngestVesselSource` |
| `vessels.list_on_external_list` | `vessels.manage` | `Modules\Vessels\Actions\ListVesselOnExternalList` |
| `vessels.register` | `vessels.manage` | `Modules\Vessels\Actions\RegisterVessel` |
| `vessels.settle_duplicate` | `vessels.match` | `Modules\Vessels\Actions\SettleVesselDuplicate` |
| `vessels.update` | `vessels.manage` | `Modules\Vessels\Actions\UpdateVessel` |
| `vms.assign_beacon` | `vms.create` | `Modules\Vms\Actions\AssignBeacon` |
| `vms.authorize_cutoff` | `vms.manage` | `Modules\Vms\Actions\AuthorizeEmissionCutoff` |
| `vms.create_beacon` | `vms.manage` | `Modules\Vms\Actions\CreateBeacon` |
| `vms.create_installer` | `vms.manage` | `Modules\Vms\Actions\CreateApprovedInstaller` |
| `vms.declare_failure` | `vms.create` | `Modules\Vms\Actions\DeclareVmsFailure` |
| `vms.issue_cessation_order` | `vms.manage` | `Modules\Vms\Actions\IssueCessationOrder` |
| `vms.record_assessment` | `vms.create` | `Modules\Vms\Actions\RecordCompatibilityAssessment` |
| `vms.record_attestation` | `vms.create` | `Modules\Vms\Actions\RecordWorkingOrderAttestation` |
| `vms.record_ers` | `vms.create` | `Modules\Vms\Actions\RecordErsDeclaration` |
| `vms.record_installation` | `vms.create` | `Modules\Vms\Actions\RecordBeaconInstallation` |
| `vms.record_manual_report` | `vms.create` | `Modules\Vms\Actions\RecordManualPositionReport` |
| `vms.request_assessment` | `vms.create` | `Modules\Vms\Actions\RequestVmsAssessment` |
| `vms.request_new_beacon` | `vms.create` | `Modules\Vms\Actions\RequestNewBeacon` |
| `vms.set_obligations` | `vms.manage` | `Modules\Vms\Actions\SetReportingObligations` |
| `watch.acknowledge_relay` | `watch.create` | `Modules\Watch\Actions\AcknowledgeRelay` |
| `watch.add_to_director_list` | `watch.manage` | `Modules\Watch\Actions\AddVesselToDirectorList` |
| `watch.close_entry` | `watch.manage` | `Modules\Watch\Actions\CloseWatchEntry` |
| `watch.create_standing_instruction` | `watch.manage` | `Modules\Watch\Actions\CreateStandingInstruction` |
| `watch.end_standing_instruction` | `watch.manage` | `Modules\Watch\Actions\EndStandingInstruction` |
| `watch.place_under_enhanced_monitoring` | `watch.create` | `Modules\Watch\Actions\PlaceVesselUnderEnhancedMonitoring` |
| `watch.plan_rotations` | `watch.manage` | `Modules\Watch\Actions\PlanRotations` |
| `watch.record_observation` | `watch.create` | `Modules\Watch\Actions\RecordSurveillanceObservation` |
| `watch.set_observation_status` | `watch.create` | `Modules\Watch\Actions\SetSurveillanceObservationStatus` |
| `watch.start_handover` | `watch.create` | `Modules\Watch\Actions\StartShiftHandover` |
