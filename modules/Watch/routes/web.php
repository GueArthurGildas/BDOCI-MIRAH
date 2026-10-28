<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Watch\Http\Controllers\BoardController;
use Modules\Watch\Http\Controllers\InstructionController;
use Modules\Watch\Http\Controllers\ObservationController;
use Modules\Watch\Http\Controllers\ShiftController;

Route::middleware('auth')
    ->prefix('watch')
    ->name('watch.')
    ->group(function () {
        Route::get('board', [BoardController::class, 'index'])->can(Permission::WatchView->value)->name('board.index');
        Route::post('board/director-list', [BoardController::class, 'addToDirectorList'])->can(Permission::WatchManage->value)->name('board.director-list');
        Route::post('board/enhanced-monitoring', [BoardController::class, 'placeUnderEnhancedMonitoring'])->can(Permission::WatchCreate->value)->name('board.enhanced-monitoring');
        Route::post('board/{entry}/close', [BoardController::class, 'close'])->can(Permission::WatchManage->value)->name('board.close');

        Route::get('observations', [ObservationController::class, 'index'])->can(Permission::WatchView->value)->name('observations.index');
        Route::get('observations/create', [ObservationController::class, 'create'])->can(Permission::WatchCreate->value)->name('observations.create');
        Route::post('observations', [ObservationController::class, 'store'])->can(Permission::WatchCreate->value)->name('observations.store');
        Route::patch('observations/{observation}', [ObservationController::class, 'updateStatus'])->can(Permission::WatchCreate->value)->name('observations.update');

        Route::get('rotations', [ShiftController::class, 'index'])->can(Permission::WatchView->value)->name('rotations.index');
        Route::post('rotations', [ShiftController::class, 'plan'])->can(Permission::WatchManage->value)->name('rotations.plan');
        Route::post('rotations/{rotation}/handover', [ShiftController::class, 'startHandover'])->can(Permission::WatchCreate->value)->name('rotations.handover');
        Route::post('relays/{relay}/acknowledge', [ShiftController::class, 'acknowledge'])->can(Permission::WatchCreate->value)->name('relays.acknowledge');

        Route::get('instructions', [InstructionController::class, 'index'])->can(Permission::WatchView->value)->name('instructions.index');
        Route::post('instructions', [InstructionController::class, 'store'])->can(Permission::WatchManage->value)->name('instructions.store');
        Route::post('instructions/{instruction}/end', [InstructionController::class, 'end'])->can(Permission::WatchManage->value)->name('instructions.end');
    });
