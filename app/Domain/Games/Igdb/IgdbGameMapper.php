<?php

namespace App\Domain\Games\Igdb;

use App\Domain\Games\Data\CatalogGame;
use App\Domain\Games\Data\CatalogTerm;
use Carbon\CarbonImmutable;

/**
 * Maps raw IGDB rows (untrusted JSON) into catalog DTOs, validating every
 * field. Rows without id, name or slug are skipped.
 */
final class IgdbGameMapper
{
    /**
     * @param  array<mixed>  $row
     */
    public function toCatalogGame(array $row): ?CatalogGame
    {
        $id = $row['id'] ?? null;
        $name = $this->string($row['name'] ?? null);
        $slug = $this->string($row['slug'] ?? null);

        if (! is_int($id) || $name === null || $slug === null) {
            return null;
        }

        $cover = $row['cover'] ?? null;

        return new CatalogGame(
            igdbId: $id,
            slug: $slug,
            name: $name,
            summary: $this->string($row['summary'] ?? null),
            coverImageId: is_array($cover) ? $this->string($cover['image_id'] ?? null) : null,
            firstReleaseDate: $this->timestamp($row['first_release_date'] ?? null),
            gameTypeId: is_int($row['game_type'] ?? null) ? $row['game_type'] : null,
            updatedAt: $this->timestamp($row['updated_at'] ?? null),
            platforms: $this->terms($row['platforms'] ?? null),
            genres: $this->terms($row['genres'] ?? null),
        );
    }

    /**
     * @return list<CatalogTerm>
     */
    private function terms(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        $terms = [];

        foreach ($items as $item) {
            if (! is_array($item) || ! is_int($item['id'] ?? null)) {
                continue;
            }

            $name = $this->string($item['name'] ?? null);
            $slug = $this->string($item['slug'] ?? null);

            if ($name === null || $slug === null) {
                continue;
            }

            $terms[] = new CatalogTerm(
                igdbId: $item['id'],
                name: $name,
                slug: $slug,
                abbreviation: $this->string($item['abbreviation'] ?? null),
            );
        }

        return $terms;
    }

    private function string(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * IGDB timestamps are Unix seconds (UTC).
     */
    private function timestamp(mixed $value): ?CarbonImmutable
    {
        return is_int($value) ? CarbonImmutable::createFromTimestampUTC($value) : null;
    }
}
