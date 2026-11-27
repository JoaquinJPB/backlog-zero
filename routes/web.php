<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\Onboarding\UsernameController;
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

Route::middleware('auth')
    ->prefix('onboarding')
    ->name('onboarding.')
    ->controller(UsernameController::class)
    ->group(function () {
        Route::get('username', 'show')->name('username');
        Route::post('username', 'store')->middleware('throttle:20,1')->name('username.store');
    });
