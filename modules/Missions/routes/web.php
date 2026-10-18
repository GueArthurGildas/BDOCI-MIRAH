<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Missions\Http\Controllers\MissionController;

Route::middleware(['auth', 'can:'.Permission::MissionsView->value])
    ->prefix('missions')
    ->name('missions.')
    ->controller(MissionController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('{mission}', 'show')->name('show');
    });
