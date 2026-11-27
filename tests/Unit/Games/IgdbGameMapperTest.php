<?php

use App\Domain\Games\Igdb\IgdbGameMapper;

test('maps a full IGDB game row', function () {
    $game = (new IgdbGameMapper)->toCatalogGame(igdbFixture('games-search')[0]);

    expect($game)->not->toBeNull()
        ->and($game?->igdbId)->toBe(1942)
        ->and($game?->slug)->toBe('the-witcher-3-wild-hunt')
        ->and($game?->name)->toBe('The Witcher 3: Wild Hunt')
        ->and($game?->coverImageId)->toBe('co1wyy')
        ->and($game?->firstReleaseDate?->toDateString())->toBe('2015-05-19')
        ->and($game?->gameTypeId)->toBe(0)
        ->and($game?->updatedAt?->getTimestamp())->toBe(1730000000)
        ->and($game?->platforms)->toHaveCount(3)
        ->and($game?->platforms[1]->abbreviation)->toBe('PS4')
        ->and($game?->platforms[1]->igdbId)->toBe(48)
        ->and(array_map(fn ($genre) => $genre->name, $game?->genres ?? []))
        ->toBe(['Role-playing (RPG)', 'Adventure']);
});

test('optional fields default to null or empty', function () {
    $game = (new IgdbGameMapper)->toCatalogGame(['id' => 7, 'name' => ' Tetris ', 'slug' => 'tetris']);

    expect($game?->name)->toBe('Tetris')
        ->and($game?->summary)->toBeNull()
        ->and($game?->coverImageId)->toBeNull()
        ->and($game?->firstReleaseDate)->toBeNull()
        ->and($game?->platforms)->toBe([])
        ->and($game?->genres)->toBe([]);
});

test('rows without a usable id, name or slug are skipped', function (array $row) {
    expect((new IgdbGameMapper)->toCatalogGame($row))->toBeNull();
})->with([
    'no name' => [['id' => 1, 'slug' => 'x']],
    'blank name' => [['id' => 1, 'name' => '  ', 'slug' => 'x']],
    'no slug' => [['id' => 1, 'name' => 'X']],
    'string id' => [['id' => '1', 'name' => 'X', 'slug' => 'x']],
]);

test('malformed platforms and genres are ignored', function () {
    $game = (new IgdbGameMapper)->toCatalogGame([
        'id' => 1,
        'name' => 'X',
        'slug' => 'x',
        'platforms' => [6, ['id' => 6, 'name' => 'PC', 'slug' => 'win'], ['id' => 7]],
        'genres' => 'not-a-list',
    ]);

    expect($game?->platforms)->toHaveCount(1)
        ->and($game?->platforms[0]->abbreviation)->toBeNull()
        ->and($game?->genres)->toBe([]);
});
