<?php

namespace Modules\Tracking\Console;

use App\Queries\SilentBeacons;
use App\Support\Authorization\Permission;
use App\Support\Notifications\Notifier;
use Illuminate\Console\Command;
use Modules\Tracking\Notifications\BeaconsSilentAlert;

class NotifySilentBeacons extends Command
{
    private const COOLDOWN_HOURS = 4;

    protected $signature = 'tracking:notify-silent-beacons';

    protected $description = 'Notify tracking viewers when installed beacons stopped emitting';

    public function handle(Notifier $notifier): int
    {
        $count = SilentBeacons::vessels()->count();

        if ($count > 0) {
            $notifier->notifyPermissionHolders(Permission::TrackingView, new BeaconsSilentAlert($count), self::COOLDOWN_HOURS);
        }

        return self::SUCCESS;
    }
}
