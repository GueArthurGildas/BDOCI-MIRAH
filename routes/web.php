<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->middleware('auth')->name('home');

Route::get('locale/{locale}', LocaleController::class)
    ->whereIn('locale', config('app.supported_locales'))
    ->name('locale.update');
