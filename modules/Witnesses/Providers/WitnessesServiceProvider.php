<?php

namespace Modules\Witnesses\Providers;

use App\Support\Authorization\Permission;
use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationGroup;

class WitnessesServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $navigation
            ->add('witnesses::witnesses.title', 'witnesses.index', 'icon-eye', NavigationGroup::Surveillance, order: 70, permission: Permission::WitnessesView->value)
            ->add('witnesses::distributions.title', 'witnesses.distributions.index', 'icon-send', NavigationGroup::Surveillance, order: 71, permission: Permission::WitnessesManage->value);
    }
}
