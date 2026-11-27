<?php

namespace App\Domain\Games\Data;

use Carbon\CarbonImmutable;

/**
 * A game as returned by a GameCatalog, independent of the provider's format.
 */
final readonly class CatalogGame
{
    /**
     * @param  list<CatalogTerm>  $platforms
     * @param  list<CatalogTerm>  $genres
     */
    public function __construct(
        public int $igdbId,
        public string $slug,
        public string $name,
        public ?string $summary = null,
        public ?string $coverImageId = null,
        public ?CarbonImmutable $firstReleaseDate = null,
        public ?int $gameTypeId = null,
        public ?CarbonImmutable $updatedAt = null,
        public array $platforms = [],
        public array $genres = [],
    ) {}
}
