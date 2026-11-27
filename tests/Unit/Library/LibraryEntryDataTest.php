<?php

use App\Domain\Library\Data\LibraryEntryData;
use App\Domain\Library\Enums\GameStatus;
use Carbon\CarbonImmutable;

test('a valid entry maps to model attributes', function () {
    $data = new LibraryEntryData(
        status: GameStatus::Finished,
        rating: 9,
        platformId: 6,
        startedOn: CarbonImmutable::parse('2026-01-05'),
        finishedOn: CarbonImmutable::parse('2026-02-10'),
    );

    expect($data->toAttributes())->toBe([
        'status' => GameStatus::Finished,
        'rating' => 9,
        'platform_id' => 6,
        'started_on' => '2026-01-05',
        'finished_on' => '2026-02-10',
    ]);
});

test('ratings must be between 1 and 10', function (int $rating) {
    new LibraryEntryData(status: GameStatus::Playing, rating: $rating);
})->with([0, 11, -1])->throws(InvalidArgumentException::class, 'between 1 and 10');

test('only finished or abandoned games can have a finish date', function (GameStatus $status, bool $allowed) {
    $build = fn () => new LibraryEntryData(status: $status, finishedOn: CarbonImmutable::parse('2026-02-10'));

    $allowed
        ? expect($build())->toBeInstanceOf(LibraryEntryData::class)
        : expect($build)->toThrow(InvalidArgumentException::class);
})->with([
    'finished' => [GameStatus::Finished, true],
    'abandoned' => [GameStatus::Abandoned, true],
    'playing' => [GameStatus::Playing, false],
    'backlog' => [GameStatus::Backlog, false],
]);

test('the start date cannot be after the finish date', function () {
    new LibraryEntryData(
        status: GameStatus::Finished,
        startedOn: CarbonImmutable::parse('2026-03-01'),
        finishedOn: CarbonImmutable::parse('2026-02-01'),
    );
})->throws(InvalidArgumentException::class, 'Start date');

test('status values match the database check constraint', function () {
    expect(GameStatus::values())->toBe(['playing', 'finished', 'abandoned', 'backlog']);
});
