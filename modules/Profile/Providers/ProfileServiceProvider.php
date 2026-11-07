<?php

namespace Modules\Profile\Providers;

use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationGroup;

class ProfileServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $navigation->add('profile::profile.title', 'profile.show', 'icon-user', NavigationGroup::Overview, order: 60);
    }
}
