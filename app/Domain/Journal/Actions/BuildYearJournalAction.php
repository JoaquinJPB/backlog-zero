<?php

namespace App\Domain\Journal\Actions;

use App\Domain\Journal\Data\YearJournal;
use App\Domain\Library\Enums\GameStatus;
use App\Domain\Library\Models\LibraryEntry;
use App\Domain\Library\Queries\FinishedGamesQuery;
use App\Domain\Users\Models\User;

/**
 * Builds the journal for one year from the Library's public query: entries
 * grouped by the month they were finished, plus the year's summary.
 */
final class BuildYearJournalAction
{
    public function __construct(private readonly FinishedGamesQuery $finishedGames) {}

    public function execute(User $user, int $year, ?int $platformId = null, bool $includeAbandoned = false): YearJournal
    {
        $entries = $this->finishedGames->forYear($user, $year, $platformId, $includeAbandoned);

        $months = [];

        foreach ($entries as $entry) {
            if ($entry->finished_on === null) {
                continue;
            }

            $months[$entry->finished_on->month][] = $entry;
        }

        krsort($months);

        $finished = $entries->filter(fn (LibraryEntry $entry): bool => $entry->status === GameStatus::Finished);
        $ratings = $finished->pluck('rating')->filter(fn (mixed $rating): bool => is_int($rating));

        return new YearJournal(
            year: $year,
            months: $months,
            finishedCount: $finished->count(),
            abandonedCount: $entries->count() - $finished->count(),
            averageRating: $ratings->isEmpty() ? null : round((float) $ratings->avg(), 1),
        );
    }
}
