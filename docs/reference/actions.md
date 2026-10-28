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
| `infractions.update` | `infractions.manage` | `Modules\Infractions\Actions\UpdateInfraction` |
| `licenses.record` | `licenses.manage` | `Modules\Licenses\Actions\RecordLicense` |
| `logbook.open` | `logbook.create` | `Modules\Logbook\Actions\OpenObservation` |
| `logbook.update` | `logbook.manage` | `Modules\Logbook\Actions\UpdateObservation` |
| `movements.record` | `movements.create` | `Modules\Movements\Actions\RecordMovement` |
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
