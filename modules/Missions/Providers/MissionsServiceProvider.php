<?php

namespace Modules\Missions\Providers;

use App\Support\Authorization\Permission;
use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationGroup;

class MissionsServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $navigation->add('missions::missions.title', 'missions.index', 'icon-flag', NavigationGroup::Controls, order: 40, permission: Permission::MissionsView->value);
    }
}
