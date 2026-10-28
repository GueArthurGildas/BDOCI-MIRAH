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
| `connectors.view` | `administration` | `view` | Consulter les connecteurs |
| `dashboard.view` | `dashboard` | `view` | Consulter le tableau de bord |
| `infractions.manage` | `infractions` | `update` | Gérer les infractions |
| `infractions.view` | `infractions` | `view` | Consulter les infractions |
| `inspections.view` | `inspections` | `view` | Consulter les inspections |
| `licenses.manage` | `licenses` | `update` | Gérer les licences |
| `licenses.view` | `licenses` | `view` | Consulter les licences |
| `logbook.create` | `logbook` | `create` | Saisir des observations |
| `logbook.manage` | `logbook` | `update` | Suivre les observations |
| `logbook.view` | `logbook` | `view` | Consulter la main courante |
| `missions.manage` | `missions` | `update` | Gérer les missions |
| `missions.view` | `missions` | `view` | Consulter les missions |
| `movements.create` | `movements` | `create` | Saisir des mouvements |
| `movements.declare` | `movements` | `update` | Saisir et suivre les déclarations ZEE |
| `movements.view` | `movements` | `view` | Consulter les mouvements |
| `registry.manage` | `registry` | `update` | Gérer les référentiels |
| `registry.view` | `registry` | `view` | Consulter les référentiels |
| `roles.manage` | `administration` | `update` | Gérer les rôles |
| `settings.manage` | `administration` | `update` | Gérer les paramètres |
| `tracking.view` | `tracking` | `view` | Consulter le suivi des positions |
| `users.manage` | `administration` | `update` | Gérer les utilisateurs |
| `vessels.manage` | `vessels` | `update` | Gérer les navires |
| `vessels.match` | `vessels` | `update` | Trancher les doublons de navires |
| `vessels.view` | `vessels` | `view` | Consulter les navires |

## Actions nommées

Actions exécutées par l'`ActionRunner` (transaction, droit, événement, état courant, audit).

| Code | Permission | Classe |
|---|---|---|
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
| `licenses.record` | `licenses.manage` | `Modules\Licenses\Actions\RecordLicense` |
| `logbook.open` | `logbook.create` | `Modules\Logbook\Actions\OpenObservation` |
| `logbook.update` | `logbook.manage` | `Modules\Logbook\Actions\UpdateObservation` |
| `movements.acknowledge_eez_declaration` | `movements.declare` | `Modules\Movements\Actions\AcknowledgeEezDeclaration` |
| `movements.close_eez_declaration` | `movements.declare` | `Modules\Movements\Actions\CloseEezDeclaration` |
| `movements.close_entry_exit_alert` | `movements.declare` | `Modules\Movements\Actions\CloseEntryExitAlert` |
| `movements.explain_entry_exit_variance` | `movements.declare` | `Modules\Movements\Actions\ExplainEntryExitVariance` |
| `movements.qualify_missed_declaration` | `movements.declare` | `Modules\Movements\Actions\QualifyMissedDeclaration` |
| `movements.record` | `movements.create` | `Modules\Movements\Actions\RecordMovement` |
| `movements.record_eez_declaration` | `movements.declare` | `Modules\Movements\Actions\RecordEezDeclaration` |
| `movements.remind_missed_declaration` | `movements.declare` | `Modules\Movements\Actions\RemindMissedDeclaration` |
| `registry.create_record` | `registry.manage` | `Modules\Registry\Actions\CreateRegistryRecord` |
| `registry.update_record` | `registry.manage` | `Modules\Registry\Actions\UpdateRegistryRecord` |
| `registry.update_source_priorities` | `registry.manage` | `Modules\Registry\Actions\UpdateSourcePriorities` |
| `vessels.add_actor` | `vessels.manage` | `Modules\Vessels\Actions\AddVesselActor` |
| `vessels.add_identifier` | `vessels.manage` | `Modules\Vessels\Actions\AddVesselIdentifier` |
| `vessels.ingest_source` | `vessels.manage` | `Modules\Vessels\Actions\IngestVesselSource` |
| `vessels.list_on_external_list` | `vessels.manage` | `Modules\Vessels\Actions\ListVesselOnExternalList` |
| `vessels.register` | `vessels.manage` | `Modules\Vessels\Actions\RegisterVessel` |
| `vessels.settle_duplicate` | `vessels.match` | `Modules\Vessels\Actions\SettleVesselDuplicate` |
| `vessels.update` | `vessels.manage` | `Modules\Vessels\Actions\UpdateVessel` |
