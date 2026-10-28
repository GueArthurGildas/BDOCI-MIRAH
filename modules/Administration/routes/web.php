<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Administration\Http\Controllers\AuditLogController;
use Modules\Administration\Http\Controllers\ConnectorCommentController;
use Modules\Administration\Http\Controllers\ConnectorController;
use Modules\Administration\Http\Controllers\ConnectorIssueController;
use Modules\Administration\Http\Controllers\CustomFieldController;
use Modules\Administration\Http\Controllers\ExternalLinkController;
use Modules\Administration\Http\Controllers\GrantController;
use Modules\Administration\Http\Controllers\GroupController;
use Modules\Administration\Http\Controllers\ReferenceListController;
use Modules\Administration\Http\Controllers\ReferenceListValueController;
use Modules\Administration\Http\Controllers\RoleController;
use Modules\Administration\Http\Controllers\SettingsController;
use Modules\Administration\Http\Controllers\UserController;

Route::middleware('auth')->prefix('administration')->name('administration.')->group(function () {
    Route::middleware('can:'.Permission::UsersManage->value)->group(function () {
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
    });

    Route::middleware('can:'.Permission::RolesManage->value)->group(function () {
        Route::resource('roles', RoleController::class)->except(['show']);
        Route::resource('groups', GroupController::class)->except(['show']);
        Route::post('roles/{role}/grants', [GrantController::class, 'storeForRole'])->name('roles.grants.store');
        Route::post('groups/{group}/grants', [GrantController::class, 'storeForGroup'])->name('groups.grants.store');
        Route::delete('grants/{grant}', [GrantController::class, 'destroy'])->whereNumber('grant')->name('grants.destroy');
    });

    Route::middleware('can:'.Permission::SettingsManage->value)->group(function () {
        Route::resource('reference-lists', ReferenceListController::class)->except(['show', 'destroy'])->parameters(['reference-lists' => 'referenceList']);
        Route::post('reference-lists/{referenceList}/values', [ReferenceListValueController::class, 'store'])->name('reference-lists.values.store');
        Route::patch('reference-lists/{referenceList}/values/{value}', [ReferenceListValueController::class, 'update'])->scopeBindings()->name('reference-lists.values.update');
        Route::resource('custom-fields', CustomFieldController::class)->except(['show', 'destroy'])->parameters(['custom-fields' => 'customField']);
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
            Route::patch('{dataSource}/mode', [ConnectorController::class, 'changeMode'])->whereNumber('dataSource')->name('mode');
            Route::get('links', [ExternalLinkController::class, 'edit'])->name('links');
            Route::put('links', [ExternalLinkController::class, 'update'])->name('links.update');
        });

        Route::post('{dataSource}/sync', [ConnectorController::class, 'sync'])->can(Permission::ConnectorsSync->value)->whereNumber('dataSource')->name('connectors.sync');
    });
    Route::get('audit', AuditLogController::class)->can(Permission::AuditView->value)->name('audit');
});
