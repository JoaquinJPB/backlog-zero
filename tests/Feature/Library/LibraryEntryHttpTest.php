<?php

use App\Domain\Games\Models\Game;
use App\Domain\Games\Models\Platform;
use App\Domain\Library\Enums\GameStatus;
use App\Domain\Library\Models\LibraryEntry;
use App\Domain\Users\Models\User;
use Illuminate\Database\QueryException;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->freezeTime();
    $this->travelTo('2026-11-27 12:00:00');
    $this->user = User::factory()->create();
    $this->game = Game::factory()->create();
    $this->switch = Platform::factory()->create(['name' => 'Nintendo Switch']);
    $this->game->platforms()->attach($this->switch);
});

test('guests see the game without library data and cannot add it', function () {
    $this->get(route('games.show', $this->game))
        ->assertInertia(fn (Assert $page) => $page->where('library', null));

    $this->post(route('library.store', $this->game), ['status' => 'playing'])->assertRedirect('/');
    expect(LibraryEntry::query()->count())->toBe(0);
});

test('the game page offers the game platforms and no entry yet', function () {
    $this->actingAs($this->user)
        ->get(route('games.show', $this->game))
        ->assertInertia(fn (Assert $page) => $page
            ->where('library.entry', null)
            ->where('library.platforms', [['id' => $this->switch->id, 'name' => 'Nintendo Switch']])
        );
});

test('a user adds a game to their library', function () {
    $this->actingAs($this->user)
        ->post(route('library.store', $this->game), [
            'status' => 'finished',
            'rating' => 9,
            'platform_id' => $this->switch->id,
            'started_on' => '2026-10-01',
            'finished_on' => '2026-11-20',
        ])
        ->assertRedirect(route('games.show', $this->game))
        ->assertInertiaFlash('success', __('ui.library.added'));

    $entry = LibraryEntry::query()->sole();
    expect($entry->user_id)->toBe($this->user->id)
        ->and($entry->status)->toBe(GameStatus::Finished)
        ->and($entry->rating)->toBe(9)
        ->and($entry->finished_on?->toDateString())->toBe('2026-11-20');

    $this->get(route('games.show', $this->game))
        ->assertInertia(fn (Assert $page) => $page
            ->where('library.entry.status', 'finished')
            ->where('library.entry.rating', 9)
            ->where('library.entry.started_on', '2026-10-01')
        );
});

test('adding the same game twice shows a message instead of failing', function () {
    LibraryEntry::factory()->for($this->user)->for($this->game)->create();

    $this->actingAs($this->user)
        ->post(route('library.store', $this->game), ['status' => 'backlog'])
        ->assertInertiaFlash('error', __('ui.library.already_added'));

    expect(LibraryEntry::query()->count())->toBe(1);
});

test('invalid entries are rejected with field errors', function (array $payload, string $field) {
    $this->actingAs($this->user)
        ->from(route('games.show', $this->game))
        ->post(route('library.store', $this->game), $payload)
        ->assertSessionHasErrors($field);

    expect(LibraryEntry::query()->count())->toBe(0);
})->with([
    'missing status' => [[], 'status'],
    'unknown status' => [['status' => 'wishlist'], 'status'],
    'rating too high' => [['status' => 'playing', 'rating' => 11], 'rating'],
    'rating zero' => [['status' => 'playing', 'rating' => 0], 'rating'],
    'foreign platform' => [['status' => 'playing', 'platform_id' => 999_999], 'platform_id'],
    'future start' => [['status' => 'playing', 'started_on' => '2026-12-01'], 'started_on'],
    'bad date format' => [['status' => 'playing', 'started_on' => '01/10/2026'], 'started_on'],
    'finish date while playing' => [['status' => 'playing', 'finished_on' => '2026-11-01'], 'finished_on'],
    'finish before start' => [['status' => 'finished', 'started_on' => '2026-11-10', 'finished_on' => '2026-11-01'], 'finished_on'],
    '100% in the backlog' => [['status' => 'backlog', 'completed' => true], 'completed'],
    '100% not a boolean' => [['status' => 'finished', 'completed' => 'yes please'], 'completed'],
    'future finish' => [['status' => 'finished', 'finished_on' => '2026-11-28'], 'finished_on'],
]);

