<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Catches\Http\Controllers\CatchDeclarationController;

Route::middleware(['auth', 'can:'.Permission::CatchesView->value])
    ->prefix('catches')
    ->name('catches.')
    ->controller(CatchDeclarationController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
    });
