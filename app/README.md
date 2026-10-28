# Noyau applicatif (`app/`)

Le noyau est ce que les modules partagent. Un module (`modules/<Nom>/`) ne dépend que de `app/` et jamais d'un autre module : `tests/Unit/ModuleIndependenceTest.php` le vérifie. Ce qui est utile à plusieurs modules vit ici, le reste reste dans son module.

Pour créer un module : skill `new-module` et `docs/GUIDE.md`, recette 4.1. Le catalogue généré des modules, actions, rôles, routes et tables est dans `docs/reference/`.

## Carte du noyau

| Dossier | Rôle | Décision |
|---|---|---|
| `Models/` | modèles du domaine : navires, licences, utilisateurs, journal des événements, état courant, droits, génériques | |
| `Enums/` | énumérations métier ; leurs libellés passent par `lang/{en,fr}/enums.php` | ADR 0001 |
| `Support/Modules/` | `ModuleRegistry` (registre des modules) et `ModuleServiceProvider` (base des modules : navigation, tâches planifiées, commandes) | |
| `Support/Navigation/` | menu construit par les modules, filtré par les droits | |
| `Support/Actions/` | `Action`, `ActionContext`, `ActionRunner` : une écriture métier = une action | ADR 0010 |
| `Support/Events/` | `VesselStateBuilder` : recalcule l'état courant d'un navire à partir des faits définis | ADR 0010 |
| `Support/Authorization/` | `Permission`, `Role`, `ActionCatalog`, `PermissionResolver`, `GrantService` : droits flexibles | ADR 0004, 0005, 0011 |
| `Support/Notifications/` | `Alert`, `Notifier` : alertes (toast et balise clignotante) avec délai de répétition, destinataires par permission | |
| `Support/Settings/` | `Settings` : réglages non sensibles modifiables par l'administrateur, qui surchargent la configuration | |
| `Support/Documents/` | `DocumentStore` (pièces avec empreinte, figeables) et `CommentService` | |
| `Support/Fields/` | `CustomFields` : champs extensibles typés | |
| `Support/Documentation/` | générateurs de `docs/reference/` | |
| `Console/Commands/` | `vessels:rebuild-state`, `modules:sync`, `permissions:sync`, `docs:generate`, `docs:acceptance` | |

## Une écriture métier, de bout en bout

1. Le contrôleur valide la requête (`FormRequest`) puis appelle `ActionRunner::run(MonAction::class, $utilisateur, $donnees)`.
2. L'`ActionRunner` ouvre une transaction et vérifie la permission de l'action auprès du `PermissionResolver`.
3. L'action écrit ses modèles et publie des faits par `ActionContext::publish(...)` : navire, module, type, objet, clé de traduction du résumé.
4. L'`ActionRunner` enregistre les faits dans `vessel_events`, rafraîchit `vessel_current_states` des navires touchés et écrit l'entrée d'audit.
5. Si une étape échoue, rien n'est écrit.

Recette : `docs/GUIDE.md`, 4.9, et skill `add-action`.

## Droits

Une permission est une valeur de l'énumération `Permission` (`module.verbe`), synchronisée avec la table `action_catalog` par `php artisan permissions:sync`. Elle est accordée à un rôle (avec héritage `Directeur → Chef d'équipe → Agent`) ou à un groupe, avec une zone et une période facultatives (`permission_grants`). Les contrôles passent par les Gates et les politiques de Laravel (`can:`, `@can`, `Gate::allows($code, $zone)`) : le `PermissionResolver` les résout par `Gate::before`. Ne jamais tester le nom d'un rôle dans le code. L'administrateur n'a aucun droit métier. Recette : skill `add-permission`.

## Règles du noyau

- Aucun texte affiché en dur : tout passe par des clés de traduction (`lang/` ou `modules/*/lang/`), y compris le résumé des événements, traduit à l'affichage.
- Le code est en anglais ; les commentaires sont proscrits, le nommage doit se suffire.
- Aucune règle métier n'est inventée : ce que la spécification (`database/docs/`) ne définit pas est une question ouverte dans `docs/QUESTIONS_GILDAS.md`. Par exemple, l'état courant ne calcule ni le feu de statut, ni les infractions « ouvertes », ni le suivi renforcé.
- Aucune suppression physique des données métier. Le journal d'audit (`activity_log`) et les documents figés sont protégés par des triggers PostgreSQL.
- Un nom de table ou de colonne suit `docs/schema/CONVENTIONS.md` ; un écart se consigne dans sa section 11.

## Tests

`tests/Feature/Kernel/` couvre le noyau : `ActionRunnerTest`, `VesselStateTest`, `ModuleRegistryTest`, `AuditLogImmutabilityTest`, `PermissionResolverTest`, `GenericElementsTest`, `GenericServicesTest`. Les tests d'un module sont dans `modules/<Nom>/Tests/`. L'assistant `signInWithPermissions(Permission ...)` de `tests/TestCase.php` connecte un utilisateur doté de permissions précises.
