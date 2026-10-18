<?php

use App\Providers\AppServiceProvider;
use Modules\Auth\Providers\AuthServiceProvider;
use Modules\Dashboard\Providers\DashboardServiceProvider;
use Modules\Licenses\Providers\LicensesServiceProvider;
use Modules\Logbook\Providers\LogbookServiceProvider;
use Modules\Movements\Providers\MovementsServiceProvider;
use Modules\Tracking\Providers\TrackingServiceProvider;
use Modules\Vessels\Providers\VesselsServiceProvider;

return [
    AppServiceProvider::class,
    AuthServiceProvider::class,
    DashboardServiceProvider::class,
    LicensesServiceProvider::class,
    LogbookServiceProvider::class,
    MovementsServiceProvider::class,
    TrackingServiceProvider::class,
    VesselsServiceProvider::class,
];
