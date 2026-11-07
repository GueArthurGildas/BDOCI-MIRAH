<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Vessels\Http\Controllers\VesselController;
use Modules\Vessels\Http\Controllers\VesselDuplicateController;
use Modules\Vessels\Http\Controllers\VesselIngestController;
use Modules\Vessels\Http\Controllers\VesselPhotoController;
use Modules\Vessels\Http\Controllers\VesselRegistryController;

Route::middleware('auth')
    ->prefix('vessels')
    ->name('vessels.')
    ->group(function () {
        Route::get('duplicates', [VesselDuplicateController::class, 'index'])->can(Permission::VesselsMatch->value)->name('duplicates.index');
        Route::post('duplicates/{source}/settle', [VesselDuplicateController::class, 'settle'])->can(Permission::VesselsMatch->value)->name('duplicates.settle');
        Route::get('ingest', [VesselIngestController::class, 'create'])->can(Permission::VesselsManage->value)->name('ingest.create');
        Route::post('ingest', [VesselIngestController::class, 'store'])->can(Permission::VesselsManage->value)->name('ingest.store');
        Route::post('{vessel}/identifiers', [VesselRegistryController::class, 'storeIdentifier'])->can(Permission::VesselsManage->value)->name('identifiers.store');
        Route::post('{vessel}/actors', [VesselRegistryController::class, 'storeActor'])->can(Permission::VesselsManage->value)->name('actors.store');
        Route::post('{vessel}/external-lists', [VesselRegistryController::class, 'storeList'])->can(Permission::VesselsManage->value)->name('external-lists.store');
        Route::post('{vessel}/photos', [VesselPhotoController::class, 'store'])->can(Permission::VesselsManage->value)->name('photos.store');
        Route::delete('{vessel}/photos/{document}', [VesselPhotoController::class, 'destroy'])->can(Permission::VesselsManage->value)->whereNumber('document')->name('photos.destroy');
    });

Route::middleware('auth')
    ->prefix('vessels')
    ->name('vessels.')
    ->controller(VesselController::class)
    ->group(function () {
        Route::get('/', 'index')->can(Permission::VesselsView->value)->name('index');
        Route::get('{vessel}/quick', 'quick')->can(Permission::VesselsView->value)->name('quick');
        Route::get('{vessel}/sheet', 'sheet')->can(Permission::VesselsView->value)->name('sheet');
        Route::get('{vessel}/photos/{document}', [VesselPhotoController::class, 'show'])->can(Permission::VesselsView->value)->whereNumber('document')->name('photos.show');
        Route::get('create', 'create')->can(Permission::VesselsManage->value)->name('create');
        Route::post('/', 'store')->can(Permission::VesselsManage->value)->name('store');
        Route::get('{vessel}', 'show')->can(Permission::VesselsView->value)->name('show');
        Route::get('{vessel}/edit', 'edit')->can(Permission::VesselsManage->value)->name('edit');
        Route::put('{vessel}', 'update')->can(Permission::VesselsManage->value)->name('update');
    });
