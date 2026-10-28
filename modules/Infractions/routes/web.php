<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Infractions\Http\Controllers\CaseFileController;
use Modules\Infractions\Http\Controllers\InfractionController;
use Modules\Infractions\Http\Controllers\InfractionTypeController;

Route::middleware('auth')->prefix('infractions')->name('infractions.')->group(function () {
    Route::middleware('can:'.Permission::InfractionsManageTypes->value)->prefix('types')->name('types.')->group(function () {
        Route::get('/', [InfractionTypeController::class, 'index'])->name('index');
        Route::post('/', [InfractionTypeController::class, 'store'])->name('store');
        Route::put('{type}', [InfractionTypeController::class, 'update'])->whereNumber('type')->name('update');
    });

    Route::middleware('can:'.Permission::InfractionsView->value)->group(function () {
        Route::get('/', [InfractionController::class, 'index'])->name('index');
        Route::get('{case}', [InfractionController::class, 'show'])->whereNumber('case')->name('show');
        Route::get('{case}/documents/{document}', [CaseFileController::class, 'download'])->whereNumber(['case', 'document'])->name('documents.download');
    });

    Route::middleware('can:'.Permission::InfractionsCreate->value)->group(function () {
        Route::get('create', [InfractionController::class, 'create'])->name('create');
        Route::post('/', [InfractionController::class, 'store'])->name('store');
    });

    Route::middleware('can:'.Permission::InfractionsManage->value)->group(function () {
        Route::patch('{case}', [InfractionController::class, 'update'])->whereNumber('case')->name('update');
        Route::post('{case}/upheld', [CaseFileController::class, 'uphold'])->whereNumber('case')->name('upheld.store');
        Route::post('{case}/evidence', [CaseFileController::class, 'evidence'])->whereNumber('case')->name('evidence.store');
        Route::post('{case}/seals', [CaseFileController::class, 'seal'])->whereNumber('case')->name('seals.store');
        Route::post('{case}/seizures', [CaseFileController::class, 'seizure'])->whereNumber('case')->name('seizures.store');
        Route::post('{case}/measures', [CaseFileController::class, 'proposeMeasure'])->whereNumber('case')->name('measures.store');
        Route::post('{case}/violation-report', [CaseFileController::class, 'violationReport'])->whereNumber('case')->name('violation-report.store');
    });

    Route::post('measures/{measure}/decide', [CaseFileController::class, 'decideMeasure'])->can(Permission::InfractionsDecideMeasures->value)->whereNumber('measure')->name('measures.decide');

    Route::middleware('can:'.Permission::InfractionsDecide->value)->group(function () {
        Route::post('{case}/follow-ups', [CaseFileController::class, 'followUp'])->whereNumber('case')->name('follow-ups.store');
        Route::post('follow-ups/{followUp}/decisions', [CaseFileController::class, 'decision'])->whereNumber('followUp')->name('decisions.store');
        Route::post('decisions/{decision}/payments', [CaseFileController::class, 'payment'])->whereNumber('decision')->name('payments.store');
        Route::post('decisions/{decision}/appeals', [CaseFileController::class, 'appeal'])->whereNumber('decision')->name('appeals.store');
        Route::patch('appeals/{appeal}', [CaseFileController::class, 'appealOutcome'])->whereNumber('appeal')->name('appeals.outcome');
        Route::post('{case}/close', [CaseFileController::class, 'close'])->whereNumber('case')->name('close');
    });
});
