# Guide du nouvel arrivant

Ce guide vous rend autonome sur le projet en une journée : installer, comprendre le vocabulaire, savoir où lire, puis réaliser les modifications courantes en suivant des recettes qui s'appuient sur du code réel.

Il renvoie à `docs/PLAN_REFONTE.md` pour l'état des phases. Quand une recette dépend d'un élément cible (action nommée, journal d'événements), elle renvoie au plan au lieu d'inventer une marche à suivre.

| Document lié | Rôle |
|---|---|
| `docs/TECHNICAL.md` | référence technique : Docker, architecture, modules, droits, traductions |
| `docs/DESIGN.md` | thème GESICA, règles de design, pièges avec Adminty |
| `docs/PLAN_REFONTE.md` | suivi des phases (toutes livrées), décisions D1 à D9 et ce qui reste à valider |
| `docs/ACCEPTATION.md` | identifiants des cas d'usage et des règles de gestion, avec leur phase ; la couverture par les tests est dans `docs/reference/acceptance.md` |
| `docs/GLOSSAIRE.md` | vocabulaire métier français et noms techniques anglais |
| `docs/decisions/` | décisions d'architecture (ADR), une par fichier |
| `docs/manuel/` | manuel d'utilisation FR/EN pour le client (PDF) et son générateur |

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
| **Module** | existe : un dossier `modules/<Nom>` avec ses routes, contrôleurs, vues, traductions et tests ; il ne dépend que du socle `app/` (un test le vérifie) ; déclaré dans le registre en base (`module_registry`, commande `modules:sync`) | | `app/Support/Modules/ModuleServiceProvider.php`, `docs/TECHNICAL.md` |
| **Socle** | existe : `app/Models`, `app/Enums`, `app/Queries`, `app/Support` partagés par tous les modules | noyau de la phase 1 | `docs/TECHNICAL.md` |
| **Navire** | une fiche unique (`vessels`) avec identifiants multiples (`vessel_identifiers`), sources et provenance par champ, file de doublons à trancher par le chef d'équipe | | `app/Models/Vessel.php`, `modules/Vessels/README.md`, `modules/Registry/README.md` |
| **Droits** | existe : catalogue d'actions (`Permission`, `action_catalog`), rôles avec héritage, groupes, permissions limitables à une zone ou une période, résolveur branché sur les `Gate` (`app/Support/Authorization/PermissionResolver.php`) | écrans pour la portée zone et période, quand une phase l'exige | `docs/TECHNICAL.md` |
| **Alerte** | notifications en base, balise clignotante, notifications flottantes ; règles d'alerte paramétrables (`alert_rules`) pour le VMS, alertes de licence à 30/60/90 jours, alertes ZEE | | recette 4.2, `modules/Tracking/README.md` |
| **Réglage** | existe : valeurs modifiables par l'administrateur, par-dessus `config/surveillance.php` | généralisation en seuils par module | recette 4.3 |
| **Audit** | existe : historisation par `spatie/laravel-activitylog`, page *Journal d'audit* ; la table est inaltérable (triggers PostgreSQL), y compris pour l'Admin | table `audit_log` dédiée (plus tard) | `app/Models/Concerns/Audited.php` |
| **Chronologie du navire** | le **journal d'événements** `vessel_events` et l'**état courant** `vessel_current_states` (statut à feux, licence, dernière position…) sont écrits par les actions ; la vue 360° les lit via la vue `v_vessel_timeline` | | `app/Support/Events/`, `modules/Vessels/README.md` |
| **Action nommée** | une classe `Action` exécutée par `ActionRunner` : permission, transaction, événement du navire, état courant, audit ; toutes les écritures métier des phases livrées passent par là | | `app/README.md`, recette 4.9 |

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
4. `app/README.md` : le noyau partagé par les modules (actions, droits, notifications, réglages, documents).
5. `docs/DESIGN.md` : avant de toucher aux vues ou au SCSS.
6. `docs/PLAN_REFONTE.md` : l'état des phases et les décisions D1 à D9.
7. `docs/ACCEPTATION.md` et `docs/reference/` : quels cas d'usage sont couverts par quels tests, et le catalogue généré du code.
8. `docs/GLOSSAIRE.md` : quand un terme métier vous est inconnu.
9. `docs/decisions/` : pourquoi une décision a été prise.
10. Les documents de cadrage : `docs/CADRAGE.pdf` et `docs/CAHIER_DES_CHARGES.pdf`, qui décrivent encore les anciens rôles.
11. La spécification reçue du métier, dans `database/docs/` (spécification de conception, MCD, schéma SQL v1).

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

### 4.4 Ajouter une permission et la donner à un rôle ou à un groupe

1. **Déclarer** un cas dans `app/Support/Authorization/Permission.php`, au format `<module>.<action>`. La méthode `module()` y rattache l'action à son module (les préfixes `users`, `roles`, `settings`, `connectors` et `audit` relèvent de l'administration).
2. **Attribuer** la permission aux rôles concernés dans `Role::permissions()` (`app/Support/Authorization/Role.php`). Un rôle hérite de ses parents (`Role::parent()`) : ne listez que ce que le rôle ajoute, le seeder retire ce qui est déjà hérité. Pour un groupe système, voir `SYSTEM_GROUPS` dans `database/seeders/RolesAndPermissionsSeeder.php`.
3. **Libellés** : `permissions.<module>.<action>` dans `modules/Administration/lang/{en,fr}/administration.php`. Pour un nouveau module, ajouter aussi `permission_groups.<module>`.
4. **Protéger** : `->can(Permission::X->value)` sur la route, et `permission:` dans l'entrée de menu (recette 4.1).
5. **Catalogue** : `php artisan permissions:sync` enregistre la nouvelle action dans `action_catalog` (le seeder le fait aussi). Sur une base existante, la permission n'est accordée à aucun rôle : l'administrateur l'accorde depuis les pages Rôles ou Groupes (en développement : `php artisan migrate:fresh --seed`).
6. **Test** : `signInAs(Role::Agent)` pour tester un rôle, `signInWithPermissions(Permission::X, ...)` pour un utilisateur qui n'a que ces permissions (`tests/TestCase.php`). Voir `modules/Administration/Tests/Feature/RoleAdministrationTest.php`, `GroupAdministrationTest.php` et `tests/Feature/Kernel/PermissionResolverTest.php`.

