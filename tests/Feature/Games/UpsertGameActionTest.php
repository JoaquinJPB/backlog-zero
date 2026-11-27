<?php

use App\Domain\Games\Actions\UpsertGameAction;
use App\Domain\Games\Data\CatalogGame;
use App\Domain\Games\Data\CatalogTerm;
use App\Domain\Games\Igdb\IgdbGameMapper;
use App\Domain\Games\Models\Game;
use App\Domain\Games\Models\Platform;

function catalogGameFromFixture(string $fixture, int $index = 0): CatalogGame
{
    $game = (new IgdbGameMapper)->toCatalogGame(igdbFixture($fixture)[$index]);

    expect($game)->not->toBeNull();

    return $game;
}

test('it imports a catalog game with platforms and genres', function () {
    $game = app(UpsertGameAction::class)->execute(catalogGameFromFixture('games-search'));

    expect($game->igdb_id)->toBe(1942)
        ->and($game->slug)->toBe('the-witcher-3-wild-hunt')
        ->and($game->cover_image_id)->toBe('co1wyy')
        ->and($game->first_release_date?->toDateString())->toBe('2015-05-19')
        ->and($game->synced_at->isToday())->toBeTrue()
        ->and($game->platforms->pluck('abbreviation')->all())->toBe(['PC', 'PS4', 'XONE'])
        ->and($game->genres->pluck('name')->all())->toBe(['Adventure', 'Role-playing (RPG)']);
});

test('re-importing updates the same row and re-syncs relations', function () {
    $this->travelTo(now()->subDays(10));
    app(UpsertGameAction::class)->execute(catalogGameFromFixture('games-search'));
    $this->travelBack();

    $game = app(UpsertGameAction::class)->execute(catalogGameFromFixture('game-find'));

    expect(Game::query()->count())->toBe(1)
        ->and($game->summary)->toBe('Updated summary.')
        ->and($game->cover_image_id)->toBe('co1wyy-new')
        ->and($game->isStale())->toBeFalse()
        // Sorted by name: "Nintendo Switch" < "PC (Microsoft Windows)".
        ->and($game->platforms->pluck('abbreviation')->all())->toBe(['Switch', 'PC'])
        ->and($game->genres->pluck('name')->all())->toBe(['Role-playing (RPG)']);
});

test('platforms shared by several games are stored once', function () {
    app(UpsertGameAction::class)->execute(catalogGameFromFixture('games-search', 0));
    app(UpsertGameAction::class)->execute(catalogGameFromFixture('games-search', 1));

    expect(Platform::query()->where('igdb_id', 6)->count())->toBe(1)
        ->and(Game::query()->count())->toBe(2);
});

test('a slug used by another IGDB game gets the igdb id appended', function () {
    Game::factory()->create(['igdb_id' => 1, 'slug' => 'tetris']);

    $game = app(UpsertGameAction::class)->execute(new CatalogGame(igdbId: 2, slug: 'tetris', name: 'Tetris'));

    expect($game->slug)->toBe('tetris-2');
});

test('slugs that clash with fixed routes are suffixed', function () {
    $game = app(UpsertGameAction::class)->execute(new CatalogGame(igdbId: 77, slug: 'search', name: 'Search'));

    expect($game->slug)->toBe('search-77');
});

test('repeated platforms in the payload are tolerated', function () {
    $pc = new CatalogTerm(igdbId: 6, name: 'PC', slug: 'win', abbreviation: 'PC');

    $game = app(UpsertGameAction::class)->execute(
        new CatalogGame(igdbId: 5, slug: 'x', name: 'X', platforms: [$pc, $pc]),
    );

    expect($game->platforms)->toHaveCount(1);
});
