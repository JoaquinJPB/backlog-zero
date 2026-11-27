<?php

use App\Domain\Games\Models\Game;
use App\Domain\Games\Models\Genre;
use App\Domain\Games\Models\Platform;
use Illuminate\Database\UniqueConstraintViolationException;

test('games have platforms and genres sorted by name', function () {
    $game = Game::factory()->create();
    $game->platforms()->attach([
        Platform::factory()->create(['name' => 'Switch'])->id,
        Platform::factory()->create(['name' => 'PC'])->id,
    ]);
    $game->genres()->attach(Genre::factory()->create(['name' => 'RPG'])->id);

    expect($game->platforms->pluck('name')->all())->toBe(['PC', 'Switch'])
        ->and($game->genres->pluck('name')->all())->toBe(['RPG']);
});

test('igdb ids are unique', function () {
    Game::factory()->create(['igdb_id' => 1942]);
    Game::factory()->create(['igdb_id' => 1942]);
})->throws(UniqueConstraintViolationException::class);

test('slugs are unique', function () {
    Game::factory()->create(['slug' => 'the-witcher-3']);
    Game::factory()->create(['slug' => 'the-witcher-3']);
})->throws(UniqueConstraintViolationException::class);

test('games are stale after a week without sync', function () {
    $fresh = Game::factory()->create();
    $stale = Game::factory()->stale()->create();

    expect($fresh->isStale())->toBeFalse()
        ->and($stale->isStale())->toBeTrue()
        ->and(Game::query()->stale()->pluck('id')->all())->toBe([$stale->id]);
});

test('games are routed by slug', function () {
    expect((new Game)->getRouteKeyName())->toBe('slug');
});
