<?php

namespace Modules\Dashboard\Providers;

use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;

class DashboardServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $navigation->add('dashboard::dashboard.title', 'dashboard', 'icon-home', order: 0);
    }
}
