<?php

namespace App\Domain\Library\Policies;

use App\Domain\Library\Models\LibraryEntry;
use App\Domain\Users\Models\User;

/**
 * Libraries are private for now: only the owner can see or change entries.
 */
class LibraryEntryPolicy
{
    public function update(User $user, LibraryEntry $entry): bool
    {
        return $entry->user_id === $user->id;
    }

    public function delete(User $user, LibraryEntry $entry): bool
    {
        return $entry->user_id === $user->id;
    }
}
