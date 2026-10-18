<?php

use Illuminate\Support\Facades\Route;
use Modules\Search\Http\Controllers\SearchController;

Route::get('search', SearchController::class)->middleware('auth')->name('search');
