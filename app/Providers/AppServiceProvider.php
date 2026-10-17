<?php

namespace App\Providers;

use App\Support\Localization\Locales;
use App\Support\Navigation\Navigation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Navigation::class);
        $this->app->singleton(Locales::class, fn () => new Locales(config('app.supported_locales')));
    }
}
