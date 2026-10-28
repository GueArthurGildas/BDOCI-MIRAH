<?php

namespace Modules\Vms\Providers;

use App\Support\Authorization\Permission;
use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationGroup;

class VmsServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $permission = Permission::VmsView->value;

        $navigation
            ->add('vms::vms.beacons.title', 'vms.beacons.index', 'icon-radio', NavigationGroup::Surveillance, order: 40, permission: $permission)
            ->add('vms::vms.requests.title', 'vms.requests.index', 'icon-check-square', NavigationGroup::Surveillance, order: 41, permission: $permission)
            ->add('vms::vms.failures.title', 'vms.failures.index', 'icon-alert-octagon', NavigationGroup::Surveillance, order: 42, permission: $permission)
            ->add('vms::vms.ers.title', 'vms.ers.index', 'icon-file-text', NavigationGroup::Surveillance, order: 43, permission: $permission);
    }
}
