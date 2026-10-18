<?php

namespace Modules\Administration\Providers;

use App\Support\Authorization\Permission;
use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationGroup;

class AdministrationServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $navigation
            ->add('administration::administration.users.title', 'administration.users.index', 'icon-users', NavigationGroup::Administration, order: 10, permission: Permission::UsersManage->value)
            ->add('administration::administration.roles.title', 'administration.roles', 'icon-shield', NavigationGroup::Administration, order: 20, permission: Permission::UsersManage->value)
            ->add('administration::administration.connectors.title', 'administration.connectors', 'icon-activity', NavigationGroup::Administration, order: 30, permission: Permission::ConnectorsView->value)
            ->add('administration::administration.audit.title', 'administration.audit', 'icon-clipboard', NavigationGroup::Administration, order: 40, permission: Permission::AuditView->value);
    }
}
