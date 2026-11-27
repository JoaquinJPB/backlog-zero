<?php

namespace Database\Factories;

use App\Domain\Games\Models\Genre;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Genre>
 */
class GenreFactory extends Factory
{
    protected $model = Genre::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'igdb_id' => fake()->unique()->numberBetween(1, 99_999),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
        ];
    }
}
