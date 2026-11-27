<?php

use App\Http\Middleware\EnsureUsernameIsSet;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            EnsureUsernameIsSet::class,
        ]);

        // Production runs behind Render's TLS-terminating proxy, the only way
        // in. Trusting it makes Laravel see https (OAuth callback URLs,
        // secure cookies). Harmless locally: nothing sends these headers.
        $middleware->trustProxies(at: '*');

        // There is no login page: signing in starts from the home page.
        $middleware->redirectGuestsTo('/');
        $middleware->redirectUsersTo('/');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
