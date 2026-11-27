<?php

namespace App\Domain\Games\Contracts;

use App\Domain\Games\Data\CatalogGame;
use App\Domain\Games\Exceptions\CatalogUnavailable;

/**
 * The external source of game data. IgdbGameCatalog is the only
 * implementation; nothing outside the Games module talks to IGDB directly.
 */
interface GameCatalog
{
    /**
     * @return list<CatalogGame>
     *
     * @throws CatalogUnavailable
     */
    public function search(string $query, int $limit = 20): array;

    /**
     * @throws CatalogUnavailable
     */
    public function find(int $igdbId): ?CatalogGame;
}
