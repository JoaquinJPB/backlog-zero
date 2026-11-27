<?php

use App\Domain\Games\Data\CatalogGame;
use App\Domain\Games\Igdb\IgdbSearchRanker;
use Carbon\CarbonImmutable;

function rankedGame(int $id, string $name, ?int $gameType): CatalogGame
{
    return new CatalogGame(igdbId: $id, slug: "game-{$id}", name: $name, gameTypeId: $gameType);
}

/**
 * @param  list<CatalogGame>  $games
 * @return list<int>
 */
function rankedIds(array $games, string $term, int $limit = 20): array
{
    return array_map(fn (CatalogGame $game): int => $game->igdbId, (new IgdbSearchRanker)->rank($games, $term, $limit));
}

test('main games, remakes and remasters come before ports, and extras go last', function () {
    // IGDB's own order for "resident evil 2" (simplified).
    $games = [
        rankedGame(1, 'Resident Evil 2 + Resident Evil 3 Bundle', 3),
        rankedGame(2, 'Resident Evil 2', 0),
        rankedGame(3, 'Resident Evil 2', 11),
        rankedGame(4, 'Resident Evil 2: Source', 5),
        rankedGame(5, 'Resident Evil 2', 8),
        rankedGame(6, 'Resident Evil 2: Extra DLC Pack', 13),
    ];

    expect(rankedIds($games, 'resident evil 2'))->toBe([2, 5, 3, 1, 4, 6]);
});

test('an exact name match wins within its tier, ignoring case and punctuation', function () {
    $games = [
        rankedGame(1, 'Prince of Persia: The Lost Crown - Dark Prince Skin', 13),
        rankedGame(2, 'Prince of Persia: The Sands of Time', 0),
        rankedGame(3, 'Prince of Persia: The Lost Crown', 0),
    ];

    expect(rankedIds($games, 'prince of persia the lost crown'))->toBe([3, 2, 1]);
});

test('games without a known type are treated as main games', function () {
    $games = [rankedGame(1, 'Some Bundle', 3), rankedGame(2, 'Old Game', null)];

    expect(rankedIds($games, 'game'))->toBe([2, 1]);
});

test('duplicates are dropped and the result is capped', function () {
    $games = [
        rankedGame(1, 'A', 0),
        rankedGame(2, 'B', 0),
        rankedGame(1, 'A', 0),
        rankedGame(3, 'C', 0),
    ];

    expect(rankedIds($games, 'x', limit: 2))->toBe([1, 2]);
});

test('among equal matches, games with a release date come before undated ones', function () {
    // IGDB lists an undated homonym before "It Takes Two" (2021).
    $games = [
        new CatalogGame(igdbId: 1, slug: 'a', name: 'It Takes Two'),
        new CatalogGame(igdbId: 2, slug: 'b', name: 'It Takes Two', firstReleaseDate: CarbonImmutable::parse('2021-03-26')),
    ];

    expect(rankedIds($games, 'it takes two'))->toBe([2, 1]);
});
