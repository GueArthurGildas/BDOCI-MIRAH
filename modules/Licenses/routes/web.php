<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Licenses\Http\Controllers\LicenseController;

Route::middleware('auth')->prefix('licenses')->name('licenses.')->controller(LicenseController::class)->group(function () {
    Route::get('/', 'index')->can(Permission::LicensesView->value)->name('index');

    Route::middleware('can:'.Permission::LicensesManage->value)->group(function () {
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
    });
});
