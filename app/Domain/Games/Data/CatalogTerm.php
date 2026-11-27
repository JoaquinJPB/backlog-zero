<?php

namespace App\Domain\Games\Data;

/**
 * A named catalog entity attached to a game (platform, genre).
 */
final readonly class CatalogTerm
{
    public function __construct(
        public int $igdbId,
        public string $name,
        public string $slug,
        public ?string $abbreviation = null,
    ) {}
}
