<?php

use App\Domain\Users\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('home shares the spanish locale and ui translations', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('welcome')
            ->where('locale', 'es')
            ->where('translations.welcome.heading', 'Deja el backlog a cero')
            ->where('translations.footer.igdb_attribution', 'Game data provided by :link')
            ->where('auth.user', null)
        );
});

test('the shared user exposes only public fields', function () {
    $user = User::factory()->create([
        'name' => 'Ada',
        'username' => 'ada',
        'email' => 'ada@example.com',
    ]);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('auth.user', [
                'id' => $user->id,
                'name' => 'Ada',
                'username' => 'ada',
                'avatar_url' => null,
            ])
        );
});
