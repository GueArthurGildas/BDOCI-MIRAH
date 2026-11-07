<?php

namespace Modules\Catches\Providers;

use App\Support\Authorization\Permission;
use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationFolder;
use App\Support\Navigation\NavigationGroup;

class CatchesServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $navigation
            ->add('catches::catches.title', 'catches.index', 'icon-package', NavigationGroup::Overview, order: 3, permission: Permission::CatchesView->value, folder: NavigationFolder::EInspection)
            ->add('catches::catches.landings.title', 'catches.landings', 'icon-download', NavigationGroup::Overview, order: 1, permission: Permission::CatchesView->value, folder: NavigationFolder::EInspection)
            ->add('catches::catches.transshipments.title', 'catches.transshipments', 'icon-repeat', NavigationGroup::Overview, order: 4, permission: Permission::CatchesView->value, folder: NavigationFolder::EInspection);
    }
}
