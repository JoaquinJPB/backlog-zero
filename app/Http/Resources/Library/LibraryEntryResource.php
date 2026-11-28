<?php

namespace App\Http\Resources\Library;

use App\Domain\Library\Models\LibraryEntry;
use App\Http\Resources\Games\GameSummaryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin LibraryEntry
 */
class LibraryEntryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'rating' => $this->rating,
            'completed' => $this->completed,
            'platform_id' => $this->platform_id,
            'platform' => $this->whenLoaded('platform', fn (): ?string => $this->platform?->name),
            'started_on' => $this->started_on?->toDateString(),
            'finished_on' => $this->finished_on?->toDateString(),
            'game' => $this->whenLoaded('game', fn (): array => (new GameSummaryResource($this->game))->resolve($request)),
        ];
    }
}
