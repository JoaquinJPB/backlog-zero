<?php

use App\Domain\Games\Exceptions\CatalogUnavailable;
use App\Domain\Games\Igdb\IgdbClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

const IGDB_TOKEN_URL = 'https://id.twitch.tv/oauth2/token';
const IGDB_GAMES_URL = 'https://api.igdb.com/v4/games';

beforeEach(function () {
    $this->freezeTime();
    Sleep::fake(syncWithCarbon: true);
});

function igdbClient(): IgdbClient
{
    return app(IgdbClient::class);
}

test('it authenticates with client credentials and sends IGDB headers', function () {
    Http::fake([
        IGDB_TOKEN_URL => Http::response(igdbFixture('token')),
        IGDB_GAMES_URL => Http::response([['id' => 1, 'name' => 'Tetris']]),
    ]);

    $rows = igdbClient()->query('games', 'fields name;');

    expect($rows)->toBe([['id' => 1, 'name' => 'Tetris']]);
    Http::assertSent(fn (Request $request) => $request->url() === IGDB_TOKEN_URL
        && $request['client_id'] === 'test-client-id'
        && $request['client_secret'] === 'test-client-secret'
        && $request['grant_type'] === 'client_credentials');
    Http::assertSent(fn (Request $request) => $request->url() === IGDB_GAMES_URL
        && $request->hasHeader('Client-ID', 'test-client-id')
        && $request->hasHeader('Authorization', 'Bearer fake-access-token')
        && $request->body() === 'fields name;');
});

test('the client secret never travels in a URL', function () {
    Http::fake([IGDB_TOKEN_URL => Http::response(igdbFixture('token')), IGDB_GAMES_URL => Http::response([])]);

    igdbClient()->query('games', 'fields name;');

    Http::assertNotSent(fn (Request $request) => str_contains($request->url(), 'test-client-secret'));
});

test('the access token is cached between requests', function () {
    Http::fake([IGDB_TOKEN_URL => Http::response(igdbFixture('token')), IGDB_GAMES_URL => Http::response([])]);

    igdbClient()->query('games', 'fields name;');
    igdbClient()->query('games', 'fields name;');

    Http::assertSentCount(3);
});

test('a 401 refreshes the token and retries once', function () {
    Http::fake([
        IGDB_TOKEN_URL => Http::sequence()
            ->push(['access_token' => 'old-token', 'expires_in' => 3600])
            ->push(['access_token' => 'new-token', 'expires_in' => 3600]),
        IGDB_GAMES_URL => Http::sequence()->push([], 401)->push([['id' => 1]]),
    ]);

    expect(igdbClient()->query('games', 'fields name;'))->toBe([['id' => 1]]);
    Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'Bearer new-token'));
});

test('429 responses are retried with backoff', function () {
    Http::fake([
        IGDB_TOKEN_URL => Http::response(igdbFixture('token')),
        IGDB_GAMES_URL => Http::sequence()->push([], 429)->push([], 429)->push([['id' => 1]]),
    ]);

    expect(igdbClient()->query('games', 'fields name;'))->toBe([['id' => 1]]);
    Sleep::assertSleptTimes(2);
});

test('requests above 4 per second wait for the next second', function () {
    Http::fake([IGDB_TOKEN_URL => Http::response(igdbFixture('token')), IGDB_GAMES_URL => Http::response([])]);

    foreach (range(1, 4) as $ignored) {
        igdbClient()->query('games', 'fields name;');
    }
    Sleep::assertNeverSlept();

    igdbClient()->query('games', 'fields name;');
    Sleep::assertSequence([Sleep::for(1)->second()]);
});

test('missing credentials fail without calling the API', function () {
    config(['services.igdb.client_id' => null]);
    Http::fake();

    expect(fn () => igdbClient()->query('games', 'fields name;'))
        ->toThrow(CatalogUnavailable::class, 'not configured');
    Http::assertNothingSent();
});

test('server errors become CatalogUnavailable and are not retried', function () {
    Http::fake([IGDB_TOKEN_URL => Http::response(igdbFixture('token')), IGDB_GAMES_URL => Http::response('boom', 500)]);

    expect(fn () => igdbClient()->query('games', 'fields name;'))->toThrow(CatalogUnavailable::class);
    Http::assertSentCount(2);
});

test('unexpected payloads become CatalogUnavailable', function () {
    Http::fake([IGDB_TOKEN_URL => Http::response(igdbFixture('token')), IGDB_GAMES_URL => Http::response(['id' => 1])]);

    expect(fn () => igdbClient()->query('games', 'fields name;'))
        ->toThrow(CatalogUnavailable::class, 'expected a JSON array');
});
