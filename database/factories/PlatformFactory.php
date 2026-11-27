<?php

namespace Database\Factories;

use App\Domain\Games\Models\Platform;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Platform>
 */
class PlatformFactory extends Factory
{
    protected $model = Platform::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = rtrim(fake()->unique()->sentence(2), '.');

        return [
            'igdb_id' => fake()->unique()->numberBetween(1, 99_999),
            'name' => Str::title($name),
            'abbreviation' => Str::upper(Str::substr($name, 0, 3)),
            'slug' => Str::slug($name),
        ];
    }
}
