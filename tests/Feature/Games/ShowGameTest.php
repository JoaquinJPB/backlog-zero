<?php

use App\Domain\Games\Models\Game;
use App\Domain\Games\Models\Genre;
use App\Domain\Games\Models\Platform;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

test('the game page is public and served from the local catalog', function () {
    Http::fake();
    $game = Game::factory()->create([
        'slug' => 'the-witcher-3-wild-hunt',
        'name' => 'The Witcher 3: Wild Hunt',
        'summary' => 'Geralt again.',
        'cover_image_id' => 'co1wyy',
        'first_release_date' => '2015-05-19',
    ]);
    $game->platforms()->attach([
        Platform::factory()->create(['name' => 'PlayStation 4', 'abbreviation' => 'PS4'])->id,
        Platform::factory()->create(['name' => 'PC (Microsoft Windows)', 'abbreviation' => 'PC'])->id,
    ]);
    $game->genres()->attach(Genre::factory()->create(['name' => 'Role-playing (RPG)'])->id);

    $this->get(route('games.show', $game))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('games/show')
            ->where('game', [
                'slug' => 'the-witcher-3-wild-hunt',
                'name' => 'The Witcher 3: Wild Hunt',
                'summary' => 'Geralt again.',
                'cover_image_id' => 'co1wyy',
                'release_date' => '2015-05-19',
                'year' => 2015,
                'platforms' => [
                    ['name' => 'PC (Microsoft Windows)', 'abbreviation' => 'PC'],
                    ['name' => 'PlayStation 4', 'abbreviation' => 'PS4'],
                ],
                'genres' => ['Role-playing (RPG)'],
                'igdb_url' => 'https://www.igdb.com/games/the-witcher-3-wild-hunt',
            ])
        );

    Http::assertNothingSent();
});

test('games without optional data still render', function () {
    $game = Game::factory()->create([
        'summary' => null,
        'cover_image_id' => null,
        'first_release_date' => null,
    ]);

    $this->get(route('games.show', $game))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('game.release_date', null)
            ->where('game.year', null)
            ->where('game.platforms', [])
        );
});

test('unknown slugs return 404', function () {
    $this->get('/games/does-not-exist')->assertNotFound();
});
