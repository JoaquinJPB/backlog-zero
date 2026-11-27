<?php

namespace App\Domain\Journal\Data;

use App\Domain\Library\Models\LibraryEntry;

/**
 * A year of finished (and optionally abandoned) games, grouped by month.
 */
final readonly class YearJournal
{
    /**
     * @param  array<int, list<LibraryEntry>>  $months  month number (12..1) => entries, newest first
     */
    public function __construct(
        public int $year,
        public array $months,
        public int $finishedCount,
        public int $abandonedCount,
        public ?float $averageRating,
    ) {}
}
