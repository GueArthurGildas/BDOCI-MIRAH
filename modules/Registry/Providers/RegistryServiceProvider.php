<?php

namespace Modules\Registry\Providers;

use App\Support\Authorization\Permission;
use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationGroup;

class RegistryServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $navigation->add('registry::registry.title', 'registry.index', 'icon-database', NavigationGroup::Registry, order: 20, permission: Permission::RegistryView->value);
    }
}
