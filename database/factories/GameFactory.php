<?php

namespace Database\Factories;

use App\Domain\Games\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    protected $model = Game::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::title(rtrim(fake()->unique()->sentence(3), '.'));

        return [
            'igdb_id' => fake()->unique()->numberBetween(1, 9_999_999),
            'slug' => Str::slug($name),
            'name' => $name,
            'summary' => fake()->paragraph(),
            'cover_image_id' => Str::lower(Str::random(20)),
            'first_release_date' => fake()->date(),
            'game_type_id' => 0,
            'igdb_updated_at' => now()->subMonth(),
            'synced_at' => now(),
        ];
    }

    public function stale(): static
    {
        return $this->state(fn (array $attributes) => [
            'synced_at' => now()->subDays(Game::STALE_AFTER_DAYS + 1),
        ]);
    }
}
