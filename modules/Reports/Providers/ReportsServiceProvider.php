<?php

namespace Modules\Reports\Providers;

use App\Support\Authorization\Permission;
use App\Support\Modules\ModuleServiceProvider;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationGroup;
use Illuminate\Console\Scheduling\Schedule;
use Modules\Reports\Console\ArchiveOperations;
use Modules\Reports\Console\RunScheduledReports;

class ReportsServiceProvider extends ModuleServiceProvider
{
    protected function navigation(Navigation $navigation): void
    {
        $permission = Permission::ReportsView->value;

        $navigation
            ->add('reports::reports.scheduled.title', 'reports.index', 'icon-file-text', NavigationGroup::Administration, order: 60, permission: $permission)
            ->add('reports::reports.exports.title', 'reports.exports.index', 'icon-download', NavigationGroup::Administration, order: 61, permission: $permission)
            ->add('reports::reports.archives.title', 'reports.archives.index', 'icon-archive', NavigationGroup::Administration, order: 62, permission: $permission)
            ->add('reports::reports.ai_agent.title', 'reports.ai-agent.index', 'icon-cpu', NavigationGroup::Administration, order: 63, permission: $permission);
    }

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(RunScheduledReports::class)->dailyAt('05:00')->withoutOverlapping();
        $schedule->command(ArchiveOperations::class)->dailyAt('02:00')->withoutOverlapping();
    }

    protected function consoleCommands(): array
    {
        return [RunScheduledReports::class, ArchiveOperations::class];
    }
}
