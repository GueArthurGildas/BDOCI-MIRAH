<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Tracking\Http\Controllers\TrackingController;

Route::middleware(['auth', 'can:'.Permission::TrackingView->value])
    ->prefix('tracking')
    ->name('tracking.')
    ->controller(TrackingController::class)
    ->group(function () {
        Route::get('map', 'map')->name('map');
        Route::get('zones', 'zones')->name('zones');
        Route::get('beacons', 'beacons')->name('beacons');
    });
