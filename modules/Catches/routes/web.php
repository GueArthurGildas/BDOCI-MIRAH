<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Catches\Http\Controllers\CatchDeclarationController;
use Modules\Catches\Http\Controllers\MirrorOperationController;

Route::middleware(['auth', 'can:'.Permission::CatchesView->value])
    ->prefix('catches')
    ->name('catches.')
    ->controller(CatchDeclarationController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
    });

Route::middleware(['auth', 'can:'.Permission::CatchesView->value])
    ->prefix('catches')
    ->name('catches.')
    ->controller(MirrorOperationController::class)
    ->group(function () {
        Route::get('landings', 'landings')->name('landings');
        Route::get('transshipments', 'transshipments')->name('transshipments');
    });
