<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Logbook\Http\Controllers\ObservationController;

Route::middleware('auth')
    ->prefix('logbook')
    ->name('logbook.')
    ->controller(ObservationController::class)
    ->group(function () {
        Route::get('/', 'index')->can(Permission::LogbookView->value)->name('index');
        Route::get('create', 'create')->can(Permission::LogbookCreate->value)->name('create');
        Route::post('/', 'store')->can(Permission::LogbookCreate->value)->name('store');
        Route::get('{observation}', 'show')->can(Permission::LogbookView->value)->name('show');
        Route::patch('{observation}', 'update')->can(Permission::LogbookManage->value)->name('update');
    });
