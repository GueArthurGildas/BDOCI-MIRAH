<!-- Fichier généré par `php artisan docs:generate` : ne pas le modifier à la main. -->

# Rôles et permissions

Permissions accordées par défaut à chaque rôle système (l'héritage `Directeur → Chef d'équipe → Agent` est déjà appliqué). L'administrateur peut les modifier dans l'application.

| Permission | Administrateur | Directeur | Chef d'équipe | Agent |
|---|---|---|---|---|
| `audit.view` | oui | — | — | — |
| `catches.view` | — | oui | oui | oui |
| `connectors.manage` | oui | — | — | — |
| `connectors.view` | oui | oui | oui | — |
| `dashboard.view` | oui | oui | oui | oui |
| `infractions.manage` | — | oui | oui | — |
| `infractions.view` | — | oui | oui | oui |
| `inspections.view` | — | oui | oui | oui |
| `licenses.manage` | — | oui | oui | — |
| `licenses.view` | — | oui | oui | oui |
| `logbook.close` | — | oui | oui | — |
| `logbook.create` | — | oui | oui | oui |
| `logbook.manage` | — | oui | oui | oui |
| `logbook.view` | — | oui | oui | oui |
| `missions.manage` | — | oui | oui | — |
| `missions.view` | — | oui | oui | oui |
| `movements.create` | — | oui | oui | oui |
| `movements.view` | — | oui | oui | oui |
| `operations.create` | — | oui | oui | oui |
| `operations.manage` | — | oui | oui | — |
| `operations.view` | — | oui | oui | oui |
| `roles.manage` | oui | — | — | — |
| `settings.manage` | oui | — | — | — |
| `tracking.view` | — | oui | oui | oui |
| `users.manage` | oui | — | — | — |
| `vessels.manage` | — | oui | oui | — |
| `vessels.view` | — | oui | oui | oui |
| `watch.create` | — | oui | oui | oui |
| `watch.manage` | — | oui | oui | — |
| `watch.view` | — | oui | oui | oui |
