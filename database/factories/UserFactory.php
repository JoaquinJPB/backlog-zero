<?php

namespace Database\Factories;

use App\Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => Str::lower(fake()->unique()->bothify('player_####??')),
            'email' => fake()->unique()->safeEmail(),
            'avatar_url' => null,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * A user who signed in but hasn't picked a username yet.
     */
    public function withoutUsername(): static
    {
        return $this->state(fn (array $attributes) => [
            'username' => null,
        ]);
    }

    public function withGoogleAccount(?string $providerUserId = null): static
    {
        return $this->has(
            SocialAccountFactory::new()->state(array_filter([
                'provider' => 'google',
                'provider_user_id' => $providerUserId,
            ])),
            'socialAccounts',
        );
    }
}
