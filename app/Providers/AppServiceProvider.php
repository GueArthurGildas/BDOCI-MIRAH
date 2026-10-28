<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Zone;
use App\Support\Authorization\ActionCatalog;
use App\Support\Authorization\PermissionResolver;
use App\Support\Localization\Locales;
use App\Support\Modules\ModuleRegistry;
use App\Support\Navigation\Navigation;
use App\Support\Settings\Settings;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Navigation::class);
        $this->app->singleton(Settings::class);
        $this->app->singleton(ModuleRegistry::class);
        $this->app->singleton(PermissionResolver::class);
        $this->app->singleton(ActionCatalog::class);
        $this->app->singleton(Locales::class, fn () => new Locales(config('app.supported_locales')));
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Gate::before(fn (User $user, string $ability, array $arguments) => $this->app->make(PermissionResolver::class)->allows($user, $ability, ($arguments[0] ?? null) instanceof Zone ? $arguments[0] : null) ?: null);
        Event::listen(RequestHandled::class, fn () => $this->app->make(PermissionResolver::class)->flush());
        Event::listen(Login::class, fn (Login $event) => $event->user->forceFill(['last_login_at' => now()])->saveQuietly());
        $this->app->make(Settings::class)->apply();
    }
}
