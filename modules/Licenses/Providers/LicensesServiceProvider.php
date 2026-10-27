<?php

namespace Modules\Licenses\Providers;

use App\Support\Authorization\Permission;
use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationGroup;
use Illuminate\Console\Scheduling\Schedule;
use Modules\Licenses\Console\NotifyExpiringLicenses;

class LicensesServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $navigation->add('licenses::licenses.title', 'licenses.index', 'icon-file-text', NavigationGroup::Registry, order: 20, permission: Permission::LicensesView->value);
    }

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(NotifyExpiringLicenses::class)->dailyAt('07:00');
    }

    protected function consoleCommands(): array
    {
        return [NotifyExpiringLicenses::class];
    }
}
