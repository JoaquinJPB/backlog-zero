<?php

namespace App\Domain\Library\Events;

use App\Domain\Library\Models\LibraryEntry;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Raised when a rating is set or changed (not when it's cleared).
 */
final class GameRated implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly LibraryEntry $entry,
        public readonly ?int $previousRating,
    ) {}
}
