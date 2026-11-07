<?php

namespace Modules\Infractions\Providers;

use App\Support\Authorization\Permission;
use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationFolder;
use App\Support\Navigation\NavigationGroup;

class InfractionsServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $navigation
            ->add('infractions::infractions.title', 'infractions.index', 'icon-alert-triangle', NavigationGroup::Overview, order: 2, permission: Permission::InfractionsView->value, folder: NavigationFolder::EInspection)
            ->add('infractions::infractions.nomenclature', 'infractions.types.index', 'icon-list', NavigationGroup::Administration, order: 60, permission: Permission::InfractionsManageTypes->value);
    }
}
