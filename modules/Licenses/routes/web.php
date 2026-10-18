<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Licenses\Http\Controllers\LicenseController;

Route::get('licenses', [LicenseController::class, 'index'])
    ->middleware('auth')
    ->can(Permission::LicensesView->value)
    ->name('licenses.index');
