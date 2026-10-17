<?php

namespace App\Support\Modules;

use App\Support\Navigation\Navigation;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use ReflectionClass;

abstract class ModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadModuleRoutes();
        $this->loadViewsFrom($this->modulePath('resources/views'), $this->moduleNamespace());
        $this->loadTranslationsFrom($this->modulePath('lang'), $this->moduleNamespace());

        $this->callAfterResolving(Navigation::class, $this->navigation(...));
    }

    protected function navigation(Navigation $navigation): void
    {
    }

    protected function moduleNamespace(): string
    {
        return Str::kebab($this->moduleName());
    }

    protected function moduleName(): string
    {
        return basename($this->modulePath());
    }

    protected function modulePath(string $path = ''): string
    {
        $root = dirname((new ReflectionClass($this))->getFileName(), 2);

        return $path === '' ? $root : $root.DIRECTORY_SEPARATOR.$path;
    }

    private function loadModuleRoutes(): void
    {
        $routes = $this->modulePath('routes/web.php');

        if ($this->app->routesAreCached() || ! file_exists($routes)) {
            return;
        }

        Route::middleware('web')->group($routes);
    }
}
