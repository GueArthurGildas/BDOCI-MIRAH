---
name: new-module
description: Scaffold a new feature module under modules/ following this project's conventions (provider, routes, views, lang en/fr, tests, navigation, registration). Use when asked to add a module, a new section of the application, or a new top-level feature.
---

# New module

A feature = one folder `modules/<Name>/` (PascalCase, plural when natural). The shared kernel stays in `app/`. A module never imports another module (`tests/Unit/ModuleIndependenceTest.php` fails otherwise): share through `app/` (models, enums, `App\Support\*`).

## Checklist

1. **Layout**
   ```
   modules/<Name>/
     Actions/  Http/Controllers/  Http/Requests/  Providers/<Name>ServiceProvider.php
     lang/en/<name>.php  lang/fr/<name>.php
     resources/views/  routes/web.php  Tests/Feature/
   ```
   Namespace `Modules\<Name>\...` (PSR-4 already maps `Modules\` to `modules/`).
2. **Provider** extends `App\Support\Modules\ModuleServiceProvider`. Hooks: `navigation(Navigation $navigation)` (`$navigation->add('<name>::<name>.title', '<route>', 'icon-...', NavigationGroup::X, order:, permission:)`), `schedule(Schedule $schedule)`, `consoleCommands(): array`. Views and lang use the kebab-case namespace (`licenses::index`, `licenses::licenses.title`).
3. **Register** the provider in `bootstrap/providers.php` (alphabetical, after the other modules).
4. **Routes** in `routes/web.php` (already under the `web` group): `middleware('auth')` plus `->can(Permission::X->value)`.
5. **Permissions**: follow the `add-permission` skill. Navigation groups: `Overview`, `Surveillance`, `Registry`, `Controls`, `Administration`.
6. **Texts**: every displayed string is a translation key in `lang/en` AND `lang/fr` (parity enforced by `TranslationCompletenessTest`). No hardcoded text, no comments in code, identifiers in English.
7. **Pages**: use the `page-ui` skill (layout, `x-ui.*` components).
8. **Writes** that must be traceable go through a named action: `add-action` skill.
9. **Tests** in `Tests/Feature` (namespace `Modules\<Name>\Tests\Feature`, `RefreshDatabase`, `signInAs(Role::X)` from `tests/TestCase.php`): guests redirected, forbidden without permission, happy path.
10. Run `docker compose exec -T app php artisan modules:sync` (declares the module in `module_registry`) and the full suite.
11. **Docs**: add a README line in `docs/TECHNICAL.md` module table and, if the module maps to a spec module, update `docs/GLOSSAIRE.md` and `docs/ACCEPTATION.md`.

Reference module with everything wired: `modules/Licenses` (action, request, alert command, schedule, tests).
