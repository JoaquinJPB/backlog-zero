<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\Friends\ShowFriendsController;
use App\Http\Controllers\Games\GameSearchController;
use App\Http\Controllers\Games\ShowGameController;
use App\Http\Controllers\Journal\ShowJournalController;
use App\Http\Controllers\Library\LibraryEntryController;
use App\Http\Controllers\Library\ShowLibraryController;
use App\Http\Controllers\Onboarding\UsernameController;
use App\Http\Controllers\Profile\ShowProfileController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

// Search hits IGDB, so it needs an account and is rate limited per user.
// Declared before games/{game} so "search" is never read as a slug.
Route::get('games/search', GameSearchController::class)
    ->middleware(['auth', 'throttle:game-search'])
    ->name('games.search');

// Public: reads only our local catalog.
Route::get('games/{game}', ShowGameController::class)->name('games.show');

// Profile sections. Without a {user} the controller falls back to the
// signed-in user, so the own and the someone-else's version of a section
// share one controller. Who may look is UserPolicy::viewProfile.
// The :username key is only for URL generation and Wayfinder's types;
// resolving it is AppServiceProvider's explicit binding, which wins.
Route::middleware('auth')->group(function () {
    Route::get('friends', ShowFriendsController::class)->name('friends.index');

    Route::get('journal', ShowJournalController::class)->name('journal.index');
    Route::get('library', ShowLibraryController::class)->name('library.index');

    Route::get('u/{user:username}', ShowProfileController::class)->name('profile.show');
    Route::get('u/{user:username}/journal', ShowJournalController::class)->name('journal.show');
    Route::get('u/{user:username}/library', ShowLibraryController::class)->name('library.show');
});

Route::middleware(['auth', 'throttle:60,1'])
    ->controller(LibraryEntryController::class)
    ->group(function () {
        Route::post('games/{game}/library', 'store')->name('library.store');
        Route::patch('library/{entry}', 'update')->name('library.update');
        Route::delete('library/{entry}', 'destroy')->name('library.destroy');
    });

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

Route::middleware('auth')
    ->prefix('settings')
    ->name('settings.')
    ->controller(ProfileController::class)
    ->group(function () {
        Route::get('profile', 'edit')->name('profile');
        Route::patch('profile', 'update')->middleware('throttle:20,1')->name('profile.update');
    });
