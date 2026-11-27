<?php

namespace App\Domain\Users\Data;

use Illuminate\Support\Str;

/**
 * What the Users domain needs from an OAuth provider, decoupled from
 * Socialite's types so actions can be reused and tested without it.
 */
final readonly class ProviderIdentity
{
    public string $email;

    public function __construct(
        public string $provider,
        public string $providerUserId,
        public string $name,
        string $email,
        public ?string $avatarUrl = null,
    ) {
        $this->email = Str::lower(trim($email));
    }
}
