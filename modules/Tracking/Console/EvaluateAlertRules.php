<?php

namespace Modules\Tracking\Console;

use App\Queries\SilentBeacons;
use App\Support\Authorization\Permission;
use App\Support\Notifications\Notifier;
use App\Support\Vms\AlertRuleEvaluator;
use Illuminate\Console\Command;
use Modules\Tracking\Notifications\BeaconsSilentAlert;

class EvaluateAlertRules extends Command
{
    protected $signature = 'tracking:evaluate-alert-rules';

    protected $description = 'Evaluate the active alert rules, raise alerts and notify tracking viewers about silent beacons';

    public function handle(Notifier $notifier, AlertRuleEvaluator $evaluator): int
    {
        if (! config('surveillance.alerts.beacons_silent.enabled')) {
            return self::SUCCESS;
        }

        $evaluator->evaluate();

        $count = SilentBeacons::vessels()->count();

        if ($count > 0) {
            $notifier->notifyPermissionHolders(Permission::TrackingView, new BeaconsSilentAlert($count), config('surveillance.alerts.beacons_silent.cooldown_hours'));
        }

        return self::SUCCESS;
    }
}
