<?php

use App\Domain\Games\Contracts\GameCatalog;
use App\Domain\Games\Igdb\IgdbGameCatalog;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

/**
 * The first matching fake wins, so each test fakes the games response itself.
 *
 * @param  array<mixed>  $games
 */
function fakeIgdbGames(array $games): void
{
    Http::fake([
        'id.twitch.tv/*' => Http::response(igdbFixture('token')),
        'api.igdb.com/v4/games' => Http::response($games),
    ]);
}

test('the container resolves the IGDB implementation', function () {
    expect(app(GameCatalog::class))->toBeInstanceOf(IgdbGameCatalog::class);
});

test('search sends a sanitized Apicalypse query and maps the results', function () {
    fakeIgdbGames(igdbFixture('games-search'));

    $games = app(GameCatalog::class)->search("  The \"Witcher\";\n 3\\ ");

    expect($games)->toHaveCount(4)
        ->and($games[0]->name)->toBe('The Witcher 3: Wild Hunt');

    Http::assertSent(fn (Request $request) => str_starts_with($request->body(), 'search "The Witcher 3"; fields name,slug,')
        && str_ends_with($request->body(), 'where version_parent = null; limit 20;'));
});

test('a blank search does not hit the API', function () {
    fakeIgdbGames([]);

    expect(app(GameCatalog::class)->search(' "; '))->toBe([]);

    Http::assertNothingSent();
});

test('find queries by id and returns the game', function () {
    fakeIgdbGames(igdbFixture('game-find'));

    $game = app(GameCatalog::class)->find(1942);

    expect($game?->igdbId)->toBe(1942)
        ->and($game?->summary)->toStartWith('The Witcher 3: Wild Hunt is an open-world');
    Http::assertSent(fn (Request $request) => str_contains($request->body(), 'where id = 1942; limit 1;'));
});

test('find returns null when IGDB has no such game', function () {
    fakeIgdbGames([]);

    expect(app(GameCatalog::class)->find(123))->toBeNull();
});

test('search also matches by name, so titles made of common words are found', function () {
    // IGDB's full-text search drops very common words: "we were here" finds nothing.
    Http::fake([
        'id.twitch.tv/*' => Http::response(igdbFixture('token')),
        'api.igdb.com/v4/games' => Http::sequence()
            ->push([])
            ->push([
                ['id' => 11, 'name' => 'We Were Here Too', 'slug' => 'we-were-here-too', 'game_type' => 0],
                ['id' => 10, 'name' => 'We Were Here', 'slug' => 'we-were-here', 'game_type' => 0],
            ]),
    ]);

    $games = app(GameCatalog::class)->search('We Were Here');

    expect(array_map(fn ($game) => $game->name, $games))->toBe(['We Were Here', 'We Were Here Too']);
    Http::assertSent(fn (Request $request) => str_contains($request->body(), 'where name ~ *"We Were Here"* & version_parent = null;'));
});

test('search results are ranked: the main game before its skins and bundles', function () {
    Http::fake([
        'id.twitch.tv/*' => Http::response(igdbFixture('token')),
        'api.igdb.com/v4/games' => Http::sequence()
            ->push([
                ['id' => 1, 'name' => 'Prince of Persia: The Lost Crown - Dark Prince Skin', 'slug' => 'skin', 'game_type' => 13],
                ['id' => 2, 'name' => 'Prince of Persia: The Lost Crown', 'slug' => 'lost-crown', 'game_type' => 0],
            ])
            ->push([
                ['id' => 2, 'name' => 'Prince of Persia: The Lost Crown', 'slug' => 'lost-crown', 'game_type' => 0],
            ]),
    ]);

    $games = app(GameCatalog::class)->search('Prince of Persia The Lost Crown');

    expect(array_map(fn ($game) => $game->igdbId, $games))->toBe([2, 1]);
});
