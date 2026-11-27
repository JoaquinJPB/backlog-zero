<?php

use App\Domain\Games\Models\Game;
use App\Domain\Games\Models\Genre;
use App\Domain\Games\Models\Platform;
use App\Domain\Library\Models\LibraryEntry;
use App\Domain\Library\Queries\LibraryStatsQuery;
use App\Domain\Stats\Actions\BuildProfileStatsAction;
use App\Domain\Users\Models\User;

beforeEach(function () {
    $this->travelTo('2026-11-27 12:00:00');
    $this->user = User::factory()->create();
});

function entryFor(User $user, string $status, array $attributes = []): LibraryEntry
{
    return LibraryEntry::factory()->for($user)->create(['status' => $status, ...$attributes]);
}

test('an empty library has zeroed stats', function () {
    $stats = app(BuildProfileStatsAction::class)->execute($this->user);

    expect($stats->libraryCount)->toBe(0)
        ->and($stats->finishedCount)->toBe(0)
        ->and($stats->finishedThisYear)->toBe(0)
        ->and($stats->averageRating)->toBeNull()
        ->and($stats->ratingHistogram)->toBe([1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0])
        ->and($stats->finishedPerYear)->toBe([])
        ->and($stats->topPlatforms)->toBe([])
        ->and($stats->topGenres)->toBe([]);
});

test('counts every status and finished games per year', function () {
    entryFor($this->user, 'finished', ['finished_on' => '2026-03-01']);
    entryFor($this->user, 'finished', ['finished_on' => '2026-07-01']);
    entryFor($this->user, 'finished', ['finished_on' => '2024-07-01']);
    entryFor($this->user, 'finished'); // no date: counts all-time, not per year
    entryFor($this->user, 'playing');
    entryFor($this->user, 'abandoned', ['finished_on' => '2026-02-02']);
    entryFor($this->user, 'backlog');
    entryFor($this->user, 'backlog');
    entryFor(User::factory()->create(), 'finished', ['finished_on' => '2026-01-01']);

    $stats = app(BuildProfileStatsAction::class)->execute($this->user);

    expect($stats->libraryCount)->toBe(8)
        ->and($stats->finishedCount)->toBe(4)
        ->and($stats->playingCount)->toBe(1)
        ->and($stats->abandonedCount)->toBe(1)
        ->and($stats->backlogCount)->toBe(2)
        ->and($stats->finishedThisYear)->toBe(2)
        ->and($stats->finishedPerYear)->toBe([2026 => 2, 2024 => 1]);
});

test('the average and histogram use rated games that were played, not the backlog', function () {
    entryFor($this->user, 'finished', ['rating' => 10]);
    entryFor($this->user, 'finished', ['rating' => 7]);
    entryFor($this->user, 'abandoned', ['rating' => 2]);
    entryFor($this->user, 'playing', ['rating' => 7]);
    entryFor($this->user, 'finished'); // unrated
    entryFor($this->user, 'backlog', ['rating' => 1]);

    $stats = app(BuildProfileStatsAction::class)->execute($this->user);

    expect($stats->averageRating)->toBe(6.5)
        ->and($stats->ratingHistogram[10])->toBe(1)
        ->and($stats->ratingHistogram[7])->toBe(2)
        ->and($stats->ratingHistogram[2])->toBe(1)
        ->and($stats->ratingHistogram[1])->toBe(0);
});

test('top platforms and genres come from played games, most played first', function () {
    $switch = Platform::factory()->create(['name' => 'Nintendo Switch']);
    $pc = Platform::factory()->create(['name' => 'PC']);
    $ps5 = Platform::factory()->create(['name' => 'PlayStation 5']);
    $rpg = Genre::factory()->create(['name' => 'Role-playing (RPG)']);
    $indie = Genre::factory()->create(['name' => 'Indie']);

    $hades = Game::factory()->create();
    $hades->genres()->attach([$rpg->id, $indie->id]);
    $zelda = Game::factory()->create();
    $zelda->genres()->attach($rpg);
    $celeste = Game::factory()->create();
    $celeste->genres()->attach($indie);
    $backlogged = Game::factory()->create();
    $backlogged->genres()->attach($indie);

    entryFor($this->user, 'finished', ['game_id' => $hades->id, 'platform_id' => $pc->id]);
    entryFor($this->user, 'playing', ['game_id' => $zelda->id, 'platform_id' => $switch->id]);
    entryFor($this->user, 'abandoned', ['game_id' => $celeste->id, 'platform_id' => $switch->id]);
    entryFor($this->user, 'backlog', ['game_id' => $backlogged->id, 'platform_id' => $ps5->id]);

    $stats = app(BuildProfileStatsAction::class)->execute($this->user);

    expect($stats->topPlatforms)->toBe([
        ['id' => $switch->id, 'name' => 'Nintendo Switch', 'count' => 2],
        ['id' => $pc->id, 'name' => 'PC', 'count' => 1],
    ])->and($stats->topGenres)->toBe([
        ['id' => $indie->id, 'name' => 'Indie', 'count' => 2],
        ['id' => $rpg->id, 'name' => 'Role-playing (RPG)', 'count' => 2],
    ]);
});

test('top lists are capped', function () {
    foreach (range(1, BuildProfileStatsAction::TOP_LIMIT + 2) as $i) {
        entryFor($this->user, 'finished', ['platform_id' => Platform::factory()->create()->id]);
    }

    $stats = app(BuildProfileStatsAction::class)->execute($this->user);

    expect($stats->topPlatforms)->toHaveCount(BuildProfileStatsAction::TOP_LIMIT);
});

test('played games exclude the backlog and are ordered by most recent date', function () {
    $old = entryFor($this->user, 'finished', ['started_on' => '2024-01-01', 'finished_on' => '2024-02-01']);
    $recent = entryFor($this->user, 'finished', ['started_on' => '2026-01-01', 'finished_on' => '2026-06-01']);
    $playing = entryFor($this->user, 'playing', ['started_on' => '2026-10-01']);
    $undated = entryFor($this->user, 'abandoned');
    entryFor($this->user, 'backlog');
    entryFor(User::factory()->create(), 'finished', ['finished_on' => '2026-11-01']);

    $played = app(LibraryStatsQuery::class)->played($this->user, perPage: 10);

    expect($played->total())->toBe(4)
        ->and($played->getCollection()->pluck('id')->all())
        ->toBe([$playing->id, $recent->id, $old->id, $undated->id]);
});
