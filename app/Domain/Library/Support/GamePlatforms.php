<?php

namespace App\Domain\Library\Support;

use App\Domain\Games\Models\Game;
use InvalidArgumentException;

/**
 * Entries may only use one of the platforms the catalog lists for the game.
 */
final class GamePlatforms
{
    public static function assertAllowed(Game $game, ?int $platformId): void
    {
        if ($platformId === null) {
            return;
        }

        if (! $game->platforms()->whereKey($platformId)->exists()) {
            throw new InvalidArgumentException("Platform {$platformId} is not available for game {$game->id}.");
        }
    }
}
