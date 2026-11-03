<?php

namespace Modules\Logbook\Providers;

use App\Support\Authorization\Permission;
use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationFolder;
use App\Support\Navigation\NavigationGroup;

class LogbookServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $navigation->add('logbook::logbook.days.nav', 'logbook.days.index', 'icon-book', NavigationGroup::Surveillance, order: 6, permission: Permission::LogbookView->value, folder: NavigationFolder::Logbook);
        $navigation->add('logbook::logbook.observations_nav', 'logbook.index', 'icon-eye', NavigationGroup::Surveillance, order: 3, permission: Permission::LogbookView->value, folder: NavigationFolder::MorningBoard);
        $navigation->add('logbook::logbook.observations_nav', 'logbook.index', 'icon-eye', NavigationGroup::Surveillance, order: 8, permission: Permission::LogbookView->value, folder: NavigationFolder::Logbook);
    }
}
