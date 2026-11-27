<?php

namespace App\Domain\Users\Actions;

use App\Domain\Users\Models\User;
use App\Domain\Users\Support\Username;

/**
 * Assumes the value was validated (format, reserved words, uniqueness).
 * The unique index still guards against a race between two requests.
 */
final class SetUsernameAction
{
    public function execute(User $user, string $username): User
    {
        $user->update(['username' => Username::normalize($username)]);

        return $user;
    }
}
