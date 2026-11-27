<?php

namespace App\Domain\Stats\Actions;

use App\Domain\Library\Data\LibraryEntryData;
use App\Domain\Library\Enums\GameStatus;
use App\Domain\Library\Queries\FinishedGamesQuery;
use App\Domain\Library\Queries\LibraryStatsQuery;
use App\Domain\Stats\Data\ProfileStats;
use App\Domain\Users\Models\User;

/**
 * Builds a user's profile metrics from the Library's public queries. Stats
 * has no tables of its own.
 */
final class BuildProfileStatsAction
{
    public const TOP_LIMIT = 5;

    public function __construct(
        private readonly LibraryStatsQuery $library,
        private readonly FinishedGamesQuery $finishedGames,
    ) {}

    public function execute(User $user): ProfileStats
    {
        $counts = $this->library->countsByStatus($user);
        $finishedPerYear = $this->finishedGames->years($user);
        $histogram = $this->histogram($this->library->ratingCounts($user));

        return new ProfileStats(
            libraryCount: $counts['all'],
            finishedCount: $counts[GameStatus::Finished->value],
            finishedThisYear: $finishedPerYear[now()->year] ?? 0,
            playingCount: $counts[GameStatus::Playing->value],
            backlogCount: $counts[GameStatus::Backlog->value],
            abandonedCount: $counts[GameStatus::Abandoned->value],
            averageRating: $this->average($histogram),
            ratingHistogram: $histogram,
            finishedPerYear: $finishedPerYear,
            topPlatforms: $this->library->topPlatforms($user, self::TOP_LIMIT),
            topGenres: $this->library->topGenres($user, self::TOP_LIMIT),
        );
    }

    /**
     * @param  array<int, int>  $ratingCounts
     * @return array<int, int> every rating from min to max, zero when unused
     */
    private function histogram(array $ratingCounts): array
    {
        $histogram = [];

        foreach (range(LibraryEntryData::MIN_RATING, LibraryEntryData::MAX_RATING) as $rating) {
            $histogram[$rating] = $ratingCounts[$rating] ?? 0;
        }

        return $histogram;
    }

    /**
     * @param  array<int, int>  $histogram
     */
    private function average(array $histogram): ?float
    {
        $rated = array_sum($histogram);

        if ($rated === 0) {
            return null;
        }

        $total = 0;

        foreach ($histogram as $rating => $count) {
            $total += $rating * $count;
        }

        return round($total / $rated, 1);
    }
}
