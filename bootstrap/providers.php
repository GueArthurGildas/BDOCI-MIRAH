<?php

use App\Providers\AppServiceProvider;
use Modules\Administration\Providers\AdministrationServiceProvider;
use Modules\Auth\Providers\AuthServiceProvider;
use Modules\Catches\Providers\CatchesServiceProvider;
use Modules\Dashboard\Providers\DashboardServiceProvider;
use Modules\Infractions\Providers\InfractionsServiceProvider;
use Modules\Inspections\Providers\InspectionsServiceProvider;
use Modules\Licenses\Providers\LicensesServiceProvider;
use Modules\Logbook\Providers\LogbookServiceProvider;
use Modules\Missions\Providers\MissionsServiceProvider;
use Modules\Movements\Providers\MovementsServiceProvider;
use Modules\Notifications\Providers\NotificationsServiceProvider;
use Modules\Operations\Providers\OperationsServiceProvider;
use Modules\Search\Providers\SearchServiceProvider;
use Modules\Tracking\Providers\TrackingServiceProvider;
use Modules\Vessels\Providers\VesselsServiceProvider;
use Modules\Watch\Providers\WatchServiceProvider;

return [
    AppServiceProvider::class,
    AdministrationServiceProvider::class,
    AuthServiceProvider::class,
    CatchesServiceProvider::class,
    DashboardServiceProvider::class,
    InfractionsServiceProvider::class,
    InspectionsServiceProvider::class,
    LicensesServiceProvider::class,
    LogbookServiceProvider::class,
    MissionsServiceProvider::class,
    MovementsServiceProvider::class,
    NotificationsServiceProvider::class,
    OperationsServiceProvider::class,
    SearchServiceProvider::class,
    TrackingServiceProvider::class,
    VesselsServiceProvider::class,
    WatchServiceProvider::class,
];
