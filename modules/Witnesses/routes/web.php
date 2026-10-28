<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Witnesses\Http\Controllers\DistributionController;
use Modules\Witnesses\Http\Controllers\WitnessReportController;

Route::middleware('auth')->prefix('witnesses')->name('witnesses.')->group(function () {
    Route::middleware('can:'.Permission::WitnessesManage->value)->group(function () {
        Route::get('distributions', [DistributionController::class, 'index'])->name('distributions.index');
        Route::post('distributions', [DistributionController::class, 'store'])->name('distributions.store');
        Route::put('{report}/usability', [WitnessReportController::class, 'usability'])->whereNumber('report')->name('usability.save');
        Route::put('{report}/investigation', [WitnessReportController::class, 'investigation'])->whereNumber('report')->name('investigation.save');
        Route::post('{report}/decision', [WitnessReportController::class, 'decide'])->whereNumber('report')->name('decision.store');
    });

    Route::middleware('can:'.Permission::WitnessesCreate->value)->group(function () {
        Route::get('create', [WitnessReportController::class, 'create'])->name('create');
        Route::post('/', [WitnessReportController::class, 'store'])->name('store');
        Route::put('{report}/operation', [WitnessReportController::class, 'operation'])->whereNumber('report')->name('operation.save');
    });

    Route::middleware('can:'.Permission::WitnessesView->value)->group(function () {
        Route::get('/', [WitnessReportController::class, 'index'])->name('index');
        Route::get('{report}', [WitnessReportController::class, 'show'])->whereNumber('report')->name('show');
    });
});
