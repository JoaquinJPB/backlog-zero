<?php

namespace App\Domain\Games\Igdb;

use App\Domain\Games\Contracts\GameCatalog;
use App\Domain\Games\Data\CatalogGame;

final class IgdbGameCatalog implements GameCatalog
{
    private const GAME_FIELDS = 'name,slug,summary,cover.image_id,first_release_date,game_type,updated_at,'
        .'platforms.name,platforms.abbreviation,platforms.slug,genres.name,genres.slug';

    private const MAX_QUERY_LENGTH = 100;

    public function __construct(
        private readonly IgdbClient $client,
        private readonly IgdbGameMapper $mapper,
    ) {}

    public function search(string $query, int $limit = 20): array
    {
        $term = $this->sanitize($query);

        if ($term === '') {
            return [];
        }

        // version_parent = null drops editions/bundles of the same game
        // (the filter IGDB's own docs use for search).
        $body = sprintf(
            'search "%s"; fields %s; where version_parent = null; limit %d;',
            $term,
            self::GAME_FIELDS,
            max(1, min($limit, 50)),
        );

        return $this->map($this->client->query('games', $body));
    }

    public function find(int $igdbId): ?CatalogGame
    {
        $body = sprintf('fields %s; where id = %d; limit 1;', self::GAME_FIELDS, $igdbId);

        return $this->map($this->client->query('games', $body))[0] ?? null;
    }

    /**
     * The search term is embedded in a quoted Apicalypse string, so quotes,
     * backslashes, semicolons and control characters are removed.
     */
    private function sanitize(string $query): string
    {
        $clean = preg_replace('/["\\\\;\p{C}]+/u', ' ', $query) ?? '';
        $clean = preg_replace('/\s+/u', ' ', $clean) ?? '';

        return mb_substr(trim($clean), 0, self::MAX_QUERY_LENGTH);
    }

    /**
     * @param  list<array<mixed>>  $rows
     * @return list<CatalogGame>
     */
    private function map(array $rows): array
    {
        $games = [];

        foreach ($rows as $row) {
            $game = $this->mapper->toCatalogGame($row);

            if ($game !== null) {
                $games[] = $game;
            }
        }

        return $games;
    }
}
