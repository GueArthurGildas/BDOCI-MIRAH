<?php

use App\Http\Controllers\LocaleController;
use App\Support\Navigation\Landing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', fn (Request $request) => redirect()->route(Landing::routeName($request->user())));

Route::get('locale/{locale}', LocaleController::class)
    ->whereIn('locale', config('app.supported_locales'))
    ->name('locale.update');
