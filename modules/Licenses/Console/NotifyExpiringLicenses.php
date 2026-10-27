<?php

namespace Modules\Licenses\Console;

use App\Enums\LicenseStatus;
use App\Models\License;
use App\Support\Authorization\Permission;
use App\Support\Notifications\Notifier;
use Illuminate\Console\Command;
use Modules\Licenses\Notifications\LicensesExpiringAlert;

class NotifyExpiringLicenses extends Command
{
    private const COOLDOWN_HOURS = 24;

    protected $signature = 'licenses:notify-expiring';

    protected $description = 'Notify license viewers when licenses are about to expire';

    public function handle(Notifier $notifier): int
    {
        $count = License::withStatus(LicenseStatus::Expiring)->count();

        if ($count > 0) {
            $notifier->notifyPermissionHolders(Permission::LicensesView, new LicensesExpiringAlert($count), self::COOLDOWN_HOURS);
        }

        return self::SUCCESS;
    }
}
