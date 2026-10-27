# Guide du nouvel arrivant

Ce guide vous rend autonome sur le projet en une journée : installer, comprendre le vocabulaire, savoir où lire, puis réaliser les modifications courantes en suivant des recettes qui s'appuient sur du code réel.

Il distingue toujours **ce qui existe** de **ce qui est la cible** décrite dans `docs/PLAN_REFONTE.md`. Quand une recette dépend d'un élément cible (action nommée, journal d'événements), elle renvoie au plan au lieu d'inventer une marche à suivre.

| Document lié | Rôle |
|---|---|
| `docs/TECHNICAL.md` | référence technique : Docker, architecture, modules, droits, traductions |
| `docs/DESIGN.md` | thème GESICA, règles de design, pièges avec Adminty |
| `docs/PLAN_REFONTE.md` | trajectoire de refonte par phases et décisions D1 à D9 |
| `docs/ACCEPTATION.md` | matrice cas d'usage et règles de gestion, avec les tests qui les couvrent |
| `docs/GLOSSAIRE.md` | vocabulaire métier français et noms techniques anglais (en cours de rédaction) |
| `docs/decisions/` | décisions d'architecture (ADR), une par fichier (en cours de rédaction) |

---

## 1. Jour 1

### 1.1 Installer

Prérequis : Docker (Compose v2) et `make`. Rien d'autre.

```bash
git clone <url-du-depot> BDOCI-MIRAH
cd BDOCI-MIRAH
make setup      # construit l'image, installe Composer, génère la clé, démarre, migre
make fresh      # remplit la base : référentiels et données de démonstration
```

`make setup` crée les tables mais ne les remplit pas (`Makefile`, cible `setup`) : sans `make fresh`, aucun compte de démonstration n'existe. `make help` liste toutes les commandes.

L'application est sur http://localhost:8000. Le service `node` (Vite) tourne en même temps et recompile le SCSS et le JS à chaque modification. Si un style ne se met pas à jour, redémarrez-le : `docker restart bdoci-mirah-node-1`. Si une page affiche « 502 Bad Gateway » juste après avoir recréé le conteneur `app`, redémarrez `nginx` : `docker restart bdoci-mirah-nginx-1` (il garde en mémoire l'ancienne adresse de PHP).

### 1.2 Comptes de démonstration

Mot de passe commun : `password`.

| Compte | Rôle | Ce que vous voyez |
|---|---|---|
| `admin@example.com` | `admin` | uniquement l'administration technique : utilisateurs, rôles, paramètres, connecteurs, journal d'audit, plus le tableau de bord |
| `director@example.com` | `director` | tout le métier, plus l'état des connecteurs |
| `teamlead@example.com` | `team_lead` (ancien superviseur) | même périmètre que le directeur aujourd'hui |
| `agent@example.com` | `agent` | suivi, main courante, mouvements, consultation |

