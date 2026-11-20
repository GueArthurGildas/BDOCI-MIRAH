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
        Route::post('{operation}/close', 'close')->can(Permission::OperationsClose->value)->name('close');
        Route::post('{operation}/events', 'storeEvent')->can(Permission::OperationsCreate->value)->name('events.store');
        Route::get('{operation}/word', 'word')->can(Permission::OperationsView->value)->name('word');
        Route::get('{operation}/pdf', 'pdf')->can(Permission::OperationsView->value)->name('pdf');
        Route::post('{operation}/documents', 'storeDocument')->can(Permission::OperationsCreate->value)->name('documents.store');
        Route::get('{operation}/documents/{document}', 'downloadDocument')->whereNumber('document')->can(Permission::OperationsView->value)->name('documents.download');
        Route::post('{operation}/instructions', 'storeInstruction')->can(Permission::OperationsManage->value)->name('instructions.store');
    });
