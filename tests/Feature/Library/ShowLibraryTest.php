<?php

use App\Domain\Games\Models\Game;
use App\Domain\Games\Models\Platform;
use App\Domain\Library\Models\LibraryEntry;
use App\Domain\Users\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('guests are sent home', function () {
    $this->get(route('library.index'))->assertRedirect('/');
});

test('the library lists only my entries, most recently updated first', function () {
    $older = LibraryEntry::factory()->for($this->user)->create(['updated_at' => now()->subDays(2)]);
    $newer = LibraryEntry::factory()->playing()->for($this->user)->create(['updated_at' => now()]);
    LibraryEntry::factory()->create(); // someone else's

    $this->actingAs($this->user)
        ->get(route('library.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('library/index')
            ->where('status', null)
            ->has('entries', 2)
            ->where('entries.0.id', $newer->id)
            ->where('entries.1.id', $older->id)
            ->where('counts', ['all' => 2, 'playing' => 1, 'finished' => 0, 'abandoned' => 0, 'backlog' => 1])
        );
});

test('entries include the game summary, platform and dates', function () {
    $game = Game::factory()->create(['name' => 'Hades', 'slug' => 'hades', 'first_release_date' => '2020-09-17']);
    $pc = Platform::factory()->create(['name' => 'PC (Microsoft Windows)', 'abbreviation' => 'PC']);
    $game->platforms()->attach($pc);
    LibraryEntry::factory()->for($this->user)->for($game)->create([
        'status' => 'finished',
        'rating' => 10,
        'platform_id' => $pc->id,
        'started_on' => '2026-01-02',
        'finished_on' => '2026-02-03',
    ]);

    $this->actingAs($this->user)
        ->get(route('library.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('entries.0.status', 'finished')
            ->where('entries.0.rating', 10)
            ->where('entries.0.platform', 'PC (Microsoft Windows)')
            ->where('entries.0.started_on', '2026-01-02')
            ->where('entries.0.finished_on', '2026-02-03')
            ->where('entries.0.game.slug', 'hades')
            ->where('entries.0.game.year', 2020)
            ->where('entries.0.game.platforms', ['PC'])
        );
});

test('the library can be filtered by status', function () {
    LibraryEntry::factory()->for($this->user)->create();
    $finished = LibraryEntry::factory()->finished()->for($this->user)->create();

    $this->actingAs($this->user)
        ->get(route('library.index', ['status' => 'finished']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('status', 'finished')
            ->has('entries', 1)
            ->where('entries.0.id', $finished->id)
            ->where('counts.all', 2)
        );
});

test('unknown status filters are rejected', function () {
    $this->actingAs($this->user)
        ->get(route('library.index', ['status' => 'wishlist']))
        ->assertSessionHasErrors('status');
});

test('large libraries are paginated', function () {
    LibraryEntry::factory()->count(50)->for($this->user)->create();

    $this->actingAs($this->user)
        ->get(route('library.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('entries', 48)
            ->where('pagination.current_page', 1)
            ->where('pagination.last_page', 2)
            ->where('pagination.prev_url', null)
            ->where('pagination.next_url', fn (?string $url) => str_contains((string) $url, 'page=2'))
        );

    $this->actingAs($this->user)
        ->get(route('library.index', ['page' => 2]))
        ->assertInertia(fn (Assert $page) => $page->has('entries', 2));
});

test('guests cannot see someone else\'s library', function () {
    $this->get(route('library.show', $this->user->username))->assertRedirect('/');
});

test('an unknown username has no library', function () {
    $this->actingAs(User::factory()->create())
        ->get('/u/nobody/library')
        ->assertNotFound();
});

test("someone else's library shows that user's entries, not the viewer's", function () {
    $visitor = User::factory()->create();
    $mine = LibraryEntry::factory()->for($this->user)->create();
    LibraryEntry::factory()->playing()->for($visitor)->create();

    $this->actingAs($visitor)
        ->get(route('library.show', $this->user->username))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('library/index')
            ->where('owner', [
                'name' => $this->user->name,
                'username' => $this->user->username,
                'avatar_url' => $this->user->avatar_url,
            ])
            ->has('entries', 1)
            ->where('entries.0.id', $mine->id)
            ->where('counts', ['all' => 1, 'playing' => 0, 'finished' => 0, 'abandoned' => 0, 'backlog' => 1])
        );
});

test('the status filter works on someone else\'s library', function () {
    LibraryEntry::factory()->playing()->for($this->user)->create();
    LibraryEntry::factory()->for($this->user)->create();

    $this->actingAs(User::factory()->create())
        ->get(route('library.show', [$this->user->username, 'status' => 'playing']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('status', 'playing')->has('entries', 1));
});

test('the own library route reports the signed-in user as the owner', function () {
    $this->actingAs($this->user)
        ->get(route('library.index'))
        ->assertInertia(fn (Assert $page) => $page->where('owner.username', $this->user->username));
});
