<?php

namespace Modules\Notifications\Providers;

use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationGroup;

class NotificationsServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $navigation->add('notifications::notifications.title', 'notifications.index', 'icon-bell', NavigationGroup::Overview, order: 90);
    }
}
