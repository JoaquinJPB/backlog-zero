<?php

namespace App\Domain\Users\Exceptions;

use RuntimeException;

/**
 * Sign-up is limited to `auth.allowed_emails` and this email isn't on it.
 */
final class RegistrationNotAllowed extends RuntimeException
{
    public static function forEmail(): self
    {
        // No email in the message: exceptions can end up in logs.
        return new self('This email is not allowed to create an account.');
    }
}
