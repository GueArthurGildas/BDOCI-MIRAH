<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Administration\Http\Controllers\AuditLogController;
use Modules\Administration\Http\Controllers\ConnectorCommentController;
use Modules\Administration\Http\Controllers\ConnectorController;
use Modules\Administration\Http\Controllers\ConnectorIssueController;
use Modules\Administration\Http\Controllers\RoleController;
use Modules\Administration\Http\Controllers\SettingsController;
use Modules\Administration\Http\Controllers\UserController;

Route::middleware('auth')->prefix('administration')->name('administration.')->group(function () {
    Route::middleware('can:'.Permission::UsersManage->value)->group(function () {
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
    });

    Route::middleware('can:'.Permission::RolesManage->value)->group(function () {
        Route::resource('roles', RoleController::class)->except(['show']);
    });

    Route::middleware('can:'.Permission::SettingsManage->value)->prefix('settings')->controller(SettingsController::class)->group(function () {
        Route::get('/', 'edit')->name('settings');
        Route::put('/', 'update')->name('settings.update');
        Route::delete('/', 'reset')->name('settings.reset');
    });

    Route::prefix('connectors')->group(function () {
        Route::middleware('can:'.Permission::ConnectorsView->value)->controller(ConnectorController::class)->group(function () {
            Route::get('/', 'index')->name('connectors');
            Route::get('{dataSource}', 'show')->whereNumber('dataSource')->name('connectors.show');
        });

        Route::middleware('can:'.Permission::ConnectorsManage->value)->name('connectors.')->group(function () {
            Route::post('{dataSource}/comments', [ConnectorCommentController::class, 'store'])->whereNumber('dataSource')->name('comments.store');
            Route::post('errors/{error}/resolve', [ConnectorIssueController::class, 'resolveError'])->whereNumber('error')->name('errors.resolve');
            Route::post('errors/{error}/reopen', [ConnectorIssueController::class, 'reopenError'])->whereNumber('error')->name('errors.reopen');
            Route::patch('rejections/{rejection}', [ConnectorIssueController::class, 'handleRejection'])->whereNumber('rejection')->name('rejections.handle');
        });
    });
    Route::get('audit', AuditLogController::class)->can(Permission::AuditView->value)->name('audit');
});
