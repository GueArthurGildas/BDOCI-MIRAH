<!-- Fichier généré par `php artisan docs:generate` : ne pas le modifier à la main. -->

# Actions et permissions

## Permissions du catalogue

| Code | Module | Verbe | Libellé |
|---|---|---|---|
| `audit.view` | `administration` | `view` | Consulter le journal d'audit |
| `catches.view` | `catches` | `view` | Consulter les captures |
| `connectors.manage` | `administration` | `update` | Gérer les connecteurs |
| `connectors.view` | `administration` | `view` | Consulter les connecteurs |
| `dashboard.view` | `dashboard` | `view` | Consulter le tableau de bord |
| `infractions.manage` | `infractions` | `update` | Gérer les infractions |
| `infractions.view` | `infractions` | `view` | Consulter les infractions |
| `inspections.view` | `inspections` | `view` | Consulter les inspections |
| `licenses.manage` | `licenses` | `update` | Gérer les licences |
| `licenses.view` | `licenses` | `view` | Consulter les licences |
| `logbook.close` | `logbook` | `update` | Clôturer la main courante du jour |
| `logbook.create` | `logbook` | `create` | Saisir des observations |
| `logbook.manage` | `logbook` | `update` | Suivre les observations |
| `logbook.view` | `logbook` | `view` | Consulter la main courante |
| `missions.manage` | `missions` | `update` | Gérer les missions |
| `missions.view` | `missions` | `view` | Consulter les missions |
| `movements.create` | `movements` | `create` | Saisir des mouvements |
| `movements.view` | `movements` | `view` | Consulter les mouvements |
| `operations.create` | `operations` | `create` | Ouvrir des opérations |
| `operations.manage` | `operations` | `update` | Suivre et clôturer les opérations |
| `operations.view` | `operations` | `view` | Consulter les opérations |
| `roles.manage` | `administration` | `update` | Gérer les rôles |
| `settings.manage` | `administration` | `update` | Gérer les paramètres |
| `tracking.view` | `tracking` | `view` | Consulter le suivi des positions |
| `users.manage` | `administration` | `update` | Gérer les utilisateurs |
| `vessels.manage` | `vessels` | `update` | Gérer les navires |
| `vessels.view` | `vessels` | `view` | Consulter les navires |
| `watch.create` | `watch` | `create` | Saisir des observations, relais et suivis renforcés |
| `watch.manage` | `watch` | `update` | Planifier les rotations, consignes permanentes et listes de veille |
| `watch.view` | `watch` | `view` | Consulter le tableau du matin et la surveillance de quart |

## Actions nommées

Actions exécutées par l'`ActionRunner` (transaction, droit, événement, état courant, audit).

| Code | Permission | Classe |
|---|---|---|
| `infractions.update` | `infractions.manage` | `Modules\Infractions\Actions\UpdateInfraction` |
| `licenses.record` | `licenses.manage` | `Modules\Licenses\Actions\RecordLicense` |
| `logbook.add_entry` | `logbook.create` | `Modules\Logbook\Actions\AddLogbookEntry` |
| `logbook.close_day` | `logbook.close` | `Modules\Logbook\Actions\CloseLogbookDay` |
| `logbook.open` | `logbook.create` | `Modules\Logbook\Actions\OpenObservation` |
| `logbook.rectify_entry` | `logbook.create` | `Modules\Logbook\Actions\RectifyLogbookEntry` |
| `logbook.set_entry_handling` | `logbook.manage` | `Modules\Logbook\Actions\SetLogbookEntryHandling` |
| `logbook.update` | `logbook.manage` | `Modules\Logbook\Actions\UpdateObservation` |
| `movements.record` | `movements.create` | `Modules\Movements\Actions\RecordMovement` |
| `operations.add_event` | `operations.create` | `Modules\Operations\Actions\AddOperationEvent` |
| `operations.close` | `operations.manage` | `Modules\Operations\Actions\CloseOperation` |
| `operations.give_instruction` | `operations.manage` | `Modules\Operations\Actions\GiveOperationInstruction` |
| `operations.open` | `operations.create` | `Modules\Operations\Actions\OpenOperation` |
| `operations.update` | `operations.manage` | `Modules\Operations\Actions\UpdateOperation` |
| `vessels.register` | `vessels.manage` | `Modules\Vessels\Actions\RegisterVessel` |
| `vessels.update` | `vessels.manage` | `Modules\Vessels\Actions\UpdateVessel` |
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
