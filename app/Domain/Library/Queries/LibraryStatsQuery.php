<?php

namespace App\Domain\Library\Queries;

use App\Domain\Library\Enums\GameStatus;
use App\Domain\Library\Models\LibraryEntry;
use App\Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use stdClass;

/**
 * Public, read-only aggregates over a user's library, for the Stats module
 * and the library tabs. "Played" means any status except backlog.
 */
final class LibraryStatsQuery
{
    /**
     * @return array<string, int> 'all' plus one key per status value
     */
    public function countsByStatus(User $user): array
    {
        $byStatus = $this->entries($user)
            ->toBase()
            ->selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $counts = ['all' => (int) $byStatus->sum()];

        foreach (GameStatus::cases() as $case) {
            $counts[$case->value] = (int) ($byStatus[$case->value] ?? 0);
        }

        return $counts;
    }

    /**
     * Ratings of played games. Only ratings that occur are returned.
     *
     * @return array<int, int> rating (1-10) => number of entries
     */
    public function ratingCounts(User $user): array
    {
        $rows = $this->playedEntries($user)
            ->whereNotNull('rating')
            ->toBase()
            ->selectRaw('rating, COUNT(*) AS total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $counts = [];

        foreach ($rows as $rating => $total) {
            $counts[(int) $rating] = (int) $total;
        }

        return $counts;
    }

    /**
     * Platforms of played games, most played first (ties by name).
     *
     * @return list<array{id: int, name: string, count: int}>
     */
    public function topPlatforms(User $user, int $limit): array
    {
        $rows = $this->playedEntries($user)
            ->join('platforms', 'platforms.id', '=', 'library_entries.platform_id')
            ->toBase()
            ->selectRaw('platforms.id, platforms.name, COUNT(*) AS total')
            ->groupBy('platforms.id', 'platforms.name')
            ->orderByDesc('total')
            ->orderBy('platforms.name')
            ->limit($limit)
            ->get();

        return $this->toRanking($rows->all());
    }

    /**
     * Genres of played games, most played first (ties by name). A game with
     * several genres counts once for each.
     *
     * @return list<array{id: int, name: string, count: int}>
     */
    public function topGenres(User $user, int $limit): array
    {
        $rows = $this->playedEntries($user)
            ->join('game_genre', 'game_genre.game_id', '=', 'library_entries.game_id')
            ->join('genres', 'genres.id', '=', 'game_genre.genre_id')
            ->toBase()
            ->selectRaw('genres.id, genres.name, COUNT(*) AS total')
            ->groupBy('genres.id', 'genres.name')
            ->orderByDesc('total')
            ->orderBy('genres.name')
            ->limit($limit)
            ->get();

        return $this->toRanking($rows->all());
    }

    /**
     * Played games, most recent first: by finish date, else start date;
     * undated entries last.
     *
     * @return LengthAwarePaginator<int, LibraryEntry>
     */
    public function played(User $user, int $perPage): LengthAwarePaginator
    {
        return $this->playedEntries($user)
            ->with(['game.platforms', 'platform'])
            ->orderByRaw('COALESCE(finished_on, started_on) DESC NULLS LAST')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * @return Builder<LibraryEntry>
     */
    private function entries(User $user): Builder
    {
        return LibraryEntry::query()->where('library_entries.user_id', $user->id);
    }

    /**
     * @return Builder<LibraryEntry>
     */
    private function playedEntries(User $user): Builder
    {
        return $this->entries($user)->whereIn('library_entries.status', GameStatus::played());
    }

    /**
     * @param  array<int, stdClass>  $rows  rows with id, name and total
     * @return list<array{id: int, name: string, count: int}>
     */
    private function toRanking(array $rows): array
    {
        return array_values(array_map(fn (stdClass $row): array => [
            'id' => (int) $row->id,
            'name' => (string) $row->name,
            'count' => (int) $row->total,
        ], $rows));
    }
}
