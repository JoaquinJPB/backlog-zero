<?php

namespace App\Domain\Users\Actions;

use App\Domain\Users\Data\ProviderIdentity;
use App\Domain\Users\Events\UserRegistered;
use App\Domain\Users\Exceptions\EmailAlreadyRegistered;
use App\Domain\Users\Models\SocialAccount;
use App\Domain\Users\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Finds the user behind a provider identity, registering them on first
 * sign-in. Identities are matched by (provider, provider_user_id) only.
 */
final class AuthenticateWithProviderAction
{
    /**
     * @throws EmailAlreadyRegistered
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

    private function refreshAvatar(User $user, ProviderIdentity $identity): User
    {
        if ($identity->avatarUrl !== null && $identity->avatarUrl !== $user->avatar_url) {
            $user->update(['avatar_url' => $identity->avatarUrl]);
        }

        return $user;
    }
}
