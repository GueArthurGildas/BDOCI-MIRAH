<?php

use App\Providers\AppServiceProvider;
use Modules\Administration\Providers\AdministrationServiceProvider;
use Modules\Auth\Providers\AuthServiceProvider;
use Modules\Catches\Providers\CatchesServiceProvider;
use Modules\Communications\Providers\CommunicationsServiceProvider;
use Modules\Dashboard\Providers\DashboardServiceProvider;
use Modules\Indicators\Providers\IndicatorsServiceProvider;
use Modules\Infractions\Providers\InfractionsServiceProvider;
use Modules\Inspections\Providers\InspectionsServiceProvider;
use Modules\Licenses\Providers\LicensesServiceProvider;
use Modules\Logbook\Providers\LogbookServiceProvider;
use Modules\Missions\Providers\MissionsServiceProvider;
use Modules\Movements\Providers\MovementsServiceProvider;
use Modules\Mrep\Providers\MrepServiceProvider;
use Modules\Notifications\Providers\NotificationsServiceProvider;
use Modules\Operations\Providers\OperationsServiceProvider;
use Modules\Registry\Providers\RegistryServiceProvider;
use Modules\Reports\Providers\ReportsServiceProvider;
use Modules\Search\Providers\SearchServiceProvider;
use Modules\Tracking\Providers\TrackingServiceProvider;
use Modules\Vessels\Providers\VesselsServiceProvider;
use Modules\Vms\Providers\VmsServiceProvider;
use Modules\Watch\Providers\WatchServiceProvider;

return [
    AppServiceProvider::class,
    AdministrationServiceProvider::class,
    AuthServiceProvider::class,
    CatchesServiceProvider::class,
    CommunicationsServiceProvider::class,
    DashboardServiceProvider::class,
    InfractionsServiceProvider::class,
    InspectionsServiceProvider::class,
    LicensesServiceProvider::class,
    LogbookServiceProvider::class,
    MissionsServiceProvider::class,
    MovementsServiceProvider::class,
    MrepServiceProvider::class,
    NotificationsServiceProvider::class,
    RegistryServiceProvider::class,
    ReportsServiceProvider::class,
    OperationsServiceProvider::class,
    SearchServiceProvider::class,
    TrackingServiceProvider::class,
    VesselsServiceProvider::class,
    VmsServiceProvider::class,
    WatchServiceProvider::class,
    IndicatorsServiceProvider::class,
];
