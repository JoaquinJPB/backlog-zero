<?php

namespace App\Http\Resources\Games;

use App\Domain\Games\Models\Game;
use App\Domain\Games\Models\Platform;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Compact game shape for lists (search results).
 *
 * @mixin Game
 */
class GameSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'year' => $this->first_release_date?->year,
            'cover_image_id' => $this->cover_image_id,
            'platforms' => $this->platforms
                ->map(fn (Platform $platform): string => $platform->abbreviation ?? $platform->name)
                ->all(),
        ];
    }
}