Un administrateur crée aussi des rôles et des groupes et choisit leurs permissions sans toucher au code. Une permission, elle, n'existe que si le code la vérifie.

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
6. Quand un test couvre un cas d'usage ou une règle de gestion, ajoutez `#[Covers('UCxx')]` (convention décrite dans `docs/ACCEPTATION.md`) puis lancez `php artisan docs:acceptance`.

Modèle à copier : `modules/Licenses/Tests/Feature/LicensePagesTest.php`.

### 4.8 Lancer les tests

```bash
make test                                              # toute la suite
make artisan c="test --filter=LicensePagesTest"        # une classe
make artisan c="test --filter=test_licenses_can_be_filtered_by_status"   # une méthode
```

Avant de commiter, lancez toute la suite : elle inclut la complétude des traductions et les règles de navigation.

### 4.9 Ajouter une action nommée

Toute écriture métier qui doit laisser une trace (événement du navire, état courant, audit) passe par une action. Référence : `modules/Licenses/Actions/RecordLicense.php`.

1. **Créer la classe** dans `modules/<Module>/Actions/`, qui étend `App\Support\Actions\Action` : `code()` (par exemple `licenses.record`), `permission()` (une valeur de `Permission`) et `handle(ActionContext $context, array $input)`.
2. **Écrire et publier** dans `handle` : créer ou modifier les modèles, puis `$context->publish(...)` pour chaque fait à inscrire dans le journal du navire (module, type, objet concerné, clé de traduction du résumé et ses paramètres). Ne rien traduire dans `handle` : la clé est traduite à l'affichage.
3. **Traduire** le résumé dans `modules/<Module>/lang/{en,fr}/events.php`.
4. **Appeler** l'action depuis le contrôleur : `$actions->run(RecordLicense::class, $request->user(), $request->validated())`, où `$actions` est un `App\Support\Actions\ActionRunner` injecté. La validation reste dans la `FormRequest`.
5. **Tester** : l'événement, l'état courant, l'audit, le refus sans permission et le retour en arrière en cas d'échec (voir `modules/Licenses/Tests/Feature/RecordLicenseTest.php` et `tests/Feature/Kernel/ActionRunnerTest.php`).

L'`ActionRunner` fait le reste, dans une seule transaction : contrôle de la permission, écriture des événements, rafraîchissement de l'état courant des navires touchés, entrée d'audit. Si l'une de ces étapes échoue, rien n'est écrit.

### 4.10 Tenir la documentation générée à jour

`docs/reference/` n'est jamais écrit à la main : il est produit par le code.

| Fichier | Contenu | Commande |
|---|---|---|
| `modules.md`, `actions.md`, `roles.md`, `routes.md`, `schema.md` | modules, permissions et actions, rôles × permissions, routes, schéma de données (Mermaid) | `php artisan docs:generate` |
| `acceptance.md` | couverture des cas d'usage et règles de gestion par les tests | `php artisan docs:acceptance` |

