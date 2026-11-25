<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Logbook\Http\Controllers\LogbookDayController;
use Modules\Logbook\Http\Controllers\LogbookEntryController;
use Modules\Logbook\Http\Controllers\ObservationController;
use Modules\Logbook\Http\Controllers\ShiftHandoverController;

Route::middleware('auth')
    ->prefix('logbook')
    ->name('logbook.')
    ->group(function () {
        Route::get('days', [LogbookDayController::class, 'index'])->can(Permission::LogbookView->value)->name('days.index');
        Route::get('days/today', [LogbookDayController::class, 'today'])->can(Permission::LogbookView->value)->name('days.today');
        Route::get('days/{day}', [LogbookDayController::class, 'show'])->can(Permission::LogbookView->value)->name('days.show');
        Route::get('days/{day}/pdf', [LogbookDayController::class, 'pdf'])->can(Permission::LogbookView->value)->name('days.pdf');
        Route::get('days/{day}/print', [LogbookDayController::class, 'print'])->can(Permission::LogbookView->value)->name('days.print');
        Route::get('days/{day}/word',[LogbookDayController::class, 'word'])->can(Permission::LogbookView->value)->name('days.word');
        Route::post('days/{day}/close', [LogbookDayController::class, 'close'])->can(Permission::LogbookClose->value)->name('days.close');
        Route::get('handovers/create', [ShiftHandoverController::class, 'create'])->can(Permission::LogbookHandover->value)->name('handovers.create');
        Route::post('handovers', [ShiftHandoverController::class, 'store'])->can(Permission::LogbookHandover->value)->name('handovers.store');
        Route::post('entries', [LogbookEntryController::class, 'store'])->can(Permission::LogbookCreate->value)->name('entries.store');
        Route::get('entries/{entry}/rectify', [LogbookEntryController::class, 'rectifyForm'])->can(Permission::LogbookCreate->value)->name('entries.rectify');
        Route::post('entries/{entry}/rectify', [LogbookEntryController::class, 'rectify'])->can(Permission::LogbookCreate->value)->name('entries.rectify.store');
        Route::patch('entries/{entry}', [LogbookEntryController::class, 'updateHandling'])->can(Permission::LogbookManage->value)->name('entries.update');
        Route::delete('entries/{entry}', [LogbookEntryController::class, 'destroy'])->can(Permission::LogbookDelete->value)->name('entries.destroy');
    });

Route::middleware('auth')
    ->prefix('logbook')
    ->name('logbook.')
    ->controller(ObservationController::class)
    ->group(function () {
        Route::get('/', 'index')->can(Permission::LogbookView->value)->name('index');
        Route::get('create', 'create')->can(Permission::LogbookCreate->value)->name('create');
        Route::post('/', 'store')->can(Permission::LogbookCreate->value)->name('store');
        Route::get('{observation}', 'show')->can(Permission::LogbookView->value)->name('show');
        Route::patch('{observation}', 'update')->can(Permission::LogbookManage->value)->name('update');
        Route::delete('{observation}', 'destroy')->can(Permission::LogbookDelete->value)->name('destroy');
    });
