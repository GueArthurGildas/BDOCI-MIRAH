# BDOCI-MIRAH

Système d'information du Centre de Surveillance des Pêches (CSP) : référentiel des navires, suivi des positions, main courante, contrôles et pilotage — Laravel 13, thème Adminty.

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

Comptes de démo (après `make fresh`, mot de passe `password`) : `admin@example.com`, `supervisor@example.com`, `agent@example.com`, `inspector@example.com`

## Commandes

```bash
make up / make down     # démarrer / arrêter
make test               # lancer les tests
make shell              # shell PHP
make artisan c="…"      # commande artisan
make help               # toutes les commandes
```

## Organisation

- `modules/` : une fonctionnalité = un module (navires, suivi, main courante, contrôles, administration…)
- `app/` : socle commun et domaine métier (modèles, droits d'accès)
- `resources/` : layout, composants d'interface, thème

Détails (Docker, configuration, architecture, création d'un module) : [docs/TECHNICAL.md](docs/TECHNICAL.md)
