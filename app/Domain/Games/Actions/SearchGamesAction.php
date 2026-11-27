<?php

namespace App\Domain\Games\Actions;

use App\Domain\Games\Contracts\GameCatalog;
use App\Domain\Games\Exceptions\CatalogUnavailable;
use App\Domain\Games\Models\Game;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

/**
 * Searches the catalog and imports every result into the local `games`
 * table, so game pages never need IGDB. Results are cached per query.
 */
final class SearchGamesAction
{
    public const MIN_QUERY_LENGTH = 2;

    public const MAX_QUERY_LENGTH = 100;

    private const RESULTS_LIMIT = 20;

    private const CACHE_TTL_HOURS = 6;

    public function __construct(
        private readonly GameCatalog $catalog,
        private readonly UpsertGameAction $upsert,
        private readonly Cache $cache,
    ) {}

    /**
     * @return Collection<int, Game>
     *
     * @throws CatalogUnavailable
     */
    public function execute(string $query): Collection
    {
        $normalized = Str::of($query)->squish()->lower()->limit(self::MAX_QUERY_LENGTH, '')->toString();

        if (mb_strlen($normalized) < self::MIN_QUERY_LENGTH) {
            return new Collection;
        }

        $cacheKey = 'games.search.'.sha1($normalized);
        $ids = $this->cachedIds($cacheKey);

        if ($ids === null) {
            $ids = [];

            foreach ($this->catalog->search($normalized, self::RESULTS_LIMIT) as $catalogGame) {
                $ids[] = $this->upsert->execute($catalogGame)->id;
            }

            $this->cache->put($cacheKey, $ids, now()->addHours(self::CACHE_TTL_HOURS));
        }

        return $this->loadInOrder($ids);
    }

    /**
     * @return list<int>|null
     */
    private function cachedIds(string $key): ?array
    {
        $ids = $this->cache->get($key);

        if (! is_array($ids) || ! array_is_list($ids)) {
            return null;
        }

        return array_values(array_filter($ids, is_int(...)));
    }

    /**
     * Keeps the catalog's relevance order.
     *
     * @param  list<int>  $ids
     * @return Collection<int, Game>
     */
    private function loadInOrder(array $ids): Collection
    {
        if ($ids === []) {
            return new Collection;
        }

        $games = Game::query()->with('platforms')->whereKey($ids)->get()->keyBy('id');

        return new Collection(array_values(array_filter(
            array_map(fn (int $id): ?Game => $games->get($id), $ids),
        )));
    }
}
