<?php

namespace App\Domain\Library\Queries;

use App\Domain\Library\Enums\GameStatus;
use App\Domain\Library\Models\LibraryEntry;
use App\Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Public, read-only view of what a user is playing right now. Other modules
 * (Journal, later Stats) use this instead of querying library tables.
 */
final class PlayingGamesQuery
{
    /**
     * Most recently started first; entries without a start date go last.
     *
     * @return Collection<int, LibraryEntry>
     */
    public function forUser(User $user, int $limit = 6): Collection
    {
        return LibraryEntry::query()
            ->whereBelongsTo($user)
            ->where('status', GameStatus::Playing)
            ->with(['game', 'platform'])
            ->orderByRaw('started_on DESC NULLS LAST')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }
}
