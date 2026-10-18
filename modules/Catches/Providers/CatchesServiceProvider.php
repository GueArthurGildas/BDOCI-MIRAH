<?php

namespace Modules\Catches\Providers;

use App\Support\Authorization\Permission;
use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationGroup;

class CatchesServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $navigation->add('catches::catches.title', 'catches.index', 'icon-package', NavigationGroup::Controls, order: 30, permission: Permission::CatchesView->value);
    }
}
