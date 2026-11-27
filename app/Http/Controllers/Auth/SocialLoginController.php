<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Users\Actions\AuthenticateWithProviderAction;
use App\Domain\Users\Data\ProviderIdentity;
use App\Domain\Users\Exceptions\EmailAlreadyRegistered;
use App\Domain\Users\Exceptions\RegistrationNotAllowed;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class SocialLoginController extends Controller
{
    public function redirect(string $provider): SymfonyRedirectResponse
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback(
        Request $request,
        string $provider,
        AuthenticateWithProviderAction $authenticate,
    ): RedirectResponse {
        try {
            $identity = $this->toIdentity($provider, Socialite::driver($provider)->user());
        } catch (Exception $e) {
            // Cancelled consent, expired state, provider outage... Log it, but
            // the user only needs to know the sign-in didn't work.
            report($e);

            return $this->failed('ui.auth.failed');
        }

        if ($identity === null) {
            return $this->failed('ui.auth.missing_email');
        }

        try {
            $user = $authenticate->execute($identity);
        } catch (EmailAlreadyRegistered) {
            return $this->failed('ui.auth.email_taken');
        } catch (RegistrationNotAllowed) {
            return $this->failed('ui.auth.not_invited');
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    private function toIdentity(string $provider, SocialiteUser $socialUser): ?ProviderIdentity
    {
        $email = $socialUser->getEmail();

        if ($email === null || $email === '') {
            return null;
        }

        return new ProviderIdentity(
            provider: $provider,
            providerUserId: (string) $socialUser->getId(),
            name: $socialUser->getName() ?: $socialUser->getNickname() ?: $email,
            email: $email,
            avatarUrl: $socialUser->getAvatar(),
        );
    }

    private function failed(string $messageKey): RedirectResponse
    {
        Inertia::flash('error', __($messageKey));

        return redirect()->route('home');
    }
}
