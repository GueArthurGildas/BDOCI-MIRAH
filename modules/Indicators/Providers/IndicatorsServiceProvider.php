<?php

namespace Modules\Indicators\Providers;

use App\Support\Authorization\Permission;
use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationGroup;

class IndicatorsServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $navigation->add('indicators::indicators.fleet.title', 'fleet.index', 'icon-globe', NavigationGroup::Overview, order: 1, permission: Permission::IndicatorsView->value);
    }
}
