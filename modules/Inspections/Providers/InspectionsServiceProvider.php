<?php

namespace Modules\Inspections\Providers;

use App\Support\Authorization\Permission;
use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationFolder;
use App\Support\Navigation\NavigationGroup;

class InspectionsServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $navigation->add('inspections::inspections.title', 'inspections.index', 'icon-check-square', NavigationGroup::Overview, order: 5, permission: Permission::InspectionsView->value, folder: NavigationFolder::EInspection);
        $navigation->add('inspections::never.nav', 'inspections.never-inspected', 'icon-eye-off', NavigationGroup::Overview, order: 6, permission: Permission::InspectionsView->value, folder: NavigationFolder::EInspection);
    }
}
