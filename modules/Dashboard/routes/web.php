<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\DashboardController;

Route::get('dashboard', DashboardController::class)
    ->middleware('auth')
    ->can(Permission::DashboardView->value)
    ->name('dashboard');
