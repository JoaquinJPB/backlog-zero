<?php

namespace Database\Factories;

use App\Domain\Games\Models\Game;
use App\Domain\Library\Enums\GameStatus;
use App\Domain\Library\Models\LibraryEntry;
use App\Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LibraryEntry>
 */
class LibraryEntryFactory extends Factory
{
    protected $model = LibraryEntry::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'game_id' => Game::factory(),
            'status' => GameStatus::Backlog,
            'rating' => null,
            'platform_id' => null,
            'started_on' => null,
            'finished_on' => null,
        ];
    }

    public function playing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GameStatus::Playing,
            'started_on' => now()->subWeeks(2)->toDateString(),
        ]);
    }

    public function finished(?string $on = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GameStatus::Finished,
            'started_on' => now()->subMonth()->toDateString(),
            'finished_on' => $on ?? now()->subDay()->toDateString(),
        ]);
    }

    public function rated(int $rating): static
    {
        return $this->state(fn (array $attributes) => ['rating' => $rating]);
    }
}
