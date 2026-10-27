<?php

use Illuminate\Support\Facades\Route;
use Modules\Notifications\Http\Controllers\NotificationController;

Route::middleware('auth')
    ->prefix('notifications')
    ->name('notifications.')
    ->controller(NotificationController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('feed', 'feed')->name('feed');
        Route::post('read-all', 'readAll')->name('read-all');
        Route::get('{notification}', 'open')->whereUuid('notification')->name('open');
    });
