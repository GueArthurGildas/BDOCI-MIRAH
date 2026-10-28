<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Administration\Http\Controllers\AuditLogController;
use Modules\Administration\Http\Controllers\ConnectorController;
use Modules\Administration\Http\Controllers\RoleController;
use Modules\Administration\Http\Controllers\SettingsController;
use Modules\Administration\Http\Controllers\UserController;

Route::middleware('auth')->prefix('administration')->name('administration.')->group(function () {
    Route::middleware('can:'.Permission::UsersManage->value)->group(function () {
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::get('roles', RoleController::class)->name('roles');
    });

    Route::middleware('can:'.Permission::SettingsManage->value)->group(function () {
        Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    });

    Route::get('connectors', ConnectorController::class)->can(Permission::ConnectorsView->value)->name('connectors');
    Route::get('audit', AuditLogController::class)->can(Permission::AuditView->value)->name('audit');
});
