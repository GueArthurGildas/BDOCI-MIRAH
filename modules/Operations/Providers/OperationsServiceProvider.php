<?php

namespace Modules\Operations\Providers;

use App\Support\Authorization\Permission;
use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationGroup;

class OperationsServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $navigation->add('operations::operations.title', 'operations.index', 'icon-layers', NavigationGroup::Surveillance, order: 42, permission: Permission::OperationsView->value);
    }
}
