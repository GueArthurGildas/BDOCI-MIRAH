<?php

namespace App\Support\Documentation;

use App\Support\Actions\Action;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use App\Support\Modules\ModuleRegistry;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use ReflectionClass;

final class ReferenceGenerator
{
    private const HEADER = "<!-- Fichier généré par `php artisan docs:generate` : ne pas le modifier à la main. -->\n\n";

    private const FRAMEWORK_TABLES = ['migrations', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs', 'sessions', 'password_reset_tokens', 'spatial_ref_sys'];

    public function __construct(private readonly ModuleRegistry $modules) {}

    /** @return array<string, string> */
    public function files(): array
    {
        $previousLocale = app()->getLocale();
        app()->setLocale('fr');

        try {
            return [
                'docs/reference/modules.md' => self::HEADER.$this->modules(),
                'docs/reference/actions.md' => self::HEADER.$this->actions(),
                'docs/reference/roles.md' => self::HEADER.$this->roles(),
                'docs/reference/routes.md' => self::HEADER.$this->routes(),
                'docs/reference/schema.md' => self::HEADER.$this->schema(),
            ];
        } finally {
            app()->setLocale($previousLocale);
        }
    }

    private function modules(): string
    {
        $rows = collect($this->modules->declared())
            ->sortKeys()
            ->map(fn (array $module, string $code) => sprintf(
                '| `%s` | %s | %s | %s |',
                $code,
                __($module['label']),
                $module['version'],
                $module['dependencies'] === [] ? '—' : implode(', ', array_map(fn (string $dependency) => "`{$dependency}`", $module['dependencies'])),
            ));

        return "# Modules\n\nModules déclarés dans le registre (`ModuleServiceProvider`).\n\n| Code | Libellé | Version | Dépendances |\n|---|---|---|---|\n".$rows->implode("\n")."\n";
    }

    private function actions(): string
    {
        $permissions = collect(Permission::cases())
            ->sortBy(fn (Permission $permission) => $permission->value)
            ->map(fn (Permission $permission) => sprintf(
                '| `%s` | `%s` | `%s` | %s |',
                $permission->value,
                $permission->module(),
                $permission->verb(),
                __('administration::administration.permissions.'.$permission->value),
            ));

        $actions = $this->actionClasses()
            ->map(fn (string $class) => app($class))
            ->sortBy(fn (Action $action) => $action->code())
            ->map(fn (Action $action) => sprintf('| `%s` | `%s` | `%s` |', $action->code(), $action->permission()->value, get_class($action)));

        return "# Actions et permissions\n\n## Permissions du catalogue\n\n| Code | Module | Verbe | Libellé |\n|---|---|---|---|\n"
            .$permissions->implode("\n")
            ."\n\n## Actions nommées\n\nActions exécutées par l'`ActionRunner` (transaction, droit, événement, état courant, audit).\n\n| Code | Permission | Classe |\n|---|---|---|\n"
            .($actions->isEmpty() ? '' : $actions->implode("\n")."\n");
    }

    private function roles(): string
    {
        $permissions = collect(Permission::cases())->sortBy(fn (Permission $permission) => $permission->value);
        $roles = collect(Role::cases());

        $header = '| Permission | '.$roles->map(fn (Role $role) => __($role->label()))->implode(' | ')." |\n|---|".str_repeat('---|', $roles->count());
        $rows = $permissions->map(fn (Permission $permission) => '| `'.$permission->value.'` | '.$roles->map(
            fn (Role $role) => in_array($permission, $role->permissions(), true) ? 'oui' : '—',
        )->implode(' | ').' |');

        return "# Rôles et permissions\n\nPermissions accordées par défaut à chaque rôle système (l'héritage `Directeur → Chef d'équipe → Agent` est déjà appliqué). L'administrateur peut les modifier dans l'application.\n\n"
            .$header."\n".$rows->implode("\n")."\n";
    }

    private function routes(): string
    {
        $rows = collect(RouteFacade::getRoutes()->getRoutes())
            ->filter(fn (Route $route) => $route->getName() !== null && $this->moduleOf($route) !== null)
            ->sortBy(fn (Route $route) => $route->getName())
            ->map(fn (Route $route) => sprintf(
                '| `%s` | %s | `/%s` | %s |',
                $route->getName(),
                implode(', ', array_diff($route->methods(), ['HEAD'])),
                ltrim($route->uri(), '/'),
                $this->permissionOf($route),
            ))
            ->values();

        return "# Routes\n\nRoutes nommées de l'application et du socle, avec la permission exigée.\n\n| Nom | Méthode | URL | Permission |\n|---|---|---|---|\n".$rows->implode("\n")."\n";
    }

    private function schema(): string
    {
        $tables = collect(Schema::getTables())
            ->pluck('name')
            ->reject(fn (string $name) => in_array($name, self::FRAMEWORK_TABLES, true))
            ->sort()
            ->values();

        $entities = $tables->map(function (string $table) {
            $foreignColumns = collect(Schema::getForeignKeys($table))->pluck('columns')->flatten()->all();

            $columns = collect(Schema::getColumns($table))->map(function (array $column) use ($foreignColumns) {
                $marker = $column['name'] === 'id' ? ' PK' : (in_array($column['name'], $foreignColumns, true) ? ' FK' : '');

                return sprintf('        %s %s%s', Str::of($column['type_name'])->replaceMatches('/[^A-Za-z0-9_]/', '_'), $column['name'], $marker);
            });

            return "    {$table} {\n".$columns->implode("\n")."\n    }";
        });

        $relations = $tables->flatMap(fn (string $table) => collect(Schema::getForeignKeys($table))->map(
            fn (array $foreignKey) => sprintf('    %s ||--o{ %s : "%s"', $foreignKey['foreign_table'], $table, implode(', ', $foreignKey['columns'])),
        ))->unique()->sort()->values();

        return "# Schéma de données\n\nTables de l'application (hors tables du framework) et clés étrangères, relevées dans la base migrée.\n\n```mermaid\nerDiagram\n"
            .$entities->implode("\n")."\n".$relations->implode("\n")."\n```\n";
    }

    /** @return Collection<int, class-string<Action>> */
    private function actionClasses(): Collection
    {
        return collect(glob(base_path('modules/*/Actions/*.php')) ?: [])
            ->map(fn (string $file) => 'Modules\\'.Str::of($file)->after(base_path('modules/'))->before('.php')->replace('/', '\\'))
            ->filter(fn (string $class) => class_exists($class) && is_subclass_of($class, Action::class) && ! (new ReflectionClass($class))->isAbstract())
            ->values();
    }

    private function moduleOf(Route $route): ?string
    {
        $class = $route->getActionName();

        return Str::startsWith($class, ['App\\', 'Modules\\']) ? $class : null;
    }

    private function permissionOf(Route $route): string
    {
        $permission = collect($route->gatherMiddleware())
            ->filter(fn ($middleware) => is_string($middleware) && Str::startsWith($middleware, 'can:'))
            ->map(fn (string $middleware) => '`'.Str::after($middleware, 'can:').'`')
            ->implode(', ');

        return $permission === '' ? '—' : $permission;
    }
}
