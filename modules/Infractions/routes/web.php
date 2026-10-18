<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Infractions\Http\Controllers\InfractionController;

Route::middleware(['auth', 'can:'.Permission::InfractionsView->value])
    ->prefix('infractions')
    ->name('infractions.')
    ->controller(InfractionController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('{infraction}', 'show')->name('show');
        Route::patch('{infraction}', 'update')->can(Permission::InfractionsManage->value)->name('update');
    });
