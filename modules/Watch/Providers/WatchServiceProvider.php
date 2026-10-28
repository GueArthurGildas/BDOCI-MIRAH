<?php

namespace Modules\Watch\Providers;

use App\Support\Authorization\Permission;
use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationGroup;

class WatchServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $navigation
            ->add('watch::watch.board.nav', 'watch.board.index', 'icon-sunrise', NavigationGroup::Surveillance, order: 30, permission: Permission::WatchView->value)
            ->add('watch::watch.observations.nav', 'watch.observations.index', 'icon-search', NavigationGroup::Surveillance, order: 31, permission: Permission::WatchView->value)
            ->add('watch::watch.rotations.nav', 'watch.rotations.index', 'icon-repeat', NavigationGroup::Surveillance, order: 32, permission: Permission::WatchView->value)
            ->add('watch::watch.instructions.nav', 'watch.instructions.index', 'icon-clipboard', NavigationGroup::Surveillance, order: 33, permission: Permission::WatchView->value);
    }
}
