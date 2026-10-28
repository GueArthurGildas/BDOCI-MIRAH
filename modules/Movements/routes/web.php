<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Movements\Http\Controllers\EezDeclarationController;
use Modules\Movements\Http\Controllers\EntryExitAlertController;
use Modules\Movements\Http\Controllers\MissedDeclarationController;
use Modules\Movements\Http\Controllers\MovementController;

Route::middleware('auth')
    ->prefix('eez-declarations')
    ->name('movements.eez.')
    ->group(function () {
        $view = Permission::MovementsView->value;
        $declare = Permission::MovementsDeclare->value;

        Route::controller(EntryExitAlertController::class)->group(function () use ($view, $declare) {
            Route::get('alerts', 'index')->can($view)->name('alerts');
            Route::post('alerts/{alert}/close', 'close')->can($declare)->name('alerts.close');
        });

        Route::controller(MissedDeclarationController::class)->prefix('missed')->name('missed.')->group(function () use ($view, $declare) {
            Route::get('/', 'index')->can($view)->name('index');
            Route::post('{missed}/qualify', 'qualify')->can($declare)->name('qualify');
            Route::post('{missed}/remind', 'remind')->can($declare)->name('remind');
        });

        Route::controller(EezDeclarationController::class)->group(function () use ($view, $declare) {
            Route::get('/', 'index')->can($view)->name('index');
            Route::get('create', 'create')->can($declare)->name('create');
            Route::post('/', 'store')->can($declare)->name('store');
            Route::get('{declaration}', 'show')->can($view)->name('show');
            Route::post('{declaration}/acknowledge', 'acknowledge')->can($declare)->name('acknowledge');
            Route::post('{declaration}/close', 'close')->can($declare)->name('close');
            Route::post('links/{link}/explain', 'explain')->can($declare)->name('explain');
        });
    });

Route::middleware('auth')
    ->prefix('movements')
    ->name('movements.')
    ->controller(MovementController::class)
    ->group(function () {
        Route::get('/', 'index')->can(Permission::MovementsView->value)->name('index');
        Route::get('create', 'create')->can(Permission::MovementsCreate->value)->name('create');
        Route::post('/', 'store')->can(Permission::MovementsCreate->value)->name('store');
    });
