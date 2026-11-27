<?php

use App\Domain\Games\Exceptions\CatalogUnavailable;
use App\Domain\Games\Models\Game;
use App\Domain\Users\Models\User;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->create();
});

/**
 * @param  array<mixed>|int  $games  fixture rows, or an HTTP status to fail with
 */
function fakeIgdbSearch(array|int $games): void
{
    Http::fake([
        'id.twitch.tv/*' => Http::response(igdbFixture('token')),
        'api.igdb.com/v4/games' => is_int($games) ? Http::response('error', $games) : Http::response($games),
    ]);
}

test('guests must sign in to search', function () {
    $this->get(route('games.search', ['q' => 'witcher']))->assertRedirect('/');
});

test('the empty search page does not call IGDB', function () {
    Http::fake();

    $this->actingAs($this->user)
        ->get(route('games.search'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('games/search')
            ->where('query', '')
            ->where('results', [])
            ->where('failed', false)
            ->where('minLength', 2)
        );

    Http::assertNothingSent();
});

test('queries shorter than the minimum do not call IGDB', function () {
    Http::fake();

    $this->actingAs($this->user)
        ->get(route('games.search', ['q' => ' w ']))
        ->assertInertia(fn (Assert $page) => $page->where('results', []));

    Http::assertNothingSent();
});

test('search imports results locally and returns them in IGDB order', function () {
    fakeIgdbSearch(igdbFixture('games-search'));

    $this->actingAs($this->user)
        ->get(route('games.search', ['q' => 'witcher 3']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('query', 'witcher 3')
            ->has('results', 4)
            ->where('results.0', [
                'slug' => 'the-witcher-3-wild-hunt',
                'name' => 'The Witcher 3: Wild Hunt',
                'year' => 2015,
                'cover_image_id' => 'coaarl',
                'platforms' => ['Switch', 'Switch 2', 'PC', 'PS4', 'PS5', 'XONE', 'Series X|S'],
            ])
            ->where('results.1.slug', 'the-witcher-3-wild-hunt-plus-dark-souls-iii')
            ->where('results.1.year', 2018)
        );

    expect(Game::query()->pluck('igdb_id')->sort()->values()->all())->toBe([1942, 13166, 141472, 415005]);
});

test('repeated searches are served from cache', function () {
    fakeIgdbSearch(igdbFixture('games-search'));

    $this->actingAs($this->user)->get(route('games.search', ['q' => 'Witcher 3']));
    $this->actingAs($this->user)->get(route('games.search', ['q' => '  witcher   3 ']))
        ->assertInertia(fn (Assert $page) => $page->has('results', 4));

    // One token request plus a single IGDB search.
    Http::assertSentCount(2);
});

test('a catalog outage shows a friendly error and is reported', function () {
    Exceptions::fake();
    fakeIgdbSearch(503);

    $this->actingAs($this->user)
        ->get(route('games.search', ['q' => 'witcher']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('failed', true)
            ->where('results', [])
        );

    Exceptions::assertReported(CatalogUnavailable::class);
});

test('overly long queries are rejected', function () {
    Http::fake();

    $this->actingAs($this->user)
        ->get(route('games.search', ['q' => str_repeat('a', 101)]))
        ->assertSessionHasErrors('q');

    Http::assertNothingSent();
});

test('search is rate limited per user', function () {
    foreach (range(1, 30) as $ignored) {
        $this->actingAs($this->user)->get(route('games.search'))->assertOk();
    }

    $this->actingAs($this->user)->get(route('games.search'))->assertTooManyRequests();
});
