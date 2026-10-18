<?php

namespace Modules\Movements\Providers;

use App\Support\Authorization\Permission;
use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationGroup;

class MovementsServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $navigation->add('movements::movements.title', 'movements.index', 'icon-navigation', NavigationGroup::Surveillance, order: 50, permission: Permission::MovementsView->value);
    }
}
