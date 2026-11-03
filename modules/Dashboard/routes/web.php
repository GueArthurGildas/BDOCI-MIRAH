<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\DashboardController;
use Modules\Dashboard\Http\Controllers\WidgetController;

Route::middleware(['auth', 'can:'.Permission::DashboardView->value])->prefix('dashboard')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('customize', [WidgetController::class, 'edit'])->name('dashboard.customize');
    Route::put('widgets', [WidgetController::class, 'update'])->name('dashboard.widgets.update');
    Route::delete('widgets', [WidgetController::class, 'reset'])->name('dashboard.widgets.reset');
    Route::get('widgets/{widget}', [WidgetController::class, 'show'])->name('dashboard.widgets.show');
});
