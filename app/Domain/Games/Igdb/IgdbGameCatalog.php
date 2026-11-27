<?php

namespace App\Domain\Games\Igdb;

use App\Domain\Games\Contracts\GameCatalog;
use App\Domain\Games\Data\CatalogGame;

final class IgdbGameCatalog implements GameCatalog
{
    private const GAME_FIELDS = 'name,slug,summary,cover.image_id,first_release_date,game_type,updated_at,'
        .'platforms.name,platforms.abbreviation,platforms.slug,genres.name,genres.slug';

    private const MAX_QUERY_LENGTH = 100;

    private const NAME_MATCH_LIMIT = 50;

    public function __construct(
        private readonly IgdbClient $client,
        private readonly IgdbGameMapper $mapper,
        private readonly IgdbSearchRanker $ranker,
    ) {}

    public function search(string $query, int $limit = 20): array
    {
        $term = $this->sanitize($query);

        if ($term === '') {
            return [];
        }

        $limit = max(1, min($limit, 50));

        // version_parent = null drops editions of the same game (the filter
        // IGDB's own docs use for search).
        $searchBody = sprintf(
            'search "%s"; fields %s; where version_parent = null; limit %d;',
            $term,
            self::GAME_FIELDS,
            $limit,
        );

        // IGDB's full-text search drops very common words, so a title like
        // "We Were Here" finds nothing. A name match catches those.
        $nameBody = sprintf(
            'fields %s; where name ~ *"%s"* & version_parent = null; limit %d;',
            self::GAME_FIELDS,
            $term,
            self::NAME_MATCH_LIMIT,
        );

        $games = [
            ...$this->map($this->client->query('games', $searchBody)),
            ...$this->map($this->client->query('games', $nameBody)),
        ];

        return $this->ranker->rank($games, $term, $limit);
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
