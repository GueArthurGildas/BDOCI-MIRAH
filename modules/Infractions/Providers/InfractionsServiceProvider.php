<?php

namespace Modules\Infractions\Providers;

use App\Support\Authorization\Permission;
use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationGroup;

class InfractionsServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $navigation->add('infractions::infractions.title', 'infractions.index', 'icon-alert-triangle', NavigationGroup::Controls, order: 20, permission: Permission::InfractionsView->value);
    }
}
