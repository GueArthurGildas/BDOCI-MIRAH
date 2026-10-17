# BDOCI-MIRAH
Project for faire centralization bd customers diomand

## Environnement de développement (Docker)

Seuls **Docker** (avec Docker Compose v2) et **make** sont nécessaires sur la machine : PHP, Composer, Node et MySQL tournent dans des conteneurs.

### Stack

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

Laravel 13 — application dans la racine du dépôt.

### Comment les conteneurs sont créés

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

### Installation sur un nouvel appareil

```bash
git clone <url-du-depot> BDOCI-MIRAH
cd BDOCI-MIRAH
make setup
```

`make setup` crée le `.env`, y règle votre UID/GID, construit les images, installe les dépendances Composer, génère la clé d'application, démarre les conteneurs, puis lance les migrations. Les dépendances npm sont installées automatiquement au démarrage du conteneur `node`.

### Commandes utiles

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
make logs
make clean                         # ⚠️ supprime aussi les volumes (données)
```

### Configuration

Les ports, l'UID/GID et les identifiants se règlent dans `.env` (voir `.env.example`). En cas de conflit de port, modifiez par ex. `APP_PORT` ou `FORWARD_DB_PORT`.

- **Xdebug** : `XDEBUG_MODE=debug` dans `.env`, puis `docker compose up -d`. Port 9003, mapping de chemin `/var/www/html` → racine du projet.
- **MTU** : le réseau Docker utilise un MTU de 1400 (`DOCKER_MTU`) pour éviter les blocages TLS de npm/composer sur certains réseaux (Wi-Fi, VPN, box). Vous pouvez remettre 1500 si votre réseau le permet.

### Front-end (template Adminty)

L'interface reprend le template **Adminty** (Bootstrap 5). Tout est compilé par Vite :

```
resources/scss/app.scss              # point d'entrée : Bootstrap, icônes, thème Adminty
resources/scss/adminty/              # SCSS du thème (variables dans partials/_variables.scss)
resources/js/app.js                  # point d'entrée : jQuery, Bootstrap, pcoded
resources/js/theme.js                # initialisation du layout (menu, cartes, plein écran…)
resources/fonts/                     # polices d'icônes feather, icofont, themify
resources/images/                    # images du thème (logo, fond de connexion…)
resources/views/components/layouts/  # layouts Blade : app (menu vertical) et auth
resources/views/components/          # composants : page-header, card, stat-card, nav-item
```

Pour reprendre un écran du template : copier le HTML de la page Adminty (`src/html/pages/<page>.html` dans le package du template) situé entre les includes de layout dans une vue `<x-layouts.app>`, puis remplacer les blocs répétés par les composants.

Les textes de l'interface sont écrits en anglais dans le code et traduits dans `lang/fr.json`.

Compte de démo (après `make fresh`) : `test@example.com` / `password`.

### Fichiers Docker

```
compose.yaml                         # définition des services
docker/php/Dockerfile                # image PHP (cible "dev" avec Xdebug)
docker/php/php.ini, xdebug.ini
docker/nginx/default.conf
docker/mysql/create-testing-database.sh
Makefile
```
