<?php

namespace App\Domain\Library\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Carries ids only: the entry no longer exists when listeners run.
 */
final class GameRemovedFromLibrary implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly int $userId,
        public readonly int $gameId,
    ) {}
}