Ces comptes sont créés dans `database/seeders/DatabaseSeeder.php`. Les rôles sont expliqués dans `docs/TECHNICAL.md` (section Droits d'accès).

### 1.3 Première visite

1. Ouvrez http://localhost:8000 : l'adresse `/` redirige vers `/dashboard` (`routes/web.php`) ; vous arrivez sur la page de connexion.
2. Connectez-vous en `agent@example.com`. Le tableau de bord (`modules/Dashboard`) montre quatre indicateurs, des graphiques et l'état des sources de données.
3. Ouvrez le menu **Référentiel > Navires**, puis la fiche d'un navire (`modules/Vessels/resources/views/show.blade.php`) : c'est l'écran le plus riche, il rassemble licences, balises, mouvements, observations, inspections, infractions, captures et missions.
4. Reconnectez-vous en `admin@example.com` et comparez le menu : c'est la façon la plus rapide de comprendre les rôles.
5. Cliquez la cloche en haut à droite : c'est la page des notifications (`modules/Notifications`).
6. Lancez les tests avec `make test` pour vérifier que votre poste est sain.

---

## 2. Modèle mental

Le vocabulaire de la spécification (`database/docs/`) et celui du code ne se recouvrent pas encore entièrement. Ce tableau dit pour chaque notion si elle existe et où la trouver.

| Notion | Aujourd'hui | Cible | Où lire |
|---|---|---|---|
| **Module** | existe : un dossier `modules/<Nom>` avec ses routes, contrôleurs, vues, traductions et tests ; il ne dépend que du socle `app/` | ajout d'un registre en base (`module_registry`) | `app/Support/Modules/ModuleServiceProvider.php`, `docs/TECHNICAL.md` |
| **Socle** | existe : `app/Models`, `app/Enums`, `app/Queries`, `app/Support` partagés par tous les modules | noyau de la phase 1 | `docs/TECHNICAL.md` |
| **Navire** | existe sous forme d'une table `vessels` ; propriétaire et pavillon sont du texte | fiche unique avec identifiants multiples, sources et rapprochement (phase 2) | `app/Models/Vessel.php`, `docs/PLAN_REFONTE.md` |
| **Droits** | existe : permissions déclarées dans `app/Support/Authorization/Permission.php`, quatre rôles système dans `Role.php`, rôles personnalisés créés depuis l'administration | catalogue d'actions par module, groupes, portée zone et période (phase 1) | `docs/TECHNICAL.md` |
| **Alerte** | existe : notifications en base, balise clignotante et notifications flottantes, deux alertes planifiées | alertes de la spécification avec règles et traitement (phases 3b et 5) | recette 4.2 |
| **Réglage** | existe : valeurs modifiables par l'administrateur, par-dessus `config/surveillance.php` | généralisation en seuils par module | recette 4.3 |
| **Audit** | existe : historisation automatique par `spatie/laravel-activitylog`, page *Journal d'audit* | journal immuable, y compris pour l'Admin (phase 1) | `app/Models/Concerns/Audited.php` |
| **Chronologie du navire** | reconstituée à l'affichage par `modules/Vessels/Support/VesselTimeline.php` | **événement du navire** (`vessel_events`) écrit à chaque fait, avec **état courant** (`vessel_current_states`) | `docs/PLAN_REFONTE.md`, section 3 |
| **Action nommée** | n'existe pas : les contrôleurs écrivent directement | classe `Action` qui vérifie la permission, écrit, publie l'événement, met à jour l'état courant et journalise | `docs/PLAN_REFONTE.md`, section 3 |

Règles à connaître dès le premier jour :

- **Code en anglais**, sans commentaires superflus : des noms explicites à la place.
- **Aucun texte en dur** : tout texte affiché est une clé de traduction (recette 4.6).
- **Un module ne dépend jamais d'un autre module** ; ce qui est partagé monte dans `app/` ou dans `resources/views/components/ui/`.
- **Pas de couleur ni de rayon écrits à la main** : le thème vit dans `resources/scss/theme/` (voir `docs/DESIGN.md`).

---

## 3. Ordre de lecture

1. `README.md` : installation et commandes.
2. Ce guide, jusqu'à la section 2.
3. `docs/TECHNICAL.md` : sections Architecture, Anatomie d'un module, Droits d'accès, Traductions.
4. `docs/DESIGN.md` : avant de toucher aux vues ou au SCSS.
5. `docs/PLAN_REFONTE.md` : sections 1 à 4, pour savoir où va le projet.
6. `docs/ACCEPTATION.md` : quels cas d'usage sont couverts par quels tests.
7. `docs/GLOSSAIRE.md` : quand un terme métier vous est inconnu.
8. `docs/decisions/` : pourquoi une décision a été prise.
9. Les documents de cadrage : `docs/CADRAGE.pdf` et `docs/CAHIER_DES_CHARGES.pdf`, puis `docs/MODELE_DONNEES.md` et `docs/CAS_UTILISATION.md`. Ces deux derniers portent un avertissement : ils décrivent encore les anciens rôles.
10. La spécification reçue du métier, dans `database/docs/` (spécification de conception, MCD, schéma SQL v1).

---

## 4. Recettes

Chaque recette cite des fichiers qui existent. Quand deux modules illustrent la même chose, le premier cité est l'exemple à copier.

### 4.1 Ajouter une page dans un module existant

Exemple de référence : la page des licences, `modules/Licenses`.

1. **Route** dans `modules/Licenses/routes/web.php` : `Route::get(...)->middleware('auth')->can(Permission::LicensesView->value)->name('licenses.index')`. Le fichier est chargé automatiquement avec le middleware `web`.
2. **Contrôleur** : `modules/Licenses/Http/Controllers/LicenseController.php` (valide les filtres avec `$request->validate(...)`, interroge les modèles de `app/Models`, retourne `view('licenses::index', [...])`).
3. **Vue** : `modules/Licenses/resources/views/index.blade.php`. Elle commence par `<x-layouts.app :title="...">` et `<x-ui.page-header ...>`, puis utilise les composants de `resources/views/components/ui/` (`x-ui.card`, `x-ui.table`, `x-ui.filters`, `x-ui.stat-card`).
4. **Entrée de menu** : la méthode `navigation()` de `modules/Licenses/Providers/LicensesServiceProvider.php` appelle `$navigation->add(libellé, route, icône, NavigationGroup::..., order:, permission:)`. L'icône du bandeau de page est reprise de cette entrée, et l'entrée est active pour la route et pour `<route sans .index>.*`.
5. **Traductions** : `modules/Licenses/lang/en/licenses.php` et `modules/Licenses/lang/fr/licenses.php`, mêmes clés (recette 4.6).
6. **Test** : `modules/Licenses/Tests/Feature/LicensePagesTest.php` (recette 4.7).

Pour créer un **nouveau module**, suivez *Créer un module* dans `docs/TECHNICAL.md` (enregistrement dans `bootstrap/providers.php`).

### 4.2 Ajouter une alerte (notification planifiée)

Le mécanisme existe de bout en bout. Exemples : `modules/Tracking` (balises muettes, toutes les 15 minutes) et `modules/Licenses` (licences à échéance, chaque jour à 07:00).

1. **Classe d'alerte** qui hérite de `App\Support\Notifications\Alert` (`app/Support/Notifications/Alert.php`) : voir `modules/Tracking/Notifications/BeaconsSilentAlert.php`. On y définit `key()` (clé unique qui sert à l'anti-répétition), `level()` (`AlertLevel`), `icon()`, `translationKey()`, `params()` et `target()` (route et paramètres où mène un clic). Le message n'est pas stocké en texte : on stocke une clé et des paramètres, traduits à l'affichage dans la langue de chaque lecteur.
2. **Traductions** `title` et `message` (avec pluriel sur `count`) dans `modules/Tracking/lang/en/alerts.php` et `modules/Tracking/lang/fr/alerts.php`.
3. **Commande Artisan** qui calcule la situation puis appelle `Notifier::notifyPermissionHolders($permission, $alert, $cooldownHours)` (`app/Support/Notifications/Notifier.php`) : voir `modules/Tracking/Console/NotifySilentBeacons.php`. Le `Notifier` ne prévient que les utilisateurs actifs qui détiennent la permission, et n'envoie pas deux fois la même alerte pendant le délai.
4. **Planification** : dans le service provider du module, surcharger `schedule()` et `consoleCommands()`, comme dans `modules/Tracking/Providers/TrackingServiceProvider.php`.
5. **Valeurs par défaut** dans `config/surveillance.php`, clé `alerts.<nom>.enabled` et `alerts.<nom>.cooldown_hours`, puis rendez-les modifiables avec la recette 4.3.
6. **Test** sur le modèle de `modules/Tracking/Tests/Feature/SilentBeaconsNotificationTest.php` : qui est prévenu, qui ne l'est pas, et le comportement après le délai.

