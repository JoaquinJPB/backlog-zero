<?php

namespace App\Domain\Users\Actions;

use App\Domain\Users\Models\User;
use App\Domain\Users\Support\Username;

/**
 * Assumes validated input. Email isn't editable: it comes from the provider.
 */
final class UpdateProfileAction
{
    public function execute(User $user, string $name, string $username): User
    {
        $user->update([
            'name' => trim($name),
            'username' => Username::normalize($username),
        ]);

        return $user;
    }
}
