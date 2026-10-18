<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Vessels\Http\Controllers\VesselController;

Route::middleware('auth')
    ->prefix('vessels')
    ->name('vessels.')
    ->controller(VesselController::class)
    ->group(function () {
        Route::get('/', 'index')->can(Permission::VesselsView->value)->name('index');
        Route::get('create', 'create')->can(Permission::VesselsManage->value)->name('create');
        Route::post('/', 'store')->can(Permission::VesselsManage->value)->name('store');
        Route::get('{vessel}', 'show')->can(Permission::VesselsView->value)->name('show');
        Route::get('{vessel}/edit', 'edit')->can(Permission::VesselsManage->value)->name('edit');
        Route::put('{vessel}', 'update')->can(Permission::VesselsManage->value)->name('update');
    });
