<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Mrep\Http\Controllers\DpepController;
use Modules\Mrep\Http\Controllers\MirrorListController;

Route::middleware(['auth', 'can:'.Permission::MrepView->value])
    ->prefix('mrep')
    ->name('mrep.')
    ->group(function () {
        Route::get('/', [DpepController::class, 'index'])->name('dpeps.index');
        Route::get('dpeps/{dpep}', [DpepController::class, 'show'])->whereNumber('dpep')->name('dpeps.show');
        Route::get('gies-refusals', [MirrorListController::class, 'refusals'])->name('refusals');
        Route::get('targetings', [MirrorListController::class, 'targetings'])->name('targetings');
    });
