<?php

namespace Modules\Mrep\Providers;

use App\Support\Authorization\Permission;
use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationGroup;

class MrepServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $navigation
            ->add('mrep::mrep.dpeps.nav', 'mrep.dpeps.index', 'icon-anchor', NavigationGroup::Controls, order: 40, permission: Permission::MrepView->value)
            ->add('mrep::mrep.refusals.nav', 'mrep.refusals', 'icon-slash', NavigationGroup::Controls, order: 41, permission: Permission::MrepView->value)
            ->add('mrep::mrep.targetings.nav', 'mrep.targetings', 'icon-target', NavigationGroup::Controls, order: 42, permission: Permission::MrepView->value);
    }
}