Pour essayer à la main : `make artisan c="tracking:notify-silent-beacons"`. Le conteneur `scheduler` doit tourner pour que les tâches partent ; après une modification, `docker compose restart scheduler`.

Côté navigateur, la balise et les notifications flottantes sont déjà branchées : `modules/Notifications` (page et flux JSON) et `resources/js/layout/notifications.js`. Rien à faire pour qu'une nouvelle alerte s'y affiche.

### 4.3 Ajouter un réglage administrable

Les réglages se superposent à `config/surveillance.php` : le code continue de lire `config('surveillance.…')`, la valeur saisie par l'administrateur la remplace au démarrage.

1. **Valeur par défaut** dans `config/surveillance.php`.
2. **Déclaration** : ajouter un `new SettingDefinition(clé, clé de config, SettingType::Integer ou Boolean, groupe, min, max)` dans `Settings::definitions()` (`app/Support/Settings/Settings.php`). Le nom du champ de formulaire est la clé avec les points remplacés par des soulignés.
3. **Traductions** : libellé du champ sous `fields.<champ>` et titre du groupe sous `groups.<groupe>` dans `modules/Administration/lang/en/settings.php` et `modules/Administration/lang/fr/settings.php`.
4. **Lecture** : `config('surveillance.…')` dans le code. Rien d'autre à brancher (`App\Providers\AppServiceProvider` appelle `Settings::apply()` au démarrage).
5. **Test** : `modules/Administration/Tests/Feature/SettingsTest.php` montre comment vérifier l'enregistrement, la plage et la remise aux valeurs par défaut.

