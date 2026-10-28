<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Tracking\Http\Controllers\AlertController;
use Modules\Tracking\Http\Controllers\AlertRuleController;
use Modules\Tracking\Http\Controllers\SlotController;
use Modules\Tracking\Http\Controllers\TrackingController;

Route::middleware(['auth', 'can:'.Permission::TrackingView->value])
    ->prefix('tracking')
    ->name('tracking.')
    ->group(function () {
        Route::controller(TrackingController::class)->group(function () {
            Route::get('map', 'map')->name('map');
            Route::get('zones', 'zones')->name('zones');
            Route::get('beacons', 'beacons')->name('beacons');
        });

        Route::get('alerts', [AlertController::class, 'index'])->name('alerts.index');
        Route::get('alerts/{alert}', [AlertController::class, 'show'])->name('alerts.show');
        Route::post('alerts/{alert}/acknowledge', [AlertController::class, 'acknowledge'])->can(Permission::TrackingCreate->value)->name('alerts.acknowledge');
        Route::post('alerts/{alert}/handle', [AlertController::class, 'handle'])->can(Permission::TrackingCreate->value)->name('alerts.handle');

        Route::get('slots', [SlotController::class, 'index'])->name('slots.index');
        Route::post('slots', [SlotController::class, 'store'])->can(Permission::TrackingCreate->value)->name('slots.store');
        Route::get('slots/{slot}', [SlotController::class, 'show'])->name('slots.show');
        Route::post('slots/{slot}/positions/{position}/confirm', [SlotController::class, 'confirm'])->can(Permission::TrackingCreate->value)->name('slots.confirm');
        Route::post('slots/{slot}/sweep', [SlotController::class, 'sweep'])->can(Permission::TrackingCreate->value)->name('slots.sweep');
    });

Route::middleware(['auth', 'can:'.Permission::SettingsManage->value])
    ->prefix('tracking')
    ->name('tracking.')
    ->group(function () {
        Route::get('rules', [AlertRuleController::class, 'index'])->name('rules.index');
        Route::post('rules', [AlertRuleController::class, 'store'])->name('rules.store');
        Route::put('rules/{rule}', [AlertRuleController::class, 'update'])->name('rules.update');
    });
