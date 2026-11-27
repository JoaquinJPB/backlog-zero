<?php

namespace App\Domain\Users\Actions;

use App\Domain\Users\Data\ProviderIdentity;
use App\Domain\Users\Events\UserRegistered;
use App\Domain\Users\Exceptions\EmailAlreadyRegistered;
use App\Domain\Users\Exceptions\RegistrationNotAllowed;
use App\Domain\Users\Models\SocialAccount;
use App\Domain\Users\Models\User;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\DB;

/**
 * Finds the user behind a provider identity, registering them on first
 * sign-in. Identities are matched by (provider, provider_user_id) only.
 * New accounts are limited to `auth.allowed_emails` when that list is set.
 */
final class AuthenticateWithProviderAction
{
    /**
     * @param  list<string>  $allowedEmails  lowercase; empty means open sign-up
     */
    public function __construct(
        #[Config('auth.allowed_emails')] private readonly array $allowedEmails = [],
    ) {}

    /**
     * @throws EmailAlreadyRegistered
     * @throws RegistrationNotAllowed
     */
    public function execute(ProviderIdentity $identity): User
    {
        $account = SocialAccount::query()
            ->with('user')
            ->where('provider', $identity->provider)
            ->where('provider_user_id', $identity->providerUserId)
            ->first();

        if ($account !== null) {
            return $this->refreshAvatar($account->user, $identity);
        }

        if (! $this->mayRegister($identity->email)) {
            throw RegistrationNotAllowed::forEmail();
        }

        if (User::query()->where('email', $identity->email)->exists()) {
            throw EmailAlreadyRegistered::forProvider($identity->provider);
        }

        $user = DB::transaction(function () use ($identity): User {
            $user = User::query()->create([
                'name' => $identity->name,
                'email' => $identity->email,
                'avatar_url' => $identity->avatarUrl,
            ]);

            $user->socialAccounts()->create([
                'provider' => $identity->provider,
                'provider_user_id' => $identity->providerUserId,
            ]);

            return $user;
        });

        UserRegistered::dispatch($user, $identity->provider);

        return $user;
    }

    private function mayRegister(string $email): bool
    {
        return $this->allowedEmails === [] || in_array($email, $this->allowedEmails, true);
    }

    private function refreshAvatar(User $user, ProviderIdentity $identity): User
    {
        if ($identity->avatarUrl !== null && $identity->avatarUrl !== $user->avatar_url) {
            $user->update(['avatar_url' => $identity->avatarUrl]);
        }

        return $user;
    }
}
