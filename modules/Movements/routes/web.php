<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Movements\Http\Controllers\MovementController;

Route::middleware('auth')
    ->prefix('movements')
    ->name('movements.')
    ->controller(MovementController::class)
    ->group(function () {
        Route::get('/', 'index')->can(Permission::MovementsView->value)->name('index');
        Route::get('create', 'create')->can(Permission::MovementsCreate->value)->name('create');
        Route::post('/', 'store')->can(Permission::MovementsCreate->value)->name('store');
    });
