<?php

namespace App\Domain\Users\Exceptions;

use RuntimeException;

/**
 * A new provider identity arrived with an email that already belongs to a
 * user. We refuse to auto-link: trusting another provider's email claim
 * would let whoever controls that provider account take over the user.
 */
final class EmailAlreadyRegistered extends RuntimeException
{
    public static function forProvider(string $provider): self
    {
        return new self("Email already registered; refusing to auto-link a new {$provider} identity.");
    }
}
