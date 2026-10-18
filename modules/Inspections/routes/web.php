<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Inspections\Http\Controllers\InspectionController;

Route::middleware(['auth', 'can:'.Permission::InspectionsView->value])
    ->prefix('inspections')
    ->name('inspections.')
    ->controller(InspectionController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('{inspection}', 'show')->name('show');
    });
