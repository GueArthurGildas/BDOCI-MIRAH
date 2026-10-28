<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Indicators\Http\Controllers\FleetController;
use Modules\Indicators\Http\Controllers\IndicatorController;

Route::middleware('auth')->can(Permission::IndicatorsView->value)->group(function () {
    Route::get('fleet', FleetController::class)->name('fleet.index');
    Route::get('indicators/{indicator}', IndicatorController::class)->name('indicators.show');
});
