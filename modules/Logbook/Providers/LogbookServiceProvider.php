<?php

namespace Modules\Logbook\Providers;

use App\Support\Authorization\Permission;
use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationGroup;

class LogbookServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $navigation->add('logbook::logbook.title', 'logbook.index', 'icon-book', NavigationGroup::Surveillance, order: 40, permission: Permission::LogbookView->value);
    }
}
