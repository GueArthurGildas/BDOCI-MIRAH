# Documentation technique

- [Docker](#docker)
- [Configuration](#configuration)
- [Architecture](#architecture)
- [Domaine et modules](#domaine-et-modules)
- [Traductions](#traductions)

## Docker

PHP, Composer, Node et PostgreSQL tournent dans des conteneurs : seuls Docker (Compose v2) et `make` sont requis sur la machine.

| Service     | Rôle                                   | Accès                        |
|-------------|----------------------------------------|------------------------------|
| `app`       | PHP 8.4-FPM + Composer + Xdebug        | —                            |
| `nginx`     | Serveur web                            | http://localhost:8000        |
| `node`      | Node 24 + Vite (HMR)                   | http://localhost:5173        |
| `postgres`  | PostgreSQL 16 + PostGIS (bases `laravel` et `testing`) | `localhost:5435` |
| `scheduler` | `schedule:work` (tâches planifiées)    | —                            |

### Installation

`make setup` crée le `.env`, y règle votre UID/GID, construit les images, installe les dépendances Composer, génère la clé d'application si elle manque, démarre les conteneurs, lance les migrations et crée le lien `public/storage`. Les dépendances npm sont installées automatiquement au démarrage du conteneur `node`.

### Commandes

```bash
make help                          # liste toutes les commandes
make up / make down                # démarrer / arrêter
make shell                         # shell dans le conteneur PHP
make artisan c="make:model Client -m"
make composer c="require vendor/paquet"
make npm c="install paquet"
make assets                        # build front de production (public/build)
make migrate / make fresh          # migrations / reset + seeders
make test                          # tests (base PostgreSQL `testing`)
make db                            # client PostgreSQL (psql)
make logs
make clean                         # ⚠️ supprime aussi les volumes (données)
```

### How/Why?

Tous les services sont décrits dans `compose.yaml` et reliés au même réseau Docker privé `laravel`. Sur ce réseau, chaque conteneur joint les autres par leur nom de service : c'est pour ça que `.env` contient `DB_HOST=postgres` au lieu de `127.0.0.1`. Le réseau utilise un MTU de 1400 (voir [Configuration](#configuration)).

Le code du projet est monté dans les conteneurs (`./` → `/var/www/html`) : une modification faite sur la machine est visible immédiatement dans les conteneurs, sans reconstruire d'image.

#### `app` — PHP-FPM

- **Image** : construite localement à partir de `docker/php/Dockerfile` (cible `dev`), taguée `bdoci-mirah/app:dev`. C'est la seule image du projet qui est construite ; toutes les autres sont téléchargées telles quelles depuis Docker Hub.
- **Étape `base`** :
  - part de l'image officielle `php:8.4-fpm-bookworm` ;
  - installe les outils système (`git`, `unzip`, `curl`, client PostgreSQL `psql`) ;
  - installe les extensions PHP avec `install-php-extensions` : `pdo_pgsql`, `pgsql`, `bcmath`, `intl`, `zip`, `gd`, `exif`, `pcntl`, `opcache`, `redis` (installée mais inutilisée pour l'instant, voir *Services retirés*) ;
  - copie Composer depuis l'image officielle `composer:2` ;
  - applique la configuration `docker/php/php.ini` (mémoire, taille des uploads, OPcache réglé pour le développement).
- **Utilisateur** : l'étape `base` crée aussi un utilisateur `laravel` qui a le même UID/GID que vous (variables `UID`/`GID` du `.env`), et PHP-FPM tourne sous cet utilisateur. Les fichiers créés par Laravel ou Composer vous appartiennent donc sur votre machine (pas de fichiers `root` dans `storage/` ou `vendor/`).
- **Étape `dev`** : ajoute Xdebug et sa configuration `docker/php/xdebug.ini`. Xdebug est inactif tant que `XDEBUG_MODE=off`.
- **Démarrage** : lance `php-fpm`, qui écoute sur le port 9000 du réseau interne. Ce port n'est pas exposé sur votre machine : seul `nginx` lui parle.
- **Dépendances** : attend que `postgres` soit *healthy* avant de démarrer.

#### `scheduler` — tâches planifiées

- **Image** : la même que `app` (bloc commun `x-app` dans `compose.yaml`) : même PHP, mêmes extensions, même code monté, mêmes variables d'environnement. Seule la commande de démarrage change.
- **Rôle** : `php artisan schedule:work` exécute chaque minute les tâches planifiées déclarées par les modules (alertes de balises muettes toutes les 15 minutes, licences à échéance chaque jour à 07:00). Pas besoin de cron.

#### `nginx` — serveur web

- **Image** : `nginx:1.27-alpine`, utilisée telle quelle.
- **Configuration** : `docker/nginx/default.conf` est monté à la place de la configuration par défaut. Elle :
  - sert le dossier `public/` ;
  - redirige toutes les URL vers `index.php` (routing Laravel) ;
  - transmet les requêtes PHP à `app:9000` (FastCGI) ;
  - bloque l'accès aux fichiers cachés (`.env`, `.git`…).
- **Accès** : le code est monté en lecture seule. Le port `APP_PORT` (8000 par défaut) est exposé : c'est l'adresse de l'application.

#### `node` — Vite

- **Image** : `node:24-alpine`, utilisée telle quelle. Elle tourne avec votre UID/GID, donc `node_modules/` vous appartient.
- **Démarrage** : exécute `npm install`, puis `npm run dev -- --host 0.0.0.0`. Le serveur Vite compile le SCSS et le JS à la volée et recharge la page à chaque modification (HMR).
- **Comment Laravel trouve Vite** : Vite écrit le fichier `public/hot`. Tant que ce fichier existe, la directive `@vite` de Blade charge les assets depuis `http://localhost:5173` (port `VITE_PORT`) au lieu de `public/build`.
- **Surveillance des fichiers** : `vite.config.js` active le polling. Sans lui, les modifications faites sur la machine ne sont pas toujours détectées à travers le volume Docker. Le dossier `vendor/` est ignoré pour économiser le CPU.

#### `postgres` — base de données

- **Image** : `postgis/postgis:16-3.5`, utilisée telle quelle : c'est PostgreSQL 16 avec l'extension géographique PostGIS (zones, distances, traces). L'extension est activée par la migration `enable_postgis_extension`, y compris dans la base `testing`.
- **Premier démarrage** : l'image crée automatiquement la base `DB_DATABASE` et l'utilisateur `DB_USERNAME`/`DB_PASSWORD`, qui en est propriétaire. Elle exécute aussi `docker/postgres/create-testing-database.sh`, qui crée la base `testing` utilisée par `php artisan test` (voir `phpunit.xml`).
- **Données** : stockées dans le volume nommé `postgres-data`. Elles survivent à `make down` et ne sont supprimées que par `make clean`. Pour que le script d'initialisation s'exécute de nouveau, il faut supprimer ce volume.
- **Healthcheck** : `pg_isready`. C'est lui qui détermine quand les conteneurs PHP peuvent démarrer.
- **Accès** : port `FORWARD_DB_PORT` (5435 par défaut, pour ne pas entrer en conflit avec un PostgreSQL installé sur la machine) exposé pour s'y connecter avec un client graphique (DBeaver, pgAdmin, TablePlus…).

### Services retirés

Trois services ont été retirés parce que rien ne les utilisait encore. Ils reviendront quand une phase du plan (`docs/PLAN_REFONTE.md`) en aura besoin.

| Service retiré | Remplacé par | Quand le remettre |
|---|---|---|
| `redis` | sessions et cache dans PostgreSQL (`SESSION_DRIVER=database`, `CACHE_STORE=database`) | seulement si la charge l'exige |
| `queue` | exécution immédiate (`QUEUE_CONNECTION=sync`) | dès qu'un traitement doit tourner en arrière-plan (PDF de clôture de la main courante, envoi de messages, imports des connecteurs) : le remettre avec `QUEUE_CONNECTION=database`, sans Redis |
| `mailpit` | e-mails écrits dans les journaux (`MAIL_MAILER=log`) | à l'arrivée du canal e-mail (module Communications), comme boîte de réception de test |

### Fichiers Docker

```
compose.yaml                         # définition des services
docker/php/Dockerfile                # image PHP (cible "dev" avec Xdebug)
docker/php/php.ini, xdebug.ini
docker/nginx/default.conf
docker/postgres/create-testing-database.sh
Makefile
```

## Configuration

Les ports, l'UID/GID et les identifiants se règlent dans `.env` (voir `.env.example`). En cas de conflit de port, modifiez par ex. `APP_PORT` ou `FORWARD_DB_PORT`.

- **Xdebug** : `XDEBUG_MODE=debug` dans `.env`, puis `docker compose up -d`. Port 9003, mapping de chemin `/var/www/html` → racine du projet.
- **MTU** : le réseau Docker utilise un MTU de 1400 (`DOCKER_MTU`) pour éviter les blocages TLS de npm/composer sur certains réseaux (Wi-Fi, VPN, box). Vous pouvez remettre 1500 si votre réseau le permet.

## Architecture

L'application est découpée en **modules** : chaque fonctionnalité vit dans son propre dossier sous `modules/`, avec ses routes, contrôleurs, vues, traductions et tests. Le **domaine** (modèles, énumérations, règles métier) est partagé dans `app/` : le cahier des charges demande un référentiel commun, et la fiche navire rassemble les données de tous les modules.

```
app/
├── Enums/                               # énumérations métier (statuts, types…), libellés traduits ; Concerns/HasTranslatedLabel
├── Http/
│   ├── Controllers/LocaleController.php # changement de langue
│   └── Middleware/                      # langue (SetLocale), désactivation des comptes (EnsureUserIsActive)
├── Models/                              # domaine partagé : navires, licences, positions, observations, connecteurs, réglages…
│   └── Concerns/Audited.php             # historisation automatique des modifications (journal d'audit)
├── Queries/                             # requêtes métier réutilisées par plusieurs modules (SilentBeacons, ZoneSituation)
├── Providers/AppServiceProvider.php     # services globaux (navigation, langues, réglages)
└── Support/
    ├── Authorization/                   # permissions et rôles (Permission, Role)
    ├── Localization/                    # langues et pays
    ├── Modules/ModuleServiceProvider.php    # chargement d'un module : routes, vues, traductions, menu, commandes, planification
    ├── Navigation/                          # registre du menu (groupes, permissions, élément actif)
    ├── Notifications/                       # Alert (notification traduite), AlertLevel, Notifier (envoi par permission, anti-répétition)
    ├── Presentation/                        # badges, couleurs du thème, liens vers la fiche navire
    └── Settings/                            # réglages modifiables par l'administrateur (Settings, SettingDefinition, SettingType)

config/surveillance.php                  # seuils métier, alertes et notifications : valeurs par défaut (les réglages les surchargent)
database/                                # migrations, factories, seeders (référentiel, données de démo, activité des connecteurs)
docs/                                    # GUIDE, TECHNICAL, DESIGN, PLAN_REFONTE, ACCEPTATION, QUESTIONS_GILDAS, GLOSSAIRE, manuel, décisions, hypothèses, schéma, référence générée
lang/                                    # traductions partagées (ui.php, enums.php) et messages Laravel
modules/                                 # une fonctionnalité = un module (voir ci-dessous)

resources/
├── views/
│   ├── components/layouts/              # layouts de page : app (barre du haut gs-topbar, menu gs-sidebar et contenu dans gs-shell) et auth
│   ├── components/ui/                   # composants réutilisables (voir ci-dessous)
│   ├── layouts/partials/                # morceaux du layout : head, header (barre du haut), sidebar, flash, locale-menu, locale-links, preloader, confirm-modal, remote-modal, popup, guide
│   └── errors/                          # pages d'erreur 403, 404, 419, 429, 500, 503
├── js/
│   ├── app.js                           # point d'entrée JS
│   ├── components/                      # carte (Leaflet) et graphiques (Chart.js), chargés à la demande
│   ├── layout/                          # un fichier par comportement : notifications (balise et toasts), plein écran, actions des cartes, envoi automatique des filtres, infobulles, préchargeur
│   └── vendor/                          # jQuery, Bootstrap, pcoded (Adminty)
├── scss/
│   ├── app.scss                         # ordre d'import : Adminty, pages, ajustements, puis le thème
│   ├── adminty/                         # template Adminty, jamais modifié
│   ├── pages/_timeline.scss             # chronologie de la fiche navire
│   ├── _layout.scss, _components.scss   # ajustements propres à l'application
│   └── theme/                           # thème GESICA : _tokens, _mixins, _base, _topbar, _shell, _page-header, _cards, _tables, _buttons, _forms, _labels, _tabs, _notifications, _comments (voir docs/DESIGN.md)
├── fonts/                               # polices d'icônes feather, icofont, themify
└── images/                              # images du thème

tests/                                   # tests du socle ; chaque module a ses propres tests
```

### Anatomie d'un module

```
modules/Dashboard/
├── Http/Controllers/DashboardController.php
├── Providers/DashboardServiceProvider.php
├── Tests/Feature/DashboardTest.php
├── lang/{en,fr}/dashboard.php           # traductions, appelées avec __('dashboard::dashboard.title')
├── resources/views/index.blade.php      # vues, appelées avec view('dashboard::index')
└── routes/web.php                       # routes, chargées avec le middleware « web »
```

Chaque module a un service provider qui hérite de `App\Support\Modules\ModuleServiceProvider`. Le parent charge automatiquement les éléments du module selon ces conventions :

- **Routes** : le fichier `routes/web.php`, avec le middleware `web`.
- **Vues** : le dossier `resources/views`, sous un namespace qui est le nom du module en kebab-case (`dashboard::`, `auth::`…).
- **Traductions** : le dossier `lang/`, sous le même namespace que les vues.
- **Menu** : le module ajoute ses entrées au menu latéral dans la méthode `navigation()`, avec une clé de traduction comme libellé.
- **Commandes et planification** : un module qui a des tâches planifiées surcharge `consoleCommands()` (liste de commandes Artisan) et `schedule()` (fréquence). Exemples : `modules/Tracking/Providers/TrackingServiceProvider.php` et `modules/Licenses/Providers/LicensesServiceProvider.php`.

Selon ses besoins, un module ajoute d'autres dossiers : `Http/Requests/` (validation), `Console/` (commandes planifiées), `Notifications/` (alertes), `Support/` (classes propres au module). Les modules `Administration`, `Tracking` et `Licenses` montrent chacun une variante.

Le layout ne connaît donc aucun module : il se contente d'afficher ce que les modules ont déclaré.

```php
class DashboardServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $navigation->add('dashboard::dashboard.title', 'dashboard', 'icon-home', NavigationGroup::Overview, order: 0, permission: Permission::DashboardView->value);
    }
}
```

### Créer un module

1. Créer le dossier `modules/<Nom>/` en reprenant la structure ci-dessus. Le namespace des classes est `Modules\<Nom>\…` (autoload PSR-4 déjà configuré dans `composer.json`).
2. Créer `Providers/<Nom>ServiceProvider.php`, qui hérite de `ModuleServiceProvider`.
3. Enregistrer ce provider dans `bootstrap/providers.php`.
4. Déclarer ses permissions dans `Permission`, les attribuer aux rôles dans `Role`, puis lancer `make fresh`.
5. Créer les traductions dans `modules/<Nom>/lang/en/<fichier>.php` et `modules/<Nom>/lang/fr/<fichier>.php`, avec les mêmes clés.
6. Construire les pages avec le layout et les composants partagés :

```blade
<x-layouts.app :title="__('customers::customers.title')">
    <x-ui.page-header :title="__('customers::customers.title')" />

    <div class="page-body">
        <x-ui.card :title="__('customers::customers.list')">…</x-ui.card>
    </div>
</x-layouts.app>
```

Les tests placés dans `modules/*/Tests`, `tests/Feature` et `tests/Unit` sont lancés par `make test`.

### Conventions

- **Langue du code** : tout le code est en anglais (noms, clés, configuration).
- **Aucun texte en dur** : tout texte affiché passe par une clé de traduction (voir [Traductions](#traductions)).
- **Commentaires** : le code doit se comprendre sans commentaires, grâce à des noms explicites et des fonctions courtes.
- **Dépendances entre modules** : un module ne dépend que du socle `app/`, jamais d'un autre module. Ce qui doit être partagé remonte dans `app/` (code PHP) ou dans `resources/views/components/ui/` (composants d'interface). Les liens vers un autre module passent par des helpers qui vérifient que la route existe (`VesselLink`, recherche transversale). Seule exception assumée : le layout utilise la route `logout` du module `Auth`.
- **Recherche textuelle** : utiliser `whereLike()` / `orWhereLike()`, qui génèrent un `ILIKE` insensible à la casse sous PostgreSQL (un `where(..., 'like', ...)` y serait sensible à la casse).
- **Règles métier paramétrables** : seuils et listes dans `config/surveillance.php`, jamais en dur dans le code. Les valeurs que l'administrateur doit pouvoir modifier sont déclarées dans `App\Support\Settings\Settings` et surchargent ce fichier au démarrage.

## Domaine et modules

### Domaine

| Modèle | Rôle | Exigences |
|---|---|---|
| `Vessel` | navire (pêche ou vedette), identifiant unique, pavillon, statut | A1, A9, RG01-02 |
| `License` | licence annuelle ; statut calculé (valide, à échéance, expirée, suspendue) | A7, RG07 |
| `TrackingDevice`, `Position` | balises VMS/AIS (historique des remplacements) et positions datées | A2, RG03-06 |
| `Port`, `Zone` | ports et zones (GeoJSON) : zones portuaires, mer territoriale, ZEE, zones réglementées | A4 |
| `Movement` | entrées et sorties des ports | A5 |
| `Observation` | main courante : constat, niveau, statut, suite à donner, équipe | A6, RG08, RG10 |
| `Inspection`, `Infraction`, `InfractionType` | contrôles et manquements avec leur cycle de vie | A10, F-006, F-007 |
| `Species`, `CatchDeclaration` | captures et débarquements | F-008 |
| `Mission` | missions et opérations (navires, agents, zone) | F-005 |
| `Team`, `User` | équipes de surveillance et comptes (activables) | S-011 |
| `DataSource`, `DataSourceSync`, `DataSourceError`, `DataSourceRejection` | état des connecteurs, historique des synchronisations, erreurs et enregistrements rejetés (les commentaires de suivi sont des `Comment` génériques) | F-012 |
| `Setting` | réglages modifiables par l'administrateur (notifications, seuils d'alerte) | — |

Les notifications utilisent la table `notifications` de Laravel (modèle fourni par le framework, sans classe dans `app/Models`). Les modifications des données métier sont historisées automatiquement dans le journal d'audit (`spatie/laravel-activitylog`, trait `App\Models\Concerns\Audited`).

### Modules

| Module | Pages | Permission |
|---|---|---|
| `Auth` | connexion, déconnexion, journalisation des connexions | — |
| `Dashboard` | indicateurs, situation par zone, infractions, inspections, fraîcheur des sources | `dashboard.view` |
| `Tracking` | carte de situation, situation par zone à un instant donné, état des balises ; alerte planifiée des balises muettes | `tracking.view` |
| `Logbook` | main courante, éléments en suspens, suivi et clôture | `logbook.*` |
| `Movements` | entrées et sorties | `movements.*` |
| `Vessels` | liste, fiche 360°, création et modification, rapprochement des sources et file des doublons | `vessels.*` |
| `Registry` | référentiels (pays, organismes, personnes, codes FAO, ports, listes externes, annuaire, agents) et priorité des sources | `registry.view`, `registry.manage` |
| `Licenses` | liste et échéances ; alerte planifiée des licences à échéance | `licenses.view` |
| `Inspections` | liste et détail | `inspections.view` |
| `Infractions` | dossier d'infraction (preuves, scellés, saisies, mesures conservatoires décidées par le Directeur, PV figé, suites, décisions, paiements, recours, récidive), nomenclature éditable par l'Admin | `infractions.*` |
| `Catches` | déclarations et totaux par espèce | `catches.view` |
| `Missions` | programmation, autorisation par type (V21), équipe et moyens, briefing, trace GPX PostGIS, unité rencontrée à trois niveaux, bilan calculé | `missions.*` |
| `Witnesses` | signalements de témoins, exploitabilité, enquête, classement ou transmission, diffusion de la liste des navires autorisés | `witnesses.*` |
| `Search` | recherche transversale (en-tête) | selon les sources |
| `Notifications` | page des notifications de l'utilisateur et flux JSON lu par la balise de l'en-tête | utilisateur connecté |
| `Communications` | messages (modèles, envoi, accusé), notes par boutons, règles d'envoi automatique (plafond par jour, condition d'arrêt), canaux et consentements WhatsApp ; commande planifiée `communications:dispatch` | `communications.*` |
| `Administration` | utilisateurs, rôles et permissions, paramètres, connecteurs (liste et détail : erreurs, rejets, synchronisations, commentaires), journal d'audit | `users.manage`, `roles.manage`, `settings.manage`, `connectors.view`, `connectors.manage`, `audit.view` |

### Droits d'accès

Les permissions (`<module>.<action>`) sont les **actions** du catalogue : elles sont déclarées dans l'enum `App\Support\Authorization\Permission` et enregistrées en base (`action_catalog`) par `php artisan permissions:sync` (ou `RolesAndPermissionsSeeder`, `make fresh`). **À lancer à chaque déploiement** après `migrate`.

| Rôle | Profil |
|---|---|
| `admin` | administration technique : comptes, rôles, paramètres, connecteurs, audit ; aucune décision métier |
| `director` | voit tout et prend les décisions finales (refus de port, suspension de licence) |
| `team_lead` | chef d'équipe, équivalent de l'ancien « responsable du CSP » : valide la saisie des agents, pilote l'équipe de quart |
| `agent` | opérateur CSP ou inspecteur : suivi, main courante, mouvements, consultation |

Les descriptions de ces rôles sont affichées dans la page des rôles ; pour un rôle personnalisé, elle est saisie par l'administrateur.

**Comment une permission est accordée** (`App\Support\Authorization\PermissionResolver`, branché sur les `Gate`, donc `can()`, `->can()` et `@can` ne changent pas) :

- **Rôles** : un rôle peut avoir un rôle parent dont il hérite des permissions. Les rôles système forment la chaîne `director` > `team_lead` > `agent` ; un rôle personnalisé peut hériter de n'importe quel rôle.
- **Groupes** : un utilisateur peut appartenir à des groupes (`permission_groups`) qui lui ajoutent des permissions en plus de son rôle. Les cinq groupes de la spécification existent (gestion FNP et licences, instruction des infractions, avis surveillance, gestion des envois automatiques, déclencher l'agent IA) ; l'administrateur en crée d'autres.
- **Portée** : une permission accordée (`permission_grants`) peut être limitée à une **zone** (`Gate::allows($permission, $zone)`) ou à une **période** (`valid_from`, `valid_to`). Le résolveur les gère ; les écrans de l'administration permettent d'accorder des permissions limitées depuis la fiche d'un rôle ou d'un groupe.
- Le résolveur mémorise les droits d'un utilisateur pour la durée d'une requête ; ils sont recalculés après toute modification faite par `GrantService`, par un changement de groupe ou de rôle de l'utilisateur.
- Spatie Permission a été retiré : les rôles (`roles`, `role_user`) sont gérés par `App\Models\RoleRecord` et le trait `App\Models\Concerns\HasRoles` (`assignRole`, `syncRoles`, `hasRole`, `getRoleNames`, portée `User::role()`). Seul `spatie/laravel-activitylog` reste, pour l'audit.

L'administrateur gère rôles, groupes et attributions depuis *Administration › Rôles et permissions* et *Groupes de permissions* : il peut modifier les permissions des rôles système, sauf celles du rôle `admin` (jeu fixe, pour éviter de se couper l'accès).

Pour protéger une route : `->can(Permission::VesselsView->value)`. Pour afficher un élément de menu : passer `permission:` à `$navigation->add()`.

### Composants d'interface

| Composant | Usage |
|---|---|
| `<x-ui.page-header>` | titre, sous-titre et fil d'Ariane |
| `<x-ui.card>` | carte Adminty, avec slot `actions` |
| `<x-ui.stat-card>` | indicateur chiffré coloré |
| `<x-ui.filters>`, `<x-ui.filter-input>`, `<x-ui.filter-select>` | carte de filtres (GET), encore utilisée par les pages sans barre d'outils |
| `<x-ui.table>`, `<x-ui.empty-row>` | tableau avec pagination ; le slot `filters` ajoute la ligne de filtres sous l'en-tête |
| `<x-ui.table-toolbar>`, `<x-ui.toolbar-input>`, `<x-ui.toolbar-select>`, `<x-ui.column-filter>` | barre d'outils d'un tableau (nombre de lignes, recherche, exports, réinitialisation) et filtres par colonne ; le nombre de lignes se lit avec `App\Support\Tables\PerPage` |
| `<x-ui.tabs>`, `<x-ui.tab-pane>` | onglets |
| `<x-ui.detail-list>`, `<x-ui.detail>` | liste libellé / valeur |
| `<x-ui.status-badge>` | badge d'un enum métier (couleur et libellé traduit) |
| `<x-ui.vessel-link>` | lien vers la fiche navire si l'utilisateur y a accès |
| `<x-ui.map>` | carte Leaflet (marqueurs et zones GeoJSON) |
| `<x-ui.chart>` | graphique Chart.js (configuration passée telle quelle) |
| `<x-ui.audit-history>` | historique des modifications d'un enregistrement |
| `<x-ui.form.*>` | champs de formulaire (`input`, `select`, `textarea`, `checkbox`, `actions`) |

## Traductions

L'interface est disponible en français et en anglais. Aucun texte n'est écrit en dur dans le code : chaque texte affiché correspond à une clé, définie dans un fichier par langue.

| Où                                  | Fichiers                              | Appel                                   |
|-------------------------------------|---------------------------------------|-----------------------------------------|
| Textes partagés (layout, composants) | `lang/{en,fr}/ui.php`                 | `__('ui.log_out')`                      |
| Libellés des énumérations métier    | `lang/{en,fr}/enums.php`              | `__($status->label())`                  |
| Textes d'un module                  | `modules/<Nom>/lang/{en,fr}/<fichier>.php` | `__('<module>::<fichier>.<clé>')`  |
| Messages Laravel (validation, auth…) | `lang/{en,fr}/*.php`, `lang/{en,fr}.json` (laravel-lang) | automatique |

```php
// modules/Dashboard/lang/fr/dashboard.php
return [
    'title' => 'Tableau de bord',
    'stats' => ['users' => 'Utilisateurs'],
];
```

```blade
<h4>{{ __('dashboard::dashboard.title') }}</h4>
```

**Choix de la langue**, dans cet ordre :

1. la langue choisie par l'utilisateur (cookie `locale`, valable un an) ;
2. la langue du navigateur, si elle est supportée (`fr-CI` → `fr`) ;
3. la langue par défaut (`APP_LOCALE`).

L'utilisateur change de langue avec le sélecteur de l'en-tête, ou avec les liens sous le formulaire de connexion. Le middleware `App\Http\Middleware\SetLocale` applique la langue choisie à Laravel, aux dates (Carbon) et aux nombres (`Number`).

**Ajouter une langue** :

1. L'ajouter à `APP_SUPPORTED_LOCALES` dans `.env` (par ex. `fr,en,es`).
2. Lancer `make artisan c="lang:add es"`, qui installe les messages Laravel traduits.
3. Créer `lang/es/ui.php` et `modules/*/lang/es/*.php`, avec les mêmes clés qu'en français et en anglais (dont `ui.language`, le nom de la langue affiché dans le sélecteur).

Le test `TranslationCompletenessTest` compare tous les dossiers de langue : il échoue si une clé existe dans une langue mais manque dans une autre.
