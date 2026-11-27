<?php

namespace App\Domain\Games\Actions;

use App\Domain\Games\Data\CatalogGame;
use App\Domain\Games\Data\CatalogTerm;
use App\Domain\Games\Models\Game;
use App\Domain\Games\Models\Genre;
use App\Domain\Games\Models\Platform;
use Illuminate\Support\Facades\DB;

/**
 * Creates or updates the local copy of a catalog game (matched by igdb_id),
 * including its platforms and genres, and marks it as freshly synced.
 */
final class UpsertGameAction
{
    /** Slugs that would collide with fixed routes under /games. */
    private const RESERVED_SLUGS = ['search'];

    public function execute(CatalogGame $data): Game
    {
        return DB::transaction(function () use ($data): Game {
            $game = Game::query()->firstOrNew(['igdb_id' => $data->igdbId]);

            $game->fill([
                'slug' => $this->availableSlug($data),
                'name' => $data->name,
                'summary' => $data->summary,
                'cover_image_id' => $data->coverImageId,
                'first_release_date' => $data->firstReleaseDate?->toDateString(),
                'game_type_id' => $data->gameTypeId,
                'igdb_updated_at' => $data->updatedAt,
                'synced_at' => now(),
            ])->save();

            $game->platforms()->sync($this->upsertPlatforms($data->platforms));
            $game->genres()->sync($this->upsertGenres($data->genres));

            return $game;
        });
    }

    /**
     * IGDB slugs are unique, but a reserved word or a slug freed and reused
     * by another IGDB game would collide; suffixing the id keeps both.
     */
    private function availableSlug(CatalogGame $data): string
    {
        $taken = in_array($data->slug, self::RESERVED_SLUGS, true)
            || Game::query()
                ->where('slug', $data->slug)
                ->where('igdb_id', '!=', $data->igdbId)
                ->exists();

        return $taken ? "{$data->slug}-{$data->igdbId}" : $data->slug;
    }

    /**
     * @param  list<CatalogTerm>  $terms
     * @return list<int>
     */
    private function upsertPlatforms(array $terms): array
    {
        $rows = array_map(fn (CatalogTerm $term): array => [
            'igdb_id' => $term->igdbId,
            'name' => $term->name,
            'abbreviation' => $term->abbreviation,
            'slug' => $term->slug,
        ], $this->unique($terms));

        if ($rows === []) {
            return [];
        }

        Platform::query()->upsert($rows, ['igdb_id'], ['name', 'abbreviation', 'slug']);

        return $this->idsFor(Platform::query()->whereIn('igdb_id', array_column($rows, 'igdb_id'))->pluck('id')->all());
    }

    /**
     * @param  list<CatalogTerm>  $terms
     * @return list<int>
     */
    private function upsertGenres(array $terms): array
    {
        $rows = array_map(fn (CatalogTerm $term): array => [
            'igdb_id' => $term->igdbId,
            'name' => $term->name,
            'slug' => $term->slug,
        ], $this->unique($terms));

        if ($rows === []) {
            return [];
        }

        Genre::query()->upsert($rows, ['igdb_id'], ['name', 'slug']);

        return $this->idsFor(Genre::query()->whereIn('igdb_id', array_column($rows, 'igdb_id'))->pluck('id')->all());
    }

    /**
     * Postgres rejects an upsert that touches the same row twice.
     *
     * @param  list<CatalogTerm>  $terms
     * @return list<CatalogTerm>
     */
    private function unique(array $terms): array
    {
        $byId = [];

        foreach ($terms as $term) {
            $byId[$term->igdbId] = $term;
        }

        return array_values($byId);
    }

    /**
     * @param  array<mixed>  $ids
     * @return list<int>
     */
    private function idsFor(array $ids): array
    {
        return array_values(array_filter($ids, is_int(...)));
    }
}
