<?php

namespace Modules\Movements\Providers;

use App\Support\Authorization\Permission;
use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationGroup;
use Illuminate\Console\Scheduling\Schedule;
use Modules\Movements\Console\NotifyEezDeclarations;

class MovementsServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $permission = Permission::MovementsView->value;

        $navigation
            ->add('movements::eez.title', 'movements.eez.index', 'icon-navigation', NavigationGroup::Surveillance, order: 50, permission: $permission)
            ->add('movements::movements.title', 'movements.index', 'icon-anchor', NavigationGroup::Surveillance, order: 55, permission: $permission);
    }

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(NotifyEezDeclarations::class)->hourly()->withoutOverlapping();
    }

    protected function consoleCommands(): array
    {
        return [NotifyEezDeclarations::class];
    }
}
