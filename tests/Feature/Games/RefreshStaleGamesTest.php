<?php

use App\Domain\Games\Exceptions\CatalogUnavailable;
use App\Domain\Games\Jobs\RefreshGameJob;
use App\Domain\Games\Models\Game;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

/**
 * @param  array<mixed>|int  $games  fixture rows, or an HTTP status to fail with
 */
function fakeIgdbFind(array|int $games): void
{
    Http::fake([
        'id.twitch.tv/*' => Http::response(igdbFixture('token')),
        'api.igdb.com/v4/games' => is_int($games) ? Http::response('error', $games) : Http::response($games),
    ]);
}

test('the job updates a game from the catalog', function () {
    fakeIgdbFind(igdbFixture('game-find'));
    $game = Game::factory()->stale()->create(['igdb_id' => 1942, 'summary' => 'Old summary.']);

    RefreshGameJob::dispatchSync($game);

    $game->refresh();
    expect($game->summary)->toStartWith('The Witcher 3: Wild Hunt is an open-world')
        ->and($game->isStale())->toBeFalse()
        ->and($game->platforms)->toHaveCount(7);
});

test('games that vanished from the catalog are kept and not retried daily', function () {
    fakeIgdbFind([]);
    $game = Game::factory()->stale()->create(['summary' => 'Keep me.']);

    RefreshGameJob::dispatchSync($game);

    $game->refresh();
    expect($game->summary)->toBe('Keep me.')
        ->and($game->isStale())->toBeFalse();
});

test('catalog outages make the job fail so the queue retries it', function () {
    fakeIgdbFind(503);
    $game = Game::factory()->stale()->create();

    expect(fn () => RefreshGameJob::dispatchSync($game))->toThrow(CatalogUnavailable::class);
    expect($game->refresh()->isStale())->toBeTrue();
});

test('the job is unique per game and throttled', function () {
    $job = new RefreshGameJob(Game::factory()->create());

    expect($job->uniqueId())->toBe((string) $job->game->id)
        ->and($job->middleware()[0])->toBeInstanceOf(RateLimited::class)
        ->and($job->tries)->toBe(3);
});

test('the command queues only stale games, oldest first', function () {
    Queue::fake();
    Game::factory()->create();
    $older = Game::factory()->create(['synced_at' => now()->subDays(30)]);
    $old = Game::factory()->stale()->create();

    $this->artisan('games:refresh-stale')
        ->expectsOutputToContain('Queued 2 stale game(s)')
        ->assertSuccessful();
    Queue::assertPushed(RefreshGameJob::class, 2);

    Queue::fake();
    $this->artisan('games:refresh-stale', ['--limit' => 1])->assertSuccessful();
    Queue::assertPushed(RefreshGameJob::class, fn (RefreshGameJob $job) => $job->game->is($older));
    Queue::assertNotPushed(RefreshGameJob::class, fn (RefreshGameJob $job) => $job->game->is($old));
});

test('the refresh is scheduled daily', function () {
    $event = collect(app(Schedule::class)->events())
        ->first(fn (Event $event) => str_contains((string) $event->command, 'games:refresh-stale'));

    expect($event)->not->toBeNull()
        ->and($event?->expression)->toBe('0 4 * * *');
});

test('viewing a stale game queues a refresh but still renders', function () {
    Queue::fake();
    $stale = Game::factory()->stale()->create();
    $fresh = Game::factory()->create();

    $this->get(route('games.show', $stale))->assertOk();
    $this->get(route('games.show', $fresh))->assertOk();

    Queue::assertPushed(RefreshGameJob::class, 1);
    Queue::assertPushed(RefreshGameJob::class, fn (RefreshGameJob $job) => $job->game->is($stale));
});
