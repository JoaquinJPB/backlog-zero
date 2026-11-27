<?php

use App\Domain\Games\Models\Game;
use App\Domain\Games\Models\Platform;
use App\Domain\Library\Actions\AddGameToLibraryAction;
use App\Domain\Library\Actions\RemoveFromLibraryAction;
use App\Domain\Library\Actions\UpdateLibraryEntryAction;
use App\Domain\Library\Data\LibraryEntryData;
use App\Domain\Library\Enums\GameStatus;
use App\Domain\Library\Events\GameAddedToLibrary;
use App\Domain\Library\Events\GameFinished;
use App\Domain\Library\Events\GameRated;
use App\Domain\Library\Events\GameRemovedFromLibrary;
use App\Domain\Library\Exceptions\GameAlreadyInLibrary;
use App\Domain\Library\Models\LibraryEntry;
use App\Domain\Users\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake([GameAddedToLibrary::class, GameFinished::class, GameRated::class, GameRemovedFromLibrary::class]);
    $this->user = User::factory()->create();
    $this->game = Game::factory()->create();
    $this->pc = Platform::factory()->create();
    $this->game->platforms()->attach($this->pc);
});

test('adding a game stores the entry and announces it', function () {
    $entry = app(AddGameToLibraryAction::class)->execute($this->user, $this->game, new LibraryEntryData(
        status: GameStatus::Playing,
        platformId: $this->pc->id,
        startedOn: CarbonImmutable::parse('2026-11-01'),
    ));

    expect($entry->status)->toBe(GameStatus::Playing)
        ->and($entry->platform_id)->toBe($this->pc->id)
        ->and($entry->started_on?->toDateString())->toBe('2026-11-01');
    Event::assertDispatched(GameAddedToLibrary::class, fn ($event) => $event->entry->is($entry));
    Event::assertNotDispatched(GameFinished::class);
    Event::assertNotDispatched(GameRated::class);
});

test('adding a finished, rated game also raises GameFinished and GameRated', function () {
    app(AddGameToLibraryAction::class)->execute($this->user, $this->game, new LibraryEntryData(
        status: GameStatus::Finished,
        rating: 8,
        finishedOn: CarbonImmutable::parse('2026-11-20'),
    ));

    Event::assertDispatched(GameFinished::class);
    Event::assertDispatched(GameRated::class, fn ($event) => $event->previousRating === null && $event->entry->rating === 8);
});

test('a game can only be added once per user', function () {
    $data = new LibraryEntryData(status: GameStatus::Backlog);
    app(AddGameToLibraryAction::class)->execute($this->user, $this->game, $data);

    app(AddGameToLibraryAction::class)->execute($this->user, $this->game, $data);
})->throws(GameAlreadyInLibrary::class);

test('platforms not listed for the game are rejected', function () {
    $other = Platform::factory()->create();

    app(AddGameToLibraryAction::class)->execute(
        $this->user,
        $this->game,
        new LibraryEntryData(status: GameStatus::Playing, platformId: $other->id),
    );
})->throws(InvalidArgumentException::class, 'not available');

test('finishing a game raises GameFinished once', function () {
    $entry = LibraryEntry::factory()->playing()->for($this->user)->for($this->game)->create();
    $finished = new LibraryEntryData(status: GameStatus::Finished, finishedOn: CarbonImmutable::parse('2026-11-25'));

    app(UpdateLibraryEntryAction::class)->execute($entry, $finished);
    app(UpdateLibraryEntryAction::class)->execute($entry, $finished);

    expect($entry->fresh()?->status)->toBe(GameStatus::Finished);
    Event::assertDispatchedTimes(GameFinished::class, 1);
});

test('changing the rating raises GameRated with the previous value', function () {
    $entry = LibraryEntry::factory()->finished()->rated(6)->for($this->user)->for($this->game)->create();

    app(UpdateLibraryEntryAction::class)->execute($entry, new LibraryEntryData(
        status: GameStatus::Finished,
        rating: 7,
        startedOn: CarbonImmutable::parse($entry->started_on?->toDateString() ?? 'today'),
        finishedOn: CarbonImmutable::parse($entry->finished_on?->toDateString() ?? 'today'),
    ));

    Event::assertDispatched(GameRated::class, fn ($event) => $event->previousRating === 6 && $event->entry->rating === 7);
});

test('clearing or keeping the rating does not raise GameRated', function () {
    $entry = LibraryEntry::factory()->playing()->rated(6)->for($this->user)->for($this->game)->create();

    app(UpdateLibraryEntryAction::class)->execute($entry, new LibraryEntryData(status: GameStatus::Playing, rating: 6));
    app(UpdateLibraryEntryAction::class)->execute($entry, new LibraryEntryData(status: GameStatus::Playing));

    expect($entry->fresh()?->rating)->toBeNull();
    Event::assertNotDispatched(GameRated::class);
});

test('removing an entry deletes it and announces the ids', function () {
    $entry = LibraryEntry::factory()->for($this->user)->for($this->game)->create();

    app(RemoveFromLibraryAction::class)->execute($entry);

    expect(LibraryEntry::query()->count())->toBe(0);
    Event::assertDispatched(
        GameRemovedFromLibrary::class,
        fn ($event) => $event->userId === $this->user->id && $event->gameId === $this->game->id,
    );
});

test('the database rejects invalid ratings, statuses and date order', function (array $attributes) {
    $entry = LibraryEntry::factory()->for($this->user)->for($this->game)->create();

    DB::table('library_entries')->where('id', $entry->id)->update($attributes);
})->with([
    'rating 11' => [['rating' => 11]],
    'rating 0' => [['rating' => 0]],
    'unknown status' => [['status' => 'wishlist']],
    'start after finish' => [['started_on' => '2026-02-02', 'finished_on' => '2026-02-01']],
])->throws(QueryException::class);

test('only the owner may update or delete an entry', function () {
    $entry = LibraryEntry::factory()->for($this->user)->for($this->game)->create();
    $stranger = User::factory()->create();

    expect($this->user->can('update', $entry))->toBeTrue()
        ->and($this->user->can('delete', $entry))->toBeTrue()
        ->and($stranger->can('update', $entry))->toBeFalse()
        ->and($stranger->can('delete', $entry))->toBeFalse();
});
