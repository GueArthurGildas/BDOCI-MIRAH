<?php

namespace Modules\Inspections\Providers;

use App\Support\Authorization\Permission;
use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationGroup;

class InspectionsServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $navigation->add('inspections::inspections.title', 'inspections.index', 'icon-check-square', NavigationGroup::Controls, order: 10, permission: Permission::InspectionsView->value);
    }
}
