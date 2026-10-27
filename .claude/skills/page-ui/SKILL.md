---
name: page-ui
description: Build or change a page with the project's layout, Blade components and GESICA theme rules (radius policy, flat colors, Adminty override pitfalls, i18n). Use when writing views, forms, tables, cards or SCSS.
---

# Pages and theme

## Page skeleton

```blade
<x-layouts.app :title="__('module::file.title')">
    <x-ui.page-header :title="__('module::file.title')" :subtitle="__('module::file.subtitle')" :breadcrumbs="[...]" />
    <div class="page-body"> ... </div>
</x-layouts.app>
```
The page-header icon defaults to the active navigation item's icon. Components in `resources/views/components/ui/`: `card` (title, subtitle, `flush`, `actions` slot), `table` (head slot, `paginator`), `empty-row`, `stat-card` (label, value, icon, `color` success|warning|danger|info, `compact`), `status-badge` (any enum implementing `HasBadge`), `filters` with `filter-input|select`, `detail-list` and `detail`, `tabs` and `tab-pane`, `chart`, `map`, `vessel-link`, and `form/{input,select,textarea,checkbox,actions}`.

## Rules

- **Texts**: `__('ui.x')` for shared strings (`lang/{en,fr}/ui.php`), `__('module::file.key')` for module strings; add both languages. Enum labels through `HasTranslatedLabel`. Event and alert texts are stored as keys and translated at display time.
- **Code**: English identifiers, no comments.
- Authorization in views: `@can(App\Support\Authorization\Permission::X->value)`.

## Theme (`resources/scss/theme/`, imported last in `app.scss`)

- Tokens in `_tokens.scss` only (`--gs-*`); never write a hex value elsewhere. Flat colors, no gradients.
- **Radius policy**: two radii, `--gs-radius` (6px, surfaces) and `--gs-radius-sm` (4px, controls and labels). Circles only for avatars, icon badges and dots. No pill shapes; do not round children of a card (the card clips them).
- Orange = brand and actions, black = structure, green/red/blue = states. Buttons: `btn-primary` (orange), `btn-default`, `btn-outline-*`, `btn-success|danger|info|secondary`.
- Adminty is layered underneath and is more specific in places. Known overrides in the theme: `.btn`/`.label` capitalization removed; `.card .card-header + .card-body` top padding restored; table header selector must stay `.table > thead > tr > th`; `.md-tabs .nav-item a` needs `!important` on padding; never use the `user-profile` class in the topbar (adds 40 px); `checkbox-fade` colors need `!important`. When a rule does not apply, probe the computed style (see the `ui-verify` skill) before adding `!important`.
- Responsive: the sidebar becomes a horizontal menu under 992 px, the topbar wraps under 576 px.
- After SCSS changes restart the `node` container, then verify with the `ui-verify` skill. Keep `docs/DESIGN.md` current.
