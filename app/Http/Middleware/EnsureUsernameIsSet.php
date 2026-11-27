<?php

namespace App\Http\Middleware;

use App\Domain\Users\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Users who signed in but haven't picked a username are sent to onboarding
 * before they can use anything else (except logging out).
 */
class EnsureUsernameIsSet
{
    /** @var list<string> */
    private const ALLOWED_ROUTES = ['onboarding.*', 'logout'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || $user->hasUsername() || $request->routeIs(...self::ALLOWED_ROUTES)) {
            return $next($request);
        }

        return redirect()->route('onboarding.username');
    }
}