test('a user marks a game as 100% and sees it on the game page', function () {
    $this->actingAs($this->user)
        ->post(route('library.store', $this->game), ['status' => 'finished', 'completed' => true])
        ->assertSessionHasNoErrors();

    expect(LibraryEntry::query()->sole()->completed)->toBeTrue();

    $this->get(route('games.show', $this->game))
        ->assertInertia(fn (Assert $page) => $page->where('library.entry.completed', true));
});

test('entries are not at 100% unless the user says so', function () {
    $this->actingAs($this->user)
        ->post(route('library.store', $this->game), ['status' => 'playing'])
        ->assertSessionHasNoErrors();

    expect(LibraryEntry::query()->sole()->completed)->toBeFalse();
});

test('owners can unmark 100% when they update their entry', function () {
    $entry = LibraryEntry::factory()->completed()->for($this->user)->for($this->game)->create();

    $this->actingAs($this->user)
        ->patch(route('library.update', $entry), ['status' => 'backlog', 'completed' => false])
        ->assertSessionHasNoErrors();

    expect($entry->fresh()?->completed)->toBeFalse();
});

test('the database rejects a backlog entry at 100%', function () {
    LibraryEntry::factory()->for($this->user)->for($this->game)->create(['completed' => true]);
})->throws(QueryException::class, 'library_entries_completed_played');

test('a platform listed for another game is not accepted', function () {
    $other = Platform::factory()->create();
    Game::factory()->create()->platforms()->attach($other);

    $this->actingAs($this->user)
        ->post(route('library.store', $this->game), ['status' => 'playing', 'platform_id' => $other->id])
        ->assertSessionHasErrors('platform_id');
});

test('validation messages are in Spanish', function () {
    $this->actingAs($this->user)
        ->post(route('library.store', $this->game), ['status' => 'playing', 'finished_on' => '2026-11-01'])
        ->assertSessionHasErrors(['finished_on' => __('validation.library.finish_date_not_allowed')]);

    $this->actingAs($this->user)
        ->post(route('library.store', $this->game), ['status' => 'backlog', 'completed' => true])
        ->assertSessionHasErrors(['completed' => __('validation.library.completed_not_allowed')]);
});

test('owners update their entry', function () {
    $entry = LibraryEntry::factory()->playing()->for($this->user)->for($this->game)->create();

    $this->actingAs($this->user)
        ->from(route('games.show', $this->game))
        ->patch(route('library.update', $entry), [
            'status' => 'abandoned',
            'rating' => 4,
            'finished_on' => '2026-11-26',
        ])
        ->assertRedirect(route('games.show', $this->game))
        ->assertInertiaFlash('success', __('ui.library.saved'));

    $entry->refresh();
    expect($entry->status)->toBe(GameStatus::Abandoned)
        ->and($entry->rating)->toBe(4)
        ->and($entry->started_on)->toBeNull();
});

test('nobody can update or delete someone else\'s entry', function () {
    $entry = LibraryEntry::factory()->for(User::factory())->for($this->game)->create();

    $this->actingAs($this->user)
        ->patch(route('library.update', $entry), ['status' => 'finished'])
        ->assertForbidden();
    $this->actingAs($this->user)
        ->delete(route('library.destroy', $entry))
        ->assertForbidden();

    expect($entry->fresh()?->status)->toBe(GameStatus::Backlog);
});

test('owners remove an entry', function () {
    $entry = LibraryEntry::factory()->for($this->user)->for($this->game)->create();

    $this->actingAs($this->user)
        ->from(route('games.show', $this->game))
        ->delete(route('library.destroy', $entry))
        ->assertRedirect(route('games.show', $this->game))
        ->assertInertiaFlash('success', __('ui.library.removed'));

    expect(LibraryEntry::query()->count())->toBe(0);
});
