<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Registry\Http\Controllers\RegistryController;
use Modules\Registry\Http\Controllers\SourcePriorityController;

Route::middleware('auth')
    ->prefix('registry')
    ->name('registry.')
    ->group(function () {
        Route::get('/', [RegistryController::class, 'index'])->can(Permission::RegistryView->value)->name('index');
        Route::get('source-priorities', [SourcePriorityController::class, 'edit'])->can(Permission::RegistryManage->value)->name('priorities.edit');
        Route::put('source-priorities', [SourcePriorityController::class, 'update'])->can(Permission::RegistryManage->value)->name('priorities.update');

        Route::controller(RegistryController::class)->group(function () {
            Route::get('{resource}', 'list')->can(Permission::RegistryView->value)->name('list');
            Route::get('{resource}/create', 'create')->can(Permission::RegistryManage->value)->name('create');
            Route::post('{resource}', 'store')->can(Permission::RegistryManage->value)->name('store');
            Route::get('{resource}/{id}/edit', 'edit')->can(Permission::RegistryManage->value)->whereNumber('id')->name('edit');
            Route::put('{resource}/{id}', 'update')->can(Permission::RegistryManage->value)->whereNumber('id')->name('update');
        });
    });
