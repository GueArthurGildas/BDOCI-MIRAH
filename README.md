# BDOCI-MIRAH

Centralisation de bases de données — Laravel 13, thème Adminty.

## Prérequis

Docker et `make`. Rien d'autre à installer.

## Installation

```bash
git clone <url-du-depot> BDOCI-MIRAH
cd BDOCI-MIRAH
make setup
```

| Accès       | URL                     |
|-------------|-------------------------|
| Application | http://localhost:8000   |
| E-mails     | http://localhost:8025   |

Compte de démo (après `make fresh`) : `test@example.com` / `password`

## Commandes

```bash
make up / make down     # démarrer / arrêter
make test               # lancer les tests
make shell              # shell PHP
make artisan c="…"      # commande artisan
make help               # toutes les commandes
```

## Organisation

- `modules/` : une fonctionnalité = un module (routes, contrôleurs, vues, tests)
- `app/` : socle commun
- `resources/` : layout, composants d'interface, thème

Détails (Docker, configuration, architecture, création d'un module) : [docs/TECHNICAL.md](docs/TECHNICAL.md)
