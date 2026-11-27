<?php

namespace App\Http\Resources\Games;

use App\Domain\Games\Models\Game;
use App\Domain\Games\Models\Genre;
use App\Domain\Games\Models\Platform;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Game
 */
class GameResource extends JsonResource
{
    private const IGDB_GAME_URL = 'https://www.igdb.com/games/';

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'summary' => $this->summary,
            'cover_image_id' => $this->cover_image_id,
            'release_date' => $this->first_release_date?->toDateString(),
            'year' => $this->first_release_date?->year,
            'platforms' => $this->platforms->map(fn (Platform $platform): array => [
                'name' => $platform->name,
                'abbreviation' => $platform->abbreviation,
            ])->all(),
            'genres' => $this->genres->map(fn (Genre $genre): string => $genre->name)->all(),
            'igdb_url' => self::IGDB_GAME_URL.$this->slug,
        ];
    }
}
