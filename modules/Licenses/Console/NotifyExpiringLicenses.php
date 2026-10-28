<?php

namespace Modules\Licenses\Console;

use App\Models\License;
use App\Support\Authorization\Permission;
use App\Support\Notifications\Notifier;
use Illuminate\Console\Command;
use Modules\Licenses\Notifications\LicensesExpiringAlert;
use Modules\Licenses\Support\ExpiryTiers;

class NotifyExpiringLicenses extends Command
{
    protected $signature = 'licenses:notify-expiring';

    protected $description = 'Notify license viewers when licenses are about to expire (30, 60 and 90 days)';

    public function handle(Notifier $notifier): int
    {
        if (! config('surveillance.alerts.licenses_expiring.enabled')) {
            return self::SUCCESS;
        }

        foreach (ExpiryTiers::bands() as $band) {
            $count = License::expiringBetween($band['from'], $band['days'])->count();

            if ($count > 0) {
                $notifier->notifyPermissionHolders(Permission::LicensesView, new LicensesExpiringAlert($count, $band['days'], $band['from']), config('surveillance.alerts.licenses_expiring.cooldown_hours'));
            }
        }

        return self::SUCCESS;
    }
}
