<!-- Fichier généré par `php artisan docs:generate` : ne pas le modifier à la main. -->

# Rôles et permissions

Permissions accordées par défaut à chaque rôle système (l'héritage `Directeur → Chef d'équipe → Agent` est déjà appliqué). L'administrateur peut les modifier dans l'application.

| Permission | Administrateur | Directeur | Chef d'équipe | Agent |
|---|---|---|---|---|
| `audit.view` | oui | — | — | — |
| `catches.view` | — | oui | oui | oui |
| `communications.create_note` | — | oui | oui | oui |
| `communications.manage_dispatch` | — | oui | oui | — |
| `communications.manage_templates` | — | oui | oui | — |
| `communications.send` | — | oui | oui | oui |
| `communications.trigger_ai_agent` | — | oui | oui | — |
| `communications.view` | — | oui | oui | oui |
| `connectors.manage` | oui | — | — | — |
| `connectors.sync` | oui | — | — | — |
| `connectors.view` | oui | oui | oui | — |
| `dashboard.view` | oui | oui | oui | oui |
| `indicators.view` | — | oui | oui | — |
| `infractions.create` | — | oui | oui | — |
| `infractions.decide` | — | oui | oui | — |
| `infractions.decide_measures` | — | oui | — | — |
| `infractions.manage` | — | oui | oui | — |
| `infractions.manage_types` | oui | — | — | — |
| `infractions.view` | — | oui | oui | oui |
| `inspections.view` | — | oui | oui | oui |
| `licenses.import` | — | oui | oui | — |
| `licenses.manage` | — | oui | oui | — |
| `licenses.publish` | — | oui | oui | — |
| `licenses.revoke` | — | oui | oui | — |
| `licenses.suspend` | — | oui | oui | — |
| `licenses.validate` | — | oui | oui | — |
| `licenses.view` | — | oui | oui | oui |
| `logbook.close` | — | oui | oui | — |
| `logbook.create` | — | oui | oui | oui |
| `logbook.manage` | — | oui | oui | oui |
| `logbook.view` | — | oui | oui | oui |
| `missions.authorize` | — | oui | oui | — |
| `missions.authorize_as_director` | — | oui | — | — |
| `missions.create` | — | oui | oui | oui |
| `missions.manage` | — | oui | oui | — |
| `missions.view` | — | oui | oui | oui |
| `movements.create` | — | oui | oui | oui |
| `movements.declare` | — | oui | oui | oui |
| `movements.view` | — | oui | oui | oui |
| `mrep.view` | — | oui | oui | oui |
| `operations.create` | — | oui | oui | oui |
| `operations.manage` | — | oui | oui | — |
| `operations.view` | — | oui | oui | oui |
| `registry.manage` | — | oui | oui | — |
| `registry.view` | — | oui | oui | oui |
| `reports.archive` | — | oui | oui | — |
| `reports.export_logbook` | — | oui | oui | — |
| `reports.export_operations` | — | oui | oui | — |
| `reports.export_vessels` | — | oui | oui | — |
| `reports.generate` | — | oui | oui | — |
| `reports.manage` | — | oui | oui | — |
| `reports.view` | — | oui | oui | — |
| `reports.view_health` | oui | oui | oui | — |
| `roles.manage` | oui | — | — | — |
| `settings.manage` | oui | — | — | — |
| `tracking.create` | — | oui | oui | oui |
| `tracking.view` | — | oui | oui | oui |
| `users.manage` | oui | — | — | — |
| `vessels.manage` | — | oui | oui | — |
| `vessels.match` | — | oui | oui | — |
| `vessels.view` | — | oui | oui | oui |
| `vms.create` | — | oui | oui | oui |
| `vms.manage` | — | oui | oui | — |
| `vms.view` | — | oui | oui | oui |
| `watch.create` | — | oui | oui | oui |
| `watch.manage` | — | oui | oui | — |
| `watch.view` | — | oui | oui | oui |
| `witnesses.create` | — | oui | oui | oui |
| `witnesses.manage` | — | oui | oui | — |
| `witnesses.view` | — | oui | oui | oui |
