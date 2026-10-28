<?php

use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\AiAgentController;
use Modules\Reports\Http\Controllers\ArchiveController;
use Modules\Reports\Http\Controllers\ExportController;
use Modules\Reports\Http\Controllers\ScheduledReportController;

Route::middleware('auth')->prefix('reports')->name('reports.')->group(function () {
    Route::controller(ScheduledReportController::class)->group(function () {
        Route::get('/', 'index')->can(Permission::ReportsView->value)->name('index');
        Route::post('/', 'store')->can(Permission::ReportsManage->value)->name('store');
        Route::put('{scheduledReport}', 'update')->can(Permission::ReportsManage->value)->name('update');
        Route::post('{scheduledReport}/generate', 'generate')->can(Permission::ReportsGenerate->value)->name('generate');
        Route::get('documents/{document}', 'download')->can(Permission::ReportsView->value)->name('documents.download');
    });

    Route::controller(ExportController::class)->prefix('exports')->name('exports.')->group(function () {
        Route::get('/', 'index')->can(Permission::ReportsView->value)->name('index');
        Route::get('{type}', 'download')->name('download');
    });

    Route::controller(ArchiveController::class)->prefix('archives')->name('archives.')->group(function () {
        Route::get('/', 'index')->can(Permission::ReportsView->value)->name('index');
        Route::post('run', 'run')->can(Permission::ReportsArchive->value)->name('run');
    });

    Route::get('ai-agent', [AiAgentController::class, 'index'])->can(Permission::ReportsView->value)->name('ai-agent.index');
});
