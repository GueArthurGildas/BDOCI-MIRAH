<?php

namespace Modules\Administration\Providers;

use App\Support\Authorization\Permission;
use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationGroup;
use Illuminate\Console\Scheduling\Schedule;
use Modules\Administration\Console\RefreshConnectorFreshness;
use Modules\Administration\Console\SynchroniseConnectors;

class AdministrationServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $navigation
            ->add('administration::administration.users.title', 'administration.users.index', 'icon-users', NavigationGroup::Administration, order: 10, permission: Permission::UsersManage->value)
            ->add('administration::administration.roles.title', 'administration.roles.index', 'icon-shield', NavigationGroup::Administration, order: 20, permission: Permission::RolesManage->value)
            ->add('administration::groups.title', 'administration.groups.index', 'icon-layers', NavigationGroup::Administration, order: 25, permission: Permission::RolesManage->value)
            ->add('administration::administration.connectors.title', 'administration.connectors', 'icon-activity', NavigationGroup::Administration, order: 30, permission: Permission::ConnectorsView->value)
            ->add('administration::administration.audit.title', 'administration.audit', 'icon-clipboard', NavigationGroup::Administration, order: 40, permission: Permission::AuditView->value)
            ->add('administration::reference_lists.title', 'administration.reference-lists.index', 'icon-list', NavigationGroup::Administration, order: 45, permission: Permission::SettingsManage->value)
            ->add('administration::custom_fields.title', 'administration.custom-fields.index', 'icon-sliders', NavigationGroup::Administration, order: 47, permission: Permission::SettingsManage->value)
            ->add('administration::settings.title', 'administration.settings', 'icon-settings', NavigationGroup::Administration, order: 50, permission: Permission::SettingsManage->value);
    }

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(SynchroniseConnectors::class)->hourly()->withoutOverlapping();
        $schedule->command(RefreshConnectorFreshness::class)->hourly();
    }

    protected function consoleCommands(): array
    {
        return [SynchroniseConnectors::class, RefreshConnectorFreshness::class];
    }
}
