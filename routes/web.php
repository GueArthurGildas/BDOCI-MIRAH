<?php

use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('locale/{locale}', LocaleController::class)
    ->whereIn('locale', config('app.supported_locales'))
    ->name('locale.update');
