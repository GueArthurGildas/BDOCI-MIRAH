<?php

use Illuminate\Support\Facades\Route;
use Modules\Profile\Http\Controllers\ProfileController;

Route::middleware('auth')->prefix('profile')->name('profile.')->controller(ProfileController::class)->group(function () {
    Route::get('/', 'show')->name('show');
    Route::post('avatar', 'updateAvatar')->name('avatar.update');
    Route::delete('avatar', 'destroyAvatar')->name('avatar.destroy');
    Route::get('avatar/{user}', 'avatar')->whereNumber('user')->name('avatar');
});
