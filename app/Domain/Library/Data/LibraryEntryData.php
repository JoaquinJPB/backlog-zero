<?php

namespace App\Domain\Library\Data;

use App\Domain\Library\Enums\GameStatus;
use Carbon\CarbonImmutable;
use InvalidArgumentException;

/**
 * Validated state of a library entry. Invariants are enforced here so any
 * caller (HTTP, imports, jobs) gets the same rules; Form Requests only
 * exist to turn them into friendly messages first.
 */
final readonly class LibraryEntryData
{
    public const MIN_RATING = 1;

    public const MAX_RATING = 10;

    public function __construct(
        public GameStatus $status,
        public ?int $rating = null,
        public ?int $platformId = null,
        public ?CarbonImmutable $startedOn = null,
        public ?CarbonImmutable $finishedOn = null,
    ) {
        if ($rating !== null && ($rating < self::MIN_RATING || $rating > self::MAX_RATING)) {
            throw new InvalidArgumentException('Rating must be between 1 and 10.');
        }

        if ($finishedOn !== null && ! $status->allowsFinishDate()) {
            throw new InvalidArgumentException("A {$status->value} game can't have a finish date.");
        }

        if ($startedOn !== null && $finishedOn !== null && $startedOn->gt($finishedOn)) {
            throw new InvalidArgumentException('Start date must be on or before the finish date.');
        }
    }

    /**
     * @return array{status: GameStatus, rating: int|null, platform_id: int|null, started_on: string|null, finished_on: string|null}
     */
    public function toAttributes(): array
    {
        return [
            'status' => $this->status,
            'rating' => $this->rating,
            'platform_id' => $this->platformId,
            'started_on' => $this->startedOn?->toDateString(),
            'finished_on' => $this->finishedOn?->toDateString(),
        ];
    }
}
