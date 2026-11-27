<?php

namespace App\Domain\Stats\Data;

/**
 * Library metrics shown on a user's profile.
 */
final readonly class ProfileStats
{
    /**
     * @param  array<int, int>  $ratingHistogram  rating 1..10 => entries (every rating present)
     * @param  array<int, int>  $finishedPerYear  year => finished games, newest first
     * @param  list<array{id: int, name: string, count: int}>  $topPlatforms
     * @param  list<array{id: int, name: string, count: int}>  $topGenres
     */
    public function __construct(
        public int $libraryCount,
        public int $finishedCount,
        public int $finishedThisYear,
        public int $playingCount,
        public int $backlogCount,
        public int $abandonedCount,
        public ?float $averageRating,
        public array $ratingHistogram,
        public array $finishedPerYear,
        public array $topPlatforms,
        public array $topGenres,
    ) {}
}
