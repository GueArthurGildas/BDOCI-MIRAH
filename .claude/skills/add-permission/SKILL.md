---
name: add-permission
description: Add a permission (action in the catalog) and grant it to roles or groups, with translations and tests. Use when a new screen or action needs access control, or when asked to change what a role or group can do in code.
---

# New permission (ADR 0011)

Permissions are the actions of the catalog, declared in code. The Admin grants them to roles and groups in *Administration > Roles et permissions* and *Groupes de permissions*. A permission nothing checks has no effect. `can()`, `->can()` and `@can` go through `App\Support\Authorization\PermissionResolver` (role, role ancestors, groups, validity period, optional zone).

## Steps

1. **Declare** a case in `app/Support/Authorization/Permission.php`, value `'<module>.<action>'` (`module()` maps the prefix to a module; `users`, `roles`, `settings`, `connectors`, `audit` belong to `administration`; `verb()` derives view/create/update).
2. **Grant** in `app/Support/Authorization/Role.php` (`permissions()`): list the full set a role should have; the seeder stores only what the role adds to its parent's set (`Role::parent()`: `director` > `team_lead` > `agent`). Rules:
   - business permissions reach `Director` and `TeamLead` automatically (everything outside `TECHNICAL_PERMISSIONS`) and `Agent` only if listed;
   - the **Admin holds no business permission** (requirement S-004): only dashboard view and the technical ones. A technical permission goes in `TECHNICAL_PERMISSIONS`;
   - a system group gets default permissions in `SYSTEM_GROUPS` of `database/seeders/RolesAndPermissionsSeeder.php`.
3. **Translate** in `modules/Administration/lang/{en,fr}/administration.php`: `permissions.<module>.<action>`, plus `permission_groups.<module>` for a new module (forms and matrix read them).
4. **Protect** the route (`->can(Permission::X->value)`), the navigation entry (`permission:`), and Blade (`@can`). For zone-scoped checks pass the zone: `Gate::allows($code, $zone)`.
5. **Catalog**: `php artisan permissions:sync` records the action in `action_catalog` (the seeder does it too). **Run it on every deployment after `migrate`.**
6. **Tests**: `signInAs(Role::X)` for a role, `signInWithPermissions(Permission::A, ...)` for a user holding only those permissions (`tests/TestCase.php`). Never call Spatie's `givePermissionTo` / `syncPermissions`: its permission tables are unused. Change grants through `App\Support\Authorization\GrantService` (it refreshes the resolver); group membership through `$user->permissionGroups()` (the pivot refreshes it).

## Existing databases

The seeder only writes a role's grants when the role is created (plus the Admin role and system role parents every run), so UI edits survive. A new permission is therefore granted to nobody on an existing database: grant it from the roles or groups page, or in development run `php artisan migrate:fresh --seed`.

## Reference tests

`modules/Administration/Tests/Feature/RoleAdministrationTest.php`, `GroupAdministrationTest.php`, `tests/Feature/Kernel/PermissionResolverTest.php`.
