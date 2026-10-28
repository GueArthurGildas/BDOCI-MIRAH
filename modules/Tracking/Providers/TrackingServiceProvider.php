<?php

namespace Modules\Tracking\Providers;

use App\Support\Authorization\Permission;
use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationGroup;
use Illuminate\Console\Scheduling\Schedule;
use Modules\Tracking\Console\AggregateMonthlyEmission;
use Modules\Tracking\Console\CreatePositionPartitions;
use Modules\Tracking\Console\EvaluateAlertRules;
use Modules\Tracking\Console\SummarizePositions;

class TrackingServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $permission = Permission::TrackingView->value;

        $navigation
            ->add('tracking::tracking.map.title', 'tracking.map', 'icon-map', NavigationGroup::Surveillance, order: 10, permission: $permission)
            ->add('tracking::tracking.zones.title', 'tracking.zones', 'icon-bar-chart-2', NavigationGroup::Surveillance, order: 20, permission: $permission)
            ->add('tracking::tracking.beacons.title', 'tracking.beacons', 'icon-radio', NavigationGroup::Surveillance, order: 30, permission: $permission)
            ->add('tracking::tracking.alerts.title', 'tracking.alerts.index', 'icon-alert-triangle', NavigationGroup::Surveillance, order: 34, permission: $permission)
            ->add('tracking::tracking.slots.title', 'tracking.slots.index', 'icon-camera', NavigationGroup::Surveillance, order: 35, permission: $permission)
            ->add('tracking::tracking.rules.title', 'tracking.rules.index', 'icon-sliders', NavigationGroup::Administration, order: 60, permission: Permission::SettingsManage->value);
    }

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(EvaluateAlertRules::class)->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command(SummarizePositions::class)->dailyAt('01:00');
        $schedule->command(AggregateMonthlyEmission::class)->monthlyOn(1, '02:00');
        $schedule->command(CreatePositionPartitions::class)->monthlyOn(1, '00:30');
    }

    protected function consoleCommands(): array
    {
        return [EvaluateAlertRules::class, SummarizePositions::class, AggregateMonthlyEmission::class, CreatePositionPartitions::class];
    }
}
