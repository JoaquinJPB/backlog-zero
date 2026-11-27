<?php

namespace App\Domain\Library\Queries;

use App\Domain\Games\Models\Platform;
use App\Domain\Library\Enums\GameStatus;
use App\Domain\Library\Models\LibraryEntry;
use App\Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Public, read-only view of the games a user finished (or abandoned), by
 * year. Other modules (Journal, later Lists/Stats) use this instead of
 * querying library tables themselves.
 */
final class FinishedGamesQuery
{
    /**
     * Newest first.
     *
     * @return Collection<int, LibraryEntry>
     */
    public function forYear(User $user, int $year, ?int $platformId = null, bool $includeAbandoned = false): Collection
    {
        return $this->base($user, $includeAbandoned)
            ->whereBetween('finished_on', ["{$year}-01-01", "{$year}-12-31"])
            ->when($platformId !== null, fn (Builder $query) => $query->where('platform_id', $platformId))
            ->with(['game.platforms', 'platform'])
            ->orderByDesc('finished_on')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Years with at least one entry, newest first.
     *
     * @return array<int, int> year => number of entries
     */
    public function years(User $user, bool $includeAbandoned = false): array
    {
        $rows = $this->base($user, $includeAbandoned)
            ->toBase()
            ->selectRaw('CAST(EXTRACT(YEAR FROM finished_on) AS INTEGER) AS year, COUNT(*) AS total')
            ->groupByRaw('1')
            ->orderByDesc('year')
            ->get();

        $years = [];

        foreach ($rows as $row) {
            $years[(int) $row->year] = (int) $row->total;
        }

        return $years;
    }

    /**
     * Platforms used in that year's entries, for the filter.
     *
     * @return Collection<int, Platform>
     */
    public function platformsForYear(User $user, int $year, bool $includeAbandoned = false): Collection
    {
        $platformIds = $this->base($user, $includeAbandoned)
            ->whereBetween('finished_on', ["{$year}-01-01", "{$year}-12-31"])
            ->whereNotNull('platform_id')
            ->distinct()
            ->pluck('platform_id');

        return Platform::query()->whereKey($platformIds)->orderBy('name')->get();
    }

    /**
     * @return Builder<LibraryEntry>
     */
    private function base(User $user, bool $includeAbandoned): Builder
    {
        $statuses = $includeAbandoned
            ? [GameStatus::Finished, GameStatus::Abandoned]
            : [GameStatus::Finished];

        return LibraryEntry::query()
            ->where('user_id', $user->id)
            ->whereIn('status', $statuses)
            ->whereNotNull('finished_on');
    }
}
