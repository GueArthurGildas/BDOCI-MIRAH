<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Missions\Http\Controllers\MissionController;
use Modules\Missions\Http\Controllers\MissionRecordController;

Route::middleware('auth')->prefix('missions')->name('missions.')->group(function () {
    Route::middleware('can:'.Permission::MissionsCreate->value)->group(function () {
        Route::get('create', [MissionController::class, 'create'])->name('create');
        Route::post('/', [MissionController::class, 'store'])->name('store');
    });

    Route::middleware('can:'.Permission::MissionsView->value)->group(function () {
        Route::get('/', [MissionController::class, 'index'])->name('index');
        Route::get('{mission}', [MissionController::class, 'show'])->whereNumber('mission')->name('show');
    });

    Route::post('{mission}/authorize', [MissionRecordController::class, 'authorizeMission'])->can(Permission::MissionsAuthorize->value)->whereNumber('mission')->name('authorize');

    Route::middleware('can:'.Permission::MissionsManage->value)->group(function () {
        Route::post('{mission}/start', [MissionRecordController::class, 'start'])->whereNumber('mission')->name('start');
        Route::post('{mission}/complete', [MissionRecordController::class, 'complete'])->whereNumber('mission')->name('complete');
        Route::post('{mission}/close', [MissionRecordController::class, 'close'])->whereNumber('mission')->name('close');
        Route::post('{mission}/review', [MissionRecordController::class, 'review'])->whereNumber('mission')->name('review');
        Route::post('{mission}/participants', [MissionRecordController::class, 'participant'])->whereNumber('mission')->name('participants.store');
        Route::post('{mission}/assets', [MissionRecordController::class, 'asset'])->whereNumber('mission')->name('assets.store');
        Route::put('assets/{missionAsset}/usage', [MissionRecordController::class, 'usage'])->whereNumber('missionAsset')->name('assets.usage');
        Route::put('{mission}/briefing', [MissionRecordController::class, 'briefing'])->whereNumber('mission')->name('briefing.save');
        Route::put('{mission}/debriefing', [MissionRecordController::class, 'debriefing'])->whereNumber('mission')->name('debriefing.save');
        Route::post('{mission}/events', [MissionRecordController::class, 'event'])->whereNumber('mission')->name('events.store');
        Route::post('{mission}/units', [MissionRecordController::class, 'unit'])->whereNumber('mission')->name('units.store');
        Route::post('{mission}/track', [MissionRecordController::class, 'track'])->whereNumber('mission')->name('track.store');
        Route::post('{mission}/observers', [MissionRecordController::class, 'observer'])->whereNumber('mission')->name('observers.store');
        Route::post('catalog/assets', [MissionRecordController::class, 'storeAsset'])->name('catalog.assets.store');
    });
});
