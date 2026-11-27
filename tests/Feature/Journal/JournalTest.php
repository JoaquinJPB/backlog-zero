<?php

use App\Domain\Games\Models\Platform;
use App\Domain\Journal\Actions\BuildYearJournalAction;
use App\Domain\Library\Models\LibraryEntry;
use App\Domain\Library\Queries\FinishedGamesQuery;
use App\Domain\Users\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->travelTo('2026-11-27 12:00:00');
    $this->user = User::factory()->create();
    $this->switch = Platform::factory()->create(['name' => 'Nintendo Switch']);
    $this->pc = Platform::factory()->create(['name' => 'PC']);
});

function finishedEntry(User $user, string $on, array $attributes = []): LibraryEntry
{
    return LibraryEntry::factory()->for($user)->create([
        'status' => 'finished',
        'started_on' => null,
        'finished_on' => $on,
        ...$attributes,
    ]);
}

test('the query lists years with finished games, newest first', function () {
    finishedEntry($this->user, '2024-03-01');
    finishedEntry($this->user, '2026-01-10');
    finishedEntry($this->user, '2026-05-10');
    LibraryEntry::factory()->for($this->user)->create(['status' => 'abandoned', 'finished_on' => '2025-02-02']);
    LibraryEntry::factory()->playing()->for($this->user)->create();
    finishedEntry(User::factory()->create(), '2023-01-01');

    $query = app(FinishedGamesQuery::class);

    expect($query->years($this->user))->toBe([2026 => 2, 2024 => 1])
        ->and($query->years($this->user, includeAbandoned: true))->toBe([2026 => 2, 2025 => 1, 2024 => 1]);
});

test('the journal groups a year by month, newest first, with a summary', function () {
    $january = finishedEntry($this->user, '2026-01-10', ['rating' => 6]);
    $mayLate = finishedEntry($this->user, '2026-05-20', ['rating' => 10]);
    $mayEarly = finishedEntry($this->user, '2026-05-02');
    finishedEntry($this->user, '2025-12-31');
    LibraryEntry::factory()->for($this->user)->create(['status' => 'abandoned', 'finished_on' => '2026-05-15', 'rating' => 2]);

    $journal = app(BuildYearJournalAction::class)->execute($this->user, 2026);

    expect(array_keys($journal->months))->toBe([5, 1])
        ->and(array_map(fn ($entry) => $entry->id, $journal->months[5]))->toBe([$mayLate->id, $mayEarly->id])
        ->and($journal->months[1][0]->id)->toBe($january->id)
        ->and($journal->finishedCount)->toBe(3)
        ->and($journal->abandonedCount)->toBe(0)
        // Unrated games don't drag the average down; abandoned ones are excluded.
        ->and($journal->averageRating)->toBe(8.0);
});

test('abandoned games are only included on request', function () {
    finishedEntry($this->user, '2026-05-20');
    LibraryEntry::factory()->for($this->user)->create(['status' => 'abandoned', 'finished_on' => '2026-05-15']);

    $journal = app(BuildYearJournalAction::class)->execute($this->user, 2026, includeAbandoned: true);

    expect($journal->months[5])->toHaveCount(2)
        ->and($journal->finishedCount)->toBe(1)
        ->and($journal->abandonedCount)->toBe(1);
});

test('the journal can be filtered by platform', function () {
    $onSwitch = finishedEntry($this->user, '2026-05-20', ['platform_id' => $this->switch->id]);
    finishedEntry($this->user, '2026-06-20', ['platform_id' => $this->pc->id]);
    finishedEntry($this->user, '2025-06-20', ['platform_id' => $this->pc->id]);

    $journal = app(BuildYearJournalAction::class)->execute($this->user, 2026, $this->switch->id);
    $platforms = app(FinishedGamesQuery::class)->platformsForYear($this->user, 2026);

    expect(array_keys($journal->months))->toBe([5])
        ->and($journal->months[5][0]->id)->toBe($onSwitch->id)
        ->and($platforms->pluck('name')->all())->toBe(['Nintendo Switch', 'PC']);
});

test('guests cannot see the journal', function () {
    $this->get(route('journal.index'))->assertRedirect('/');
});

test('the page defaults to the latest year with finished games', function () {
    finishedEntry($this->user, '2025-08-14', ['platform_id' => $this->pc->id, 'started_on' => '2025-08-01']);

    $this->actingAs($this->user)
        ->get(route('journal.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('journal/index')
            ->where('year', 2025)
            ->where('years', [['year' => 2025, 'count' => 1]])
            ->where('filters', ['platform' => null, 'abandoned' => false])
            ->where('platforms', [['id' => $this->pc->id, 'name' => 'PC']])
            ->where('summary', ['finished' => 1, 'abandoned' => 0, 'average_rating' => null])
            ->has('months', 1)
            ->where('months.0.month', 8)
            ->where('months.0.entries.0.finished_on', '2025-08-14')
            ->where('months.0.entries.0.started_on', '2025-08-01')
            ->has('months.0.entries.0.game')
        );
});

test('an empty journal falls back to the current year', function () {
    $this->actingAs($this->user)
        ->get(route('journal.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('year', 2026)
            ->where('years', [])
            ->where('months', [])
        );
});

test('query string filters reach the journal', function () {
    finishedEntry($this->user, '2026-05-20', ['platform_id' => $this->switch->id]);
    finishedEntry($this->user, '2026-06-20', ['platform_id' => $this->pc->id]);
    LibraryEntry::factory()->for($this->user)->create([
        'status' => 'abandoned', 'finished_on' => '2026-05-15', 'platform_id' => $this->switch->id,
    ]);

    $this->actingAs($this->user)
        ->get(route('journal.index', ['year' => 2026, 'platform' => $this->switch->id, 'abandoned' => 1]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters', ['platform' => $this->switch->id, 'abandoned' => true])
            ->where('summary.finished', 1)
            ->where('summary.abandoned', 1)
            ->has('months.0.entries', 2)
        );
});

test('invalid filters are rejected', function (array $query) {
    $this->actingAs($this->user)
        ->get(route('journal.index', $query))
        ->assertSessionHasErrors(array_key_first($query));
})->with([
    'future year' => [['year' => 2030]],
    'year too old' => [['year' => 1900]],
    'non numeric platform' => [['platform' => 'switch']],
    'non boolean abandoned' => [['abandoned' => 'maybe']],
]);

test('the journal lists what the user is playing now, latest started first', function () {
    $older = LibraryEntry::factory()->playing()->for($this->user)->create(['started_on' => '2026-10-01']);
    $newer = LibraryEntry::factory()->playing()->for($this->user)->create(['started_on' => '2026-11-20']);
    finishedEntry($this->user, '2026-05-20');
    LibraryEntry::factory()->playing()->for(User::factory()->create())->create();

    $this->actingAs($this->user)
        ->get(route('journal.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('journal/index')
            ->has('playing', 2)
            ->where('playing.0.id', $newer->id)
            ->where('playing.1.id', $older->id)
            ->where('playing.0.status', 'playing')
            ->has('playing.0.game.slug'));
});
