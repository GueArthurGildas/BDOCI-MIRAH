<!-- Fichier généré par `php artisan docs:generate` : ne pas le modifier à la main. -->

# Routes

Routes nommées de l'application et du socle, avec la permission exigée.

| Nom | Méthode | URL | Permission |
|---|---|---|---|
| `administration.audit` | GET | `/administration/audit` | `audit.view` |
| `administration.connectors` | GET | `/administration/connectors` | `connectors.view` |
| `administration.connectors.comments.store` | POST | `/administration/connectors/{dataSource}/comments` | `connectors.manage` |
| `administration.connectors.errors.reopen` | POST | `/administration/connectors/errors/{error}/reopen` | `connectors.manage` |
| `administration.connectors.errors.resolve` | POST | `/administration/connectors/errors/{error}/resolve` | `connectors.manage` |
| `administration.connectors.rejections.handle` | PATCH | `/administration/connectors/rejections/{rejection}` | `connectors.manage` |
| `administration.connectors.show` | GET | `/administration/connectors/{dataSource}` | `connectors.view` |
| `administration.custom-fields.create` | GET | `/administration/custom-fields/create` | `settings.manage` |
| `administration.custom-fields.edit` | GET | `/administration/custom-fields/{customField}/edit` | `settings.manage` |
| `administration.custom-fields.index` | GET | `/administration/custom-fields` | `settings.manage` |
| `administration.custom-fields.store` | POST | `/administration/custom-fields` | `settings.manage` |
| `administration.custom-fields.update` | PUT, PATCH | `/administration/custom-fields/{customField}` | `settings.manage` |
| `administration.grants.destroy` | DELETE | `/administration/grants/{grant}` | `roles.manage` |
| `administration.groups.create` | GET | `/administration/groups/create` | `roles.manage` |
| `administration.groups.destroy` | DELETE | `/administration/groups/{group}` | `roles.manage` |
| `administration.groups.edit` | GET | `/administration/groups/{group}/edit` | `roles.manage` |
| `administration.groups.grants.store` | POST | `/administration/groups/{group}/grants` | `roles.manage` |
| `administration.groups.index` | GET | `/administration/groups` | `roles.manage` |
| `administration.groups.store` | POST | `/administration/groups` | `roles.manage` |
| `administration.groups.update` | PUT, PATCH | `/administration/groups/{group}` | `roles.manage` |
| `administration.reference-lists.create` | GET | `/administration/reference-lists/create` | `settings.manage` |
| `administration.reference-lists.edit` | GET | `/administration/reference-lists/{referenceList}/edit` | `settings.manage` |
| `administration.reference-lists.index` | GET | `/administration/reference-lists` | `settings.manage` |
| `administration.reference-lists.store` | POST | `/administration/reference-lists` | `settings.manage` |
| `administration.reference-lists.update` | PUT, PATCH | `/administration/reference-lists/{referenceList}` | `settings.manage` |
| `administration.reference-lists.values.store` | POST | `/administration/reference-lists/{referenceList}/values` | `settings.manage` |
| `administration.reference-lists.values.update` | PATCH | `/administration/reference-lists/{referenceList}/values/{value}` | `settings.manage` |
| `administration.roles.create` | GET | `/administration/roles/create` | `roles.manage` |
| `administration.roles.destroy` | DELETE | `/administration/roles/{role}` | `roles.manage` |
| `administration.roles.edit` | GET | `/administration/roles/{role}/edit` | `roles.manage` |
| `administration.roles.grants.store` | POST | `/administration/roles/{role}/grants` | `roles.manage` |
| `administration.roles.index` | GET | `/administration/roles` | `roles.manage` |
| `administration.roles.store` | POST | `/administration/roles` | `roles.manage` |
| `administration.roles.update` | PUT, PATCH | `/administration/roles/{role}` | `roles.manage` |
| `administration.settings` | GET | `/administration/settings` | `settings.manage` |
| `administration.settings.reset` | DELETE | `/administration/settings` | `settings.manage` |
| `administration.settings.update` | PUT | `/administration/settings` | `settings.manage` |
| `administration.users.create` | GET | `/administration/users/create` | `users.manage` |
| `administration.users.edit` | GET | `/administration/users/{user}/edit` | `users.manage` |
| `administration.users.index` | GET | `/administration/users` | `users.manage` |
| `administration.users.store` | POST | `/administration/users` | `users.manage` |
| `administration.users.update` | PUT, PATCH | `/administration/users/{user}` | `users.manage` |
| `catches.index` | GET | `/catches` | `catches.view` |
| `dashboard` | GET | `/dashboard` | `dashboard.view` |
| `infractions.index` | GET | `/infractions` | `infractions.view` |
| `infractions.show` | GET | `/infractions/{infraction}` | `infractions.view` |
| `infractions.update` | PATCH | `/infractions/{infraction}` | `infractions.view`, `infractions.manage` |
| `inspections.index` | GET | `/inspections` | `inspections.view` |
| `inspections.show` | GET | `/inspections/{inspection}` | `inspections.view` |
| `licenses.create` | GET | `/licenses/create` | `licenses.manage` |
| `licenses.index` | GET | `/licenses` | `licenses.view` |
| `licenses.store` | POST | `/licenses` | `licenses.manage` |
| `locale.update` | GET | `/locale/{locale}` | — |
| `logbook.create` | GET | `/logbook/create` | `logbook.create` |
| `logbook.index` | GET | `/logbook` | `logbook.view` |
| `logbook.show` | GET | `/logbook/{observation}` | `logbook.view` |
| `logbook.store` | POST | `/logbook` | `logbook.create` |
| `logbook.update` | PATCH | `/logbook/{observation}` | `logbook.manage` |
| `login` | GET | `/login` | — |
| `logout` | POST | `/logout` | — |
| `missions.index` | GET | `/missions` | `missions.view` |
| `missions.show` | GET | `/missions/{mission}` | `missions.view` |
| `movements.create` | GET | `/movements/create` | `movements.create` |
| `movements.eez.acknowledge` | POST | `/eez-declarations/{declaration}/acknowledge` | `movements.declare` |
| `movements.eez.alerts` | GET | `/eez-declarations/alerts` | `movements.view` |
| `movements.eez.alerts.close` | POST | `/eez-declarations/alerts/{alert}/close` | `movements.declare` |
| `movements.eez.close` | POST | `/eez-declarations/{declaration}/close` | `movements.declare` |
| `movements.eez.create` | GET | `/eez-declarations/create` | `movements.declare` |
| `movements.eez.explain` | POST | `/eez-declarations/links/{link}/explain` | `movements.declare` |
| `movements.eez.index` | GET | `/eez-declarations` | `movements.view` |
| `movements.eez.missed.index` | GET | `/eez-declarations/missed` | `movements.view` |
| `movements.eez.missed.qualify` | POST | `/eez-declarations/missed/{missed}/qualify` | `movements.declare` |
| `movements.eez.missed.remind` | POST | `/eez-declarations/missed/{missed}/remind` | `movements.declare` |
| `movements.eez.show` | GET | `/eez-declarations/{declaration}` | `movements.view` |
| `movements.eez.store` | POST | `/eez-declarations` | `movements.declare` |
| `movements.index` | GET | `/movements` | `movements.view` |
| `movements.store` | POST | `/movements` | `movements.create` |
| `notifications.feed` | GET | `/notifications/feed` | — |
| `notifications.index` | GET | `/notifications` | — |
| `notifications.open` | GET | `/notifications/{notification}` | — |
| `notifications.read-all` | POST | `/notifications/read-all` | — |
| `search` | GET | `/search` | — |
| `tracking.beacons` | GET | `/tracking/beacons` | `tracking.view` |
| `tracking.map` | GET | `/tracking/map` | `tracking.view` |
| `tracking.zones` | GET | `/tracking/zones` | `tracking.view` |
| `vessels.create` | GET | `/vessels/create` | `vessels.manage` |
| `vessels.edit` | GET | `/vessels/{vessel}/edit` | `vessels.manage` |
| `vessels.index` | GET | `/vessels` | `vessels.view` |
| `vessels.show` | GET | `/vessels/{vessel}` | `vessels.view` |
| `vessels.store` | POST | `/vessels` | `vessels.manage` |
| `vessels.update` | PUT | `/vessels/{vessel}` | `vessels.manage` |
