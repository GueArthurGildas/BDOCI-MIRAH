<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Operations\Http\Controllers\OperationController;

Route::middleware('auth')
    ->prefix('operations')
    ->name('operations.')
    ->controller(OperationController::class)
    ->group(function () {
        Route::get('/', 'index')->can(Permission::OperationsView->value)->name('index');
        Route::get('create', 'create')->can(Permission::OperationsCreate->value)->name('create');
        Route::post('/', 'store')->can(Permission::OperationsCreate->value)->name('store');
        Route::get('{operation}', 'show')->can(Permission::OperationsView->value)->name('show');
        Route::patch('{operation}', 'update')->can(Permission::OperationsManage->value)->name('update');
        Route::post('{operation}/close', 'close')->can(Permission::OperationsManage->value)->name('close');
        Route::post('{operation}/events', 'storeEvent')->can(Permission::OperationsCreate->value)->name('events.store');
        Route::post('{operation}/instructions', 'storeInstruction')->can(Permission::OperationsManage->value)->name('instructions.store');
    });