La page est *Administration > Paramètres* (`modules/Administration/Http/Controllers/SettingsController.php`), réservée à la permission `settings.manage`. N'y mettez jamais de valeur sensible.

### 4.4 Ajouter une permission et la donner à un rôle

1. **Déclarer** un cas dans `app/Support/Authorization/Permission.php`, au format `<module>.<action>`.
2. **Attribuer** la permission aux rôles concernés dans `Role::permissions()` (`app/Support/Authorization/Role.php`).
3. **Libellés** : `permissions.<module>.<action>` dans `modules/Administration/lang/en/administration.php` et `modules/Administration/lang/fr/administration.php`. Pour un nouveau module, ajouter aussi `permission_groups.<module>` dans les mêmes fichiers.
4. **Protéger** : `->can(Permission::X->value)` sur la route, et `permission:` dans l'entrée de menu (recette 4.1).
5. **Bases existantes** : `RolesAndPermissionsSeeder` (`database/seeders/RolesAndPermissionsSeeder.php`) crée la permission, mais ne synchronise les permissions d'un rôle que lorsque ce rôle est créé (et toujours pour `admin`). Sur une base existante, accordez la permission depuis *Administration > Rôles et permissions* ; en local, `make fresh` repart de zéro.
6. **Test** : `signInAs(Role::Agent)` (`tests/TestCase.php`) puis vérifier l'accès ou le refus ; `modules/Administration/Tests/Feature/RoleAdministrationTest.php` montre un rôle personnalisé qui accorde exactement ses permissions.

Un administrateur peut aussi créer des rôles et choisir leurs permissions depuis la page des rôles, sans toucher au code. Une permission, elle, n'existe que si le code la vérifie.

### 4.5 Ajouter une migration

