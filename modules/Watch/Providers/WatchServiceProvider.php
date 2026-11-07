<?php

namespace Modules\Watch\Providers;

use App\Support\Authorization\Permission;
use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationFolder;
use App\Support\Navigation\NavigationGroup;

class WatchServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $navigation
            ->add('watch::watch.board.overview_nav', 'watch.board.index', 'icon-sunrise', NavigationGroup::Surveillance, order: 0, permission: Permission::WatchView->value, folder: NavigationFolder::MorningBoard)
            ->add('watch::watch.observations.nav', 'watch.observations.index', 'icon-search', NavigationGroup::Surveillance, order: 1, permission: Permission::WatchView->value, folder: NavigationFolder::Logbook)
            ->add('watch::watch.rotations.nav', 'watch.rotations.index', 'icon-repeat', NavigationGroup::Surveillance, order: 2, permission: Permission::WatchView->value, folder: NavigationFolder::Logbook)
            ->add('watch::watch.instructions.nav', 'watch.instructions.index', 'icon-clipboard', NavigationGroup::Surveillance, order: 2, permission: Permission::WatchView->value, folder: NavigationFolder::MorningBoard)
            ->add('watch::watch.instructions.nav', 'watch.instructions.index', 'icon-clipboard', NavigationGroup::Surveillance, order: 3, permission: Permission::WatchView->value, folder: NavigationFolder::Logbook);
    }
}
