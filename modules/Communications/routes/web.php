<?php

use App\Support\Authorization\Permission;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;
use Modules\Communications\Http\Controllers\AiAgentController;
use Modules\Communications\Http\Controllers\ChannelController;
use Modules\Communications\Http\Controllers\DeliveryReportController;
use Modules\Communications\Http\Controllers\MessageController;
use Modules\Communications\Http\Controllers\NoteController;
use Modules\Communications\Http\Controllers\RuleController;
use Modules\Communications\Http\Controllers\TemplateController;

Route::middleware('auth')->prefix('communications')->name('communications.')->group(function () {
    Route::controller(MessageController::class)->prefix('messages')->name('messages.')->group(function () {
        Route::get('/', 'index')->can(Permission::CommunicationsView->value)->name('index');
        Route::get('create', 'create')->can(Permission::CommunicationsSend->value)->name('create');
        Route::post('/', 'store')->can(Permission::CommunicationsSend->value)->name('store');
        Route::get('{message}', 'show')->can(Permission::CommunicationsView->value)->name('show');
        Route::post('{message}/send', 'send')->can(Permission::CommunicationsSend->value)->name('send');
        Route::post('{message}/acknowledge', 'acknowledge')->can(Permission::CommunicationsSend->value)->name('acknowledge');
    });

    Route::controller(NoteController::class)->prefix('notes')->name('notes.')->group(function () {
        Route::get('/', 'index')->can(Permission::CommunicationsView->value)->name('index');
        Route::get('create', 'create')->can(Permission::CommunicationsCreateNote->value)->name('create');
        Route::post('/', 'store')->can(Permission::CommunicationsCreateNote->value)->name('store');
        Route::post('ai', 'requestAi')->can(Permission::CommunicationsTriggerAiAgent->value)->name('ai');
        Route::post('{note}/read', 'read')->can(Permission::CommunicationsView->value)->name('read');
    });

    Route::controller(TemplateController::class)->prefix('templates')->name('templates.')->group(function () {
        Route::get('/', 'index')->can(Permission::CommunicationsView->value)->name('index');

        Route::middleware('can:'.Permission::CommunicationsManageTemplates->value)->group(function () {
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{template}/edit', 'edit')->name('edit');
            Route::put('{template}', 'update')->name('update');
        });
    });

    Route::controller(RuleController::class)->prefix('rules')->name('rules.')->group(function () {
        Route::get('/', 'index')->can(Permission::CommunicationsView->value)->name('index');

        Route::middleware('can:'.Permission::CommunicationsManageDispatch->value)->group(function () {
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{rule}/edit', 'edit')->name('edit');
            Route::put('{rule}', 'update')->name('update');
        });
    });

    Route::controller(ChannelController::class)->prefix('channels')->name('channels.')->group(function () {
        Route::get('/', 'index')->can(Permission::CommunicationsView->value)->name('index');
        Route::put('connector', 'updateConnector')->can(Permission::ConnectorsManage->value)->name('connector');
        Route::put('{channel}', 'updateChannel')->can(Permission::CommunicationsManageDispatch->value)->name('update');
        Route::post('consents', 'storeConsent')->can(Permission::CommunicationsSend->value)->name('consents.store');
        Route::delete('consents', 'revokeConsent')->can(Permission::CommunicationsSend->value)->name('consents.destroy');
    });
});

Route::post('communications/webhooks/delivery', DeliveryReportController::class)->withoutMiddleware([PreventRequestForgery::class])->name('communications.webhooks.delivery');

Route::middleware(['auth', 'can:'.Permission::SettingsManage->value])->prefix('communications/ai-agent')->name('communications.ai-agent.')->controller(AiAgentController::class)->group(function () {
    Route::get('/', 'edit')->name('edit');
    Route::put('/', 'update')->name('update');
});
