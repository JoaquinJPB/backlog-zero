<?php

namespace App\Domain\Games\Igdb;

use App\Domain\Games\Data\CatalogGame;
use Illuminate\Support\Str;

/**
 * Orders IGDB search results so the game people mean comes first. IGDB's
 * relevance often puts skins, bundles or ports above the main game; nothing
 * is filtered out (bundles stay findable), extras just go last.
 *
 * Order: tier by game_type, exact name match, has a release date (undated
 * entries are usually stubs), then IGDB's own order.
 */
final class IgdbSearchRanker
{
    /**
     * IGDB game_type ids (https://api-docs.igdb.com/#game-type). Anything not
     * listed, including a missing type, counts as a main game.
     */
    private const TIER_BY_GAME_TYPE = [
        11 => 1, // port
        2 => 1, // expansion
        6 => 1, // episode
        7 => 1, // season
        12 => 1, // fork
        3 => 2, // bundle
        1 => 2, // dlc / add-on
        5 => 2, // mod
        13 => 2, // pack (skins, cosmetic packs)
        14 => 2, // update
    ];

    /**
     * @param  list<CatalogGame>  $games  in IGDB's order (duplicates allowed)
     * @return list<CatalogGame>
     */
    public function rank(array $games, string $term, int $limit): array
    {
        $wanted = $this->normalize($term);
        $unique = [];

        foreach ($games as $game) {
            $unique[$game->igdbId] ??= $game;
        }

        $ranked = array_values($unique);
        $position = array_flip(array_keys($unique));

        usort($ranked, fn (CatalogGame $a, CatalogGame $b): int => [
            $this->tier($a),
            $this->normalize($a->name) === $wanted ? 0 : 1,
            $a->firstReleaseDate === null ? 1 : 0,
            $position[$a->igdbId],
        ] <=> [
            $this->tier($b),
            $this->normalize($b->name) === $wanted ? 0 : 1,
            $b->firstReleaseDate === null ? 1 : 0,
            $position[$b->igdbId],
        ]);

        return array_slice($ranked, 0, max(0, $limit));
    }

    private function tier(CatalogGame $game): int
    {
        return self::TIER_BY_GAME_TYPE[$game->gameTypeId ?? 0] ?? 0;
    }

    private function normalize(string $name): string
    {
        return Str::of($name)->lower()->replaceMatches('/[^\p{L}\p{N}]+/u', ' ')->squish()->toString();
    }
}
