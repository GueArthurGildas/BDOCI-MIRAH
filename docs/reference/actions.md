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
| `logbook.create` | `logbook` | `create` | Saisir des observations |
| `logbook.manage` | `logbook` | `update` | Suivre les observations |
| `logbook.view` | `logbook` | `view` | Consulter la main courante |
| `missions.manage` | `missions` | `update` | Gérer les missions |
| `missions.view` | `missions` | `view` | Consulter les missions |
| `movements.create` | `movements` | `create` | Saisir des mouvements |
| `movements.declare` | `movements` | `update` | Saisir et suivre les déclarations ZEE |
| `movements.view` | `movements` | `view` | Consulter les mouvements |
| `roles.manage` | `administration` | `update` | Gérer les rôles |
| `settings.manage` | `administration` | `update` | Gérer les paramètres |
| `tracking.view` | `tracking` | `view` | Consulter le suivi des positions |
| `users.manage` | `administration` | `update` | Gérer les utilisateurs |
| `vessels.manage` | `vessels` | `update` | Gérer les navires |
| `vessels.view` | `vessels` | `view` | Consulter les navires |

## Actions nommées

Actions exécutées par l'`ActionRunner` (transaction, droit, événement, état courant, audit).

| Code | Permission | Classe |
|---|---|---|
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
| `vessels.register` | `vessels.manage` | `Modules\Vessels\Actions\RegisterVessel` |
| `vessels.update` | `vessels.manage` | `Modules\Vessels\Actions\UpdateVessel` |
