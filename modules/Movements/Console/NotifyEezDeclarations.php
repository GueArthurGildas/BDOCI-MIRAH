<?php

namespace Modules\Movements\Console;

use App\Enums\EntryExitAlertStatus;
use App\Models\EntryExitAlert;
use App\Models\MissedDeclaration;
use App\Support\Authorization\Permission;
use App\Support\Notifications\Notifier;
use Illuminate\Console\Command;
use Modules\Movements\Notifications\EntryExitAlertsAlert;
use Modules\Movements\Notifications\MissedDeclarationsAlert;
use Modules\Movements\Services\MissedDeclarationDetector;

class NotifyEezDeclarations extends Command
{
    protected $signature = 'movements:notify-eez-declarations';

    protected $description = 'Detect missed EEZ declarations and notify declaration viewers of missed declarations and open entry/exit alerts';

    public function handle(MissedDeclarationDetector $detector, Notifier $notifier): int
    {
        $detector->detect();

        if (! config('surveillance.alerts.eez_declarations.enabled')) {
            return self::SUCCESS;
        }

        $cooldown = config('surveillance.alerts.eez_declarations.cooldown_hours');
        $missed = MissedDeclaration::unresolved()->count();
        $open = EntryExitAlert::where('status', EntryExitAlertStatus::Open)->count();

        if ($missed > 0) {
            $notifier->notifyPermissionHolders(Permission::MovementsView, new MissedDeclarationsAlert($missed), $cooldown);
        }

        if ($open > 0) {
            $notifier->notifyPermissionHolders(Permission::MovementsView, new EntryExitAlertsAlert($open), $cooldown);
        }

        return self::SUCCESS;
    }
}
