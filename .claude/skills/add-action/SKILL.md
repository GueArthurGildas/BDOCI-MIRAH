---
name: add-action
description: Add a named business action (permission check, write, vessel event, current state, audit in one transaction) following ADR 0010. Use when a user action must write business data traceably, or when converting an existing controller write into an action.
---

# Named action (ADR 0010)

Reference implementation: `modules/Licenses/Actions/RecordLicense.php`, called from `LicenseController@store`. Kernel: `app/Support/Actions/{Action,ActionContext,ActionRunner}.php`.

## Steps

1. **Class** `modules/<Module>/Actions/<Verb><Noun>.php` extends `App\Support\Actions\Action`:
   - `code()`: `'<module>.<verb>'` (shown in the audit log),
   - `permission()`: a `Permission` case,
   - `handle(ActionContext $context, array $input)`: write models, then `$context->publish(vessel: $id, module: '<module-kebab>', type: '<noun>.<past-verb>', subject: $model, summaryKey: '<module>::events.<key>', params: [...])` for each fact to put in the vessel journal. Return the main model (it becomes the audit subject).
2. **Never translate or format text in `handle`**: events store a translation key plus scalar params, translated at display time. Add the key to `modules/<Module>/lang/{en,fr}/events.php`.
3. **Validation** stays in a `FormRequest`; the action receives `$request->validated()`.
4. **Controller**: inject `App\Support\Actions\ActionRunner $actions`; `$actions->run(MyAction::class, $request->user(), $request->validated())`. Route protected with `can:` too.
5. **Audit page**: add a label for the new `code()` under `administration.audit.events` (en/fr) if you want a readable event name; otherwise the raw code shows.
6. **Tests** (copy `RecordLicenseTest` and `tests/Feature/Kernel/ActionRunnerTest.php`): event row (module code, type, object, user), current state, audit entry with causer, forbidden without permission and nothing written, validation failures write nothing, rollback when the action throws.

## Rules

- The runner opens the transaction, checks `$actor->can(...)`, persists events, refreshes `vessel_current_states` for touched vessels, writes the audit entry. Do not duplicate any of that inside the action.
- `VesselStateBuilder` only computes facts that are defined (license status, expiry, last position, VMS silence). Do not add business rules that the spec (`database/docs/`) does not define: the status light, "open" infractions and enhanced monitoring are open questions for Gildas (see `docs/QUESTIONS_GILDAS.md`).
- Other writes still go through controllers; convert them module by module, only when asked.
- After adding an action or a permission, run `php artisan docs:generate` (catalogue in `docs/reference/actions.md`). A test that covers a use case or business rule of the specification carries `#[Covers('UC02', partial: true)]` (`Tests\Attributes\Covers`); run `php artisan docs:acceptance` afterwards.
