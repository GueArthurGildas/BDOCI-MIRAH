<?php

use App\Providers\AppServiceProvider;
use Modules\Auth\Providers\AuthServiceProvider;
use Modules\Dashboard\Providers\DashboardServiceProvider;

return [
    AppServiceProvider::class,
    AuthServiceProvider::class,
    DashboardServiceProvider::class,
];
