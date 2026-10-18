<?php

namespace Modules\Dashboard\Providers;

use App\Support\Authorization\Permission;
use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationGroup;

class DashboardServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $navigation->add('dashboard::dashboard.title', 'dashboard', 'icon-home', NavigationGroup::Overview, order: 0, permission: Permission::DashboardView->value);
    }
}
