<?php

namespace Modules\Tracking\Providers;

use App\Support\Authorization\Permission;
use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationGroup;

class TrackingServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $permission = Permission::TrackingView->value;

        $navigation
            ->add('tracking::tracking.map.title', 'tracking.map', 'icon-map', NavigationGroup::Surveillance, order: 10, permission: $permission)
            ->add('tracking::tracking.zones.title', 'tracking.zones', 'icon-bar-chart-2', NavigationGroup::Surveillance, order: 20, permission: $permission)
            ->add('tracking::tracking.beacons.title', 'tracking.beacons', 'icon-radio', NavigationGroup::Surveillance, order: 30, permission: $permission);
    }
}
