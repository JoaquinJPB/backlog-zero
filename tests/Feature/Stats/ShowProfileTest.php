<?php

use App\Domain\Library\Models\LibraryEntry;
use App\Domain\Users\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->travelTo('2026-11-27 12:00:00');
    $this->user = User::factory()->create(['name' => 'Ada Lovelace', 'username' => 'ada']);
});

test('guests are sent home', function () {
    $this->get(route('profile.show', 'ada'))->assertRedirect('/');
});

test('other users get a 404 while profiles are private', function () {
    $this->actingAs(User::factory()->create(['username' => 'bob']))
        ->get(route('profile.show', 'ada'))
        ->assertNotFound();
});

test('unknown usernames are a 404', function () {
    $this->actingAs($this->user)
        ->get('/u/nobody')
        ->assertNotFound();
});

test('the owner sees their profile with stats and played games', function () {
    LibraryEntry::factory()->for($this->user)->create([
        'status' => 'finished', 'rating' => 8, 'finished_on' => '2026-05-01',
    ]);
    LibraryEntry::factory()->for($this->user)->create(['status' => 'backlog']);

    $this->actingAs($this->user)
        ->get(route('profile.show', 'ada'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('profile/show')
            ->where('profile', ['name' => 'Ada Lovelace', 'username' => 'ada', 'avatar_url' => null])
            ->where('stats.counts', [
                'library' => 2,
                'finished' => 1,
                'finished_this_year' => 1,
                'playing' => 0,
                'backlog' => 1,
                'abandoned' => 0,
            ])
            ->where('stats.current_year', 2026)
            ->where('stats.average_rating', 8)
            ->where('stats.rating_histogram', [0, 0, 0, 0, 0, 0, 0, 1, 0, 0])
            ->where('stats.finished_per_year', [['year' => 2026, 'count' => 1]])
            ->where('stats.top_platforms', [])
            ->where('stats.top_genres', [])
            ->has('games', 1)
            ->where('games.0.status', 'finished')
            ->has('games.0.game')
            ->where('pagination.current_page', 1)
        );
});

test('the header links to the profile through the shared username', function () {
    $this->actingAs($this->user)
        ->get(route('profile.show', 'ada'))
        ->assertInertia(fn (Assert $page) => $page->where('auth.user.username', 'ada'));
});