1. `make artisan c="make:migration create_<table>_table"` crée le fichier dans `database/migrations/`, nommé `AAAA_MM_JJ_HHMMSS_...`. Exemple complet : `database/migrations/2026_10_27_120000_create_data_source_activity_tables.php`.
2. Noms de tables et de colonnes **en anglais** (décision D1 de `docs/PLAN_REFONTE.md`) ; la correspondance avec la spécification française se note dans `docs/GLOSSAIRE.md`.
3. Écrivez `up()` et `down()`. Pour une extension PostgreSQL, voir `database/migrations/2026_10_27_140000_enable_postgis_extension.php`.
4. **Modèle** dans `app/Models/` avec `#[Fillable([...])]` et les `casts()` ; ajouter le trait `App\Models\Concerns\Audited` pour historiser les modifications (`app/Models/Concerns/Audited.php`). Voir `app/Models/DataSourceRejection.php`, qui ne journalise que les mises à jour.
5. **Factory** dans `database/factories/` (par exemple `DataSourceRejectionFactory.php`) et, si la démonstration doit en contenir, un **seeder** appelé depuis `database/seeders/DatabaseSeeder.php` (par exemple `ConnectorActivitySeeder.php`).
6. Appliquer : `make migrate`. Pour tout reconstruire : `make fresh`.

Aucune base n'est déployée pour l'instant : on reconstruit plutôt que de migrer des données (hypothèse H1 du plan).

### 4.6 Ajouter une traduction

| Texte | Fichier |
|---|---|
| partagé (layout, composants) | `lang/en/ui.php` et `lang/fr/ui.php`, appelé `__('ui.cle')` |
| libellé d'un enum métier | `lang/en/enums.php` et `lang/fr/enums.php` ; la méthode `label()` vient de `app/Enums/Concerns/HasTranslatedLabel.php` |
| texte d'un module | `modules/<Nom>/lang/en/<fichier>.php` et `modules/<Nom>/lang/fr/<fichier>.php`, appelé `__('<module>::<fichier>.cle')` |

Les deux langues doivent avoir exactement les mêmes clés. Le test `tests/Unit/TranslationCompletenessTest.php` compare tous les dossiers de langue et échoue si une clé manque quelque part. Les pluriels s'écrivent `'{1} :count licence|[2,*] :count licences'` et se lisent avec `trans_choice` (voir `modules/Licenses/lang/fr/alerts.php`).

### 4.7 Écrire un test

1. Placez-le dans `modules/<Nom>/Tests/Feature/` (ou `tests/Feature/`, `tests/Unit/` pour le socle). `phpunit.xml` déclare ces trois suites.
2. Étendez `Tests\TestCase` (`tests/TestCase.php`) et ajoutez `use RefreshDatabase;`. La suite tourne sur la base `testing`, distincte de la base de développement.
3. Connectez-vous avec `$this->signInAs(Role::Agent)` : ce helper injecte les rôles et crée l'utilisateur. Pour un utilisateur sans rôle, `User::factory()->create()`.
4. Créez les données avec les factories (`License::factory()->expiringIn(10)->create()`).
5. Nommez la méthode `test_<comportement_en_phrase>` et vérifiez la réponse (`->assertOk()`, `->assertForbidden()`, `->assertSee(...)`).
6. Quand un test couvre un cas d'usage ou une règle de gestion, notez-le dans `docs/ACCEPTATION.md` (voir la convention `#[Covers]` de ce document).

Modèle à copier : `modules/Licenses/Tests/Feature/LicensePagesTest.php`.

### 4.8 Lancer les tests

```bash
make test                                              # toute la suite
make artisan c="test --filter=LicensePagesTest"        # une classe
make artisan c="test --filter=test_licenses_can_be_filtered_by_status"   # une méthode
```

Avant de commiter, lancez toute la suite : elle inclut la complétude des traductions et les règles de navigation.

---

## 5. Ce qui est à venir

| Besoin | Où est la cible |
|---|---|
| écrire un fait métier de façon traçable (action nommée) | `docs/PLAN_REFONTE.md`, section 3, phase 1 |
| faire apparaître un fait dans la chronologie du navire (événement) | idem |
| rattacher un cas d'usage à un test | `docs/ACCEPTATION.md` |
| rapprocher un navire, gérer ses identifiants | phase 2 |
| main courante, opérations, tableau du matin | phase 3a |

Tant que ces éléments n'existent pas, ne les simulez pas dans un module : signalez le besoin, il sera traité dans la phase correspondante.
