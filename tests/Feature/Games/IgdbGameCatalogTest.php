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
