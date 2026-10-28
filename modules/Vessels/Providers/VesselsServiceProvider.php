<?php

namespace Modules\Vessels\Providers;

use App\Support\Authorization\Permission;
use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationGroup;

class VesselsServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $navigation->add('vessels::vessels.title', 'vessels.index', 'icon-anchor', NavigationGroup::Registry, order: 10, permission: Permission::VesselsView->value);
        $navigation->add('vessels::vessels.duplicates.title', 'vessels.duplicates.index', 'icon-copy', NavigationGroup::Registry, order: 12, permission: Permission::VesselsMatch->value);
    }
}