Après avoir ajouté une migration, une permission, une action, une route ou un module, lancez `php artisan docs:generate`. Après avoir écrit un test qui recouvre un cas d'usage (UC) ou une règle (RG), ajoutez `#[Covers('UC02', partial: true)]` à la classe ou à la méthode (`partial: true` tant que le cas n'est pas couvert en entier), puis `php artisan docs:acceptance`. `tests/Feature/Docs/GeneratedDocumentationTest.php` échoue si l'un des deux n'est pas à jour : c'est la même vérification que `--check`.

Le manuel d'utilisation (`docs/manuel/`) n'est pas généré à chaque commande : quand l'interface ou un parcours change, régénérez ses captures et son PDF (`docs/manuel/README.md`).

### 4.11 Demander une confirmation, annoncer une réussite, ouvrir une fenêtre

Trois fenêtres existent déjà (voir `docs/DESIGN.md`, section Fenêtres) ; rien à écrire en JavaScript.

1. **Confirmation avant une action** : poser `data-confirm="{{ __('<module>::confirm.<cle>') }}"` et `data-confirm-label="{{ __('<module>::confirm.<cle>_yes') }}"` sur le `<form>` (ou sur le bouton, quand un formulaire a plusieurs boutons). Les textes vont dans `modules/<Module>/lang/{en,fr}/confirm.php`. Référence : `modules/Infractions/resources/views/show.blade.php` (clôture d'un dossier).
2. **Message de réussite** : ajouter `->with('popup', true)` à la redirection (ou un tableau `[['label' => …, 'value' => …]]` pour des tuiles de récapitulatif). Référence : `CaseFileController::close`.
3. **Aperçu d'une ressource** : un lien `data-gs-modal` charge l'URL dans une fenêtre ; la route renvoie un fragment sans layout. Référence : `VesselController::quick` et `modules/Vessels/resources/views/quick.blade.php`.

### 4.12 Ajouter une page à la visite guidée

1. **Cible** : poser `data-guide="clé"` sur la carte à expliquer (`<x-ui.card data-guide="clé" …>`), ou réutiliser les sélecteurs de liste (`.gs-table-toolbar`, `.gs-filter-row`, `.card .table`).
2. **Étapes** : déclarer la route et ses étapes (`étape => sélecteur`) dans `config/guide.php`.
3. **Textes** : l'introduction (`pages.<route avec _>.intro`) et, si besoin, le titre et le texte de chaque étape dans `lang/{en,fr}/guide.php` ; les étapes communes (barre d'outils, filtres, tableau) ont déjà un texte par défaut.
4. **Test** : `tests/Feature/GuideTest.php` échoue si la route n'existe pas ou s'il manque un texte dans une langue.

### 4.13 Travailler avec Claude Code

Le dépôt contient des *skills* Claude Code dans `.claude/skills/` : ce sont les recettes ci-dessus, sous une forme que l'assistant applique automatiquement quand la demande correspond. Ils sont chargés au démarrage d'une session.

| Skill | Quand |
|---|---|
| `new-module` | créer un module |
| `add-action` | écriture métier tracée (recette 4.9) |
| `add-alert` | nouvelle alerte planifiée (recette 4.2) |
| `add-permission` | nouvelle permission (recette 4.4) |
| `add-setting` | nouveau réglage administrable (recette 4.3) |
| `add-migration` | table ou colonne du schéma v2 (recette 4.5) |
| `page-ui` | vues, composants, thème |
| `ui-verify` | vérifier une page dans un navigateur sans interface |
| `dev-env` | Docker, base de données, tests, pièges connus |
| `phase-workflow` | suivre le plan de refonte et ses règles |
| `project-commit` | conventions de commit |

Quand une recette change, mettez à jour le skill correspondant en même temps que ce guide.

---

## 5. Ce qui reste à confirmer ou à brancher

- Les points métier ouverts : `docs/QUESTIONS_GILDAS.md`. En attendant ses réponses, chaque choix est une hypothèse provisoire (`docs/assumptions/`, identifiant `H-<phase>-<n>`) : ne contournez pas, modifiez l'hypothèse à l'endroit indiqué par sa fiche.
- Les sources externes (THEMIS, NENO, e-inspection, DPEP) sont alimentées par des données enregistrées tant que les accès réels ne sont pas obtenus (`modules/Administration/README.md`, `docs/assumptions/phase-7.md`).
