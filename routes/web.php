<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\SocialLoginController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['guest', 'throttle:10,1'])
    ->prefix('auth/{provider}')
    ->whereIn('provider', config('auth.social_providers'))
    ->controller(SocialLoginController::class)
    ->group(function () {
        Route::get('redirect', 'redirect')->name('auth.redirect');
        Route::get('callback', 'callback')->name('auth.callback');
    });

Route::post('logout', LogoutController::class)
    ->middleware('auth')
    ->name('logout');
