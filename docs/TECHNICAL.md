# Documentation technique

- [Docker](#docker)
- [Configuration](#configuration)
- [Architecture](#architecture)
- [Traductions](#traductions)

## Docker

PHP, Composer, Node et MySQL tournent dans des conteneurs : seuls Docker (Compose v2) et `make` sont requis sur la machine.

| Service     | Rôle                                   | Accès                        |
|-------------|----------------------------------------|------------------------------|
| `app`       | PHP 8.4-FPM + Composer + Xdebug        | —                            |
| `nginx`     | Serveur web                            | http://localhost:8000        |
| `node`      | Node 24 + Vite (HMR)                   | http://localhost:5173        |
| `mysql`     | MySQL 8.4 (bases `laravel` et `testing`) | `localhost:3306`           |
| `redis`     | Cache, sessions, files d'attente       | `localhost:6379`             |
| `queue`     | Worker `queue:work`                    | —                            |
| `scheduler` | `schedule:work` (tâches planifiées)    | —                            |
| `mailpit`   | Capture des e-mails                    | http://localhost:8025        |
| `phpmyadmin`| Optionnel (`make pma`)                 | http://localhost:8080        |

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
make test                          # tests (base MySQL `testing`)
make db                            # client MySQL
make pma                           # phpMyAdmin (http://localhost:8080)
make logs
make clean                         # ⚠️ supprime aussi les volumes (données)
```

### How/Why?

Tous les services sont décrits dans `compose.yaml` et reliés au même réseau Docker privé `laravel`. Sur ce réseau, chaque conteneur joint les autres par leur nom de service : c'est pour ça que `.env` contient `DB_HOST=mysql`, `REDIS_HOST=redis` ou `MAIL_HOST=mailpit` au lieu de `127.0.0.1`. Le réseau utilise un MTU de 1400 (voir [Configuration](#configuration)).

Le code du projet est monté dans les conteneurs (`./` → `/var/www/html`) : une modification faite sur la machine est visible immédiatement dans les conteneurs, sans reconstruire d'image.

#### `app` — PHP-FPM

- **Image** : construite localement à partir de `docker/php/Dockerfile` (cible `dev`), taguée `bdoci-mirah/app:dev`. C'est la seule image du projet qui est construite ; toutes les autres sont téléchargées telles quelles depuis Docker Hub.
- **Étape `base`** :
  - part de l'image officielle `php:8.4-fpm-bookworm` ;
  - installe les outils système (`git`, `unzip`, `curl`, client MySQL) ;
  - installe les extensions PHP avec `install-php-extensions` : `pdo_mysql`, `bcmath`, `intl`, `zip`, `gd`, `exif`, `pcntl`, `opcache`, `redis` ;
  - copie Composer depuis l'image officielle `composer:2` ;
  - applique la configuration `docker/php/php.ini` (mémoire, taille des uploads, OPcache réglé pour le développement).
- **Utilisateur** : l'étape `base` crée aussi un utilisateur `laravel` qui a le même UID/GID que vous (variables `UID`/`GID` du `.env`), et PHP-FPM tourne sous cet utilisateur. Les fichiers créés par Laravel ou Composer vous appartiennent donc sur votre machine (pas de fichiers `root` dans `storage/` ou `vendor/`).
- **Étape `dev`** : ajoute Xdebug et sa configuration `docker/php/xdebug.ini`. Xdebug est inactif tant que `XDEBUG_MODE=off`.
- **Démarrage** : lance `php-fpm`, qui écoute sur le port 9000 du réseau interne. Ce port n'est pas exposé sur votre machine : seul `nginx` lui parle.
- **Dépendances** : attend que `mysql` et `redis` soient *healthy* avant de démarrer.

#### `queue` et `scheduler` — workers Laravel

- **Image** : la même que `app` (bloc commun `x-app` dans `compose.yaml`). Ils ont donc le même PHP, les mêmes extensions, le même code monté et les mêmes variables d'environnement.
- **Ce qui change** : seulement la commande de démarrage.
  - `queue` lance `php artisan queue:work --tries=3 --timeout=90` : il traite les jobs mis en file dans Redis (`QUEUE_CONNECTION=redis`).
  - `scheduler` lance `php artisan schedule:work` : il exécute chaque minute les tâches planifiées définies dans `routes/console.php`. Pas besoin de cron.
- **Après une modification du code d'un job** : `queue:work` garde le code en mémoire, il faut donc redémarrer le worker avec `docker compose restart queue`.

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

#### `mysql` — base de données

- **Image** : `mysql:8.4`, utilisée telle quelle.
- **Premier démarrage** : l'image crée automatiquement la base `DB_DATABASE`, l'utilisateur `DB_USERNAME`/`DB_PASSWORD` et le mot de passe root `DB_ROOT_PASSWORD`. Elle exécute aussi `docker/mysql/create-testing-database.sh`, qui crée la base `testing` utilisée par `php artisan test` (voir `phpunit.xml`).
- **Données** : stockées dans le volume nommé `mysql-data`. Elles survivent à `make down` et ne sont supprimées que par `make clean`. Pour que le script d'initialisation s'exécute de nouveau, il faut supprimer ce volume.
- **Healthcheck** : `mysqladmin ping`. C'est lui qui détermine quand les conteneurs PHP peuvent démarrer.
- **Accès** : port `FORWARD_DB_PORT` (3306 par défaut) exposé pour s'y connecter avec un client graphique (DBeaver, TablePlus…).

#### `redis` — cache, sessions et files d'attente

- **Image** : `redis:7-alpine`, utilisée telle quelle.
- **Rôle** : sert à la fois de cache (`CACHE_STORE=redis`), de stockage des sessions (`SESSION_DRIVER=redis`) et de file d'attente pour `queue` (`QUEUE_CONNECTION=redis`). PHP s'y connecte via l'extension `redis` (`REDIS_CLIENT=phpredis`).
- **Données** : persistées dans le volume `redis-data`.
- **Healthcheck** : `redis-cli ping`.
- **Accès** : port `FORWARD_REDIS_PORT` (6379 par défaut) exposé.

#### `mailpit` — capture des e-mails

- **Image** : `axllent/mailpit`, utilisée telle quelle.
- **Rôle** : faux serveur SMTP. Laravel lui envoie tous les e-mails (`MAIL_MAILER=smtp`, `MAIL_HOST=mailpit`, `MAIL_PORT=1025`) et aucun n'est réellement envoyé.
- **Accès** : les e-mails se consultent dans l'interface web sur le port `FORWARD_MAILPIT_DASHBOARD_PORT` (http://localhost:8025).

### Fichiers Docker

```
compose.yaml                         # définition des services
docker/php/Dockerfile                # image PHP (cible "dev" avec Xdebug)
docker/php/php.ini, xdebug.ini
docker/nginx/default.conf
docker/mysql/create-testing-database.sh
Makefile
```

## Configuration

Les ports, l'UID/GID et les identifiants se règlent dans `.env` (voir `.env.example`). En cas de conflit de port, modifiez par ex. `APP_PORT` ou `FORWARD_DB_PORT`.

- **Xdebug** : `XDEBUG_MODE=debug` dans `.env`, puis `docker compose up -d`. Port 9003, mapping de chemin `/var/www/html` → racine du projet.
- **MTU** : le réseau Docker utilise un MTU de 1400 (`DOCKER_MTU`) pour éviter les blocages TLS de npm/composer sur certains réseaux (Wi-Fi, VPN, box). Vous pouvez remettre 1500 si votre réseau le permet.

## Architecture

L'application est découpée en **modules** : chaque fonctionnalité métier vit dans son propre dossier sous `modules/`, avec ses routes, contrôleurs, vues, traductions et tests. Le dossier `app/` ne contient que le socle partagé par tous les modules.

```
app/
├── Http/
│   ├── Controllers/Controller.php       # contrôleur de base
│   ├── Controllers/LocaleController.php # changement de langue
│   └── Middleware/SetLocale.php         # application de la langue à chaque requête
├── Models/User.php                      # modèles partagés
├── Providers/AppServiceProvider.php     # services globaux
└── Support/
    ├── Localization/Locales.php         # langues supportées
    ├── Modules/ModuleServiceProvider.php    # chargement automatique d'un module
    └── Navigation/                          # registre du menu latéral

lang/                                    # traductions partagées (ui.php) et messages Laravel

modules/
├── Auth/                                # connexion / déconnexion
└── Dashboard/                           # tableau de bord

resources/
├── views/
│   ├── components/layouts/              # layouts de page : app (menu vertical) et auth
│   ├── components/ui/                   # composants réutilisables : page-header, card, stat-card
│   └── layouts/partials/                # morceaux du layout : head, header, sidebar, preloader, sélecteurs de langue
├── js/
│   ├── app.js                           # point d'entrée JS
│   ├── layout/                          # un fichier par comportement : menu, cartes, plein écran…
│   └── vendor/                          # jQuery, Bootstrap, pcoded (Adminty)
├── scss/
│   ├── app.scss                         # point d'entrée CSS : Bootstrap, icônes, thème
│   └── adminty/                         # SCSS du thème (couleurs dans partials/_variables.scss)
├── fonts/                               # polices d'icônes feather, icofont, themify
└── images/                              # images du thème

tests/
├── Feature/                             # tests du socle (langues…)
└── Unit/                                # tests unitaires (navigation, complétude des traductions)
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

Le layout ne connaît donc aucun module : il se contente d'afficher ce que les modules ont déclaré.

```php
class DashboardServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $navigation->add('dashboard::dashboard.title', 'dashboard', 'icon-home', order: 0);
    }
}
```

### Créer un module

1. Créer le dossier `modules/<Nom>/` en reprenant la structure ci-dessus. Le namespace des classes est `Modules\<Nom>\…` (autoload PSR-4 déjà configuré dans `composer.json`).
2. Créer `Providers/<Nom>ServiceProvider.php`, qui hérite de `ModuleServiceProvider`.
3. Enregistrer ce provider dans `bootstrap/providers.php`.
4. Créer les traductions dans `modules/<Nom>/lang/en/<fichier>.php` et `modules/<Nom>/lang/fr/<fichier>.php`, avec les mêmes clés.
5. Construire les pages avec le layout et les composants partagés :

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
- **Dépendances entre modules** : un module ne dépend que du socle `app/`, jamais d'un autre module. Ce qui doit être partagé remonte dans `app/` (code PHP) ou dans `resources/views/components/ui/` (composants d'interface). Seule exception : le layout utilise la route `logout` du module `Auth`.

## Traductions

L'interface est disponible en français et en anglais. Aucun texte n'est écrit en dur dans le code : chaque texte affiché correspond à une clé, définie dans un fichier par langue.

| Où                                  | Fichiers                              | Appel                                   |
|-------------------------------------|---------------------------------------|-----------------------------------------|
| Textes partagés (layout, composants) | `lang/{en,fr}/ui.php`                 | `__('ui.log_out')`                      |
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
