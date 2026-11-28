<?php

use App\Domain\Users\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->create(['username' => 'ada']);
});

test('guests are sent home', function () {
    $this->get(route('friends.index'))->assertRedirect('/');
});

test('everyone else with an account is listed, by username', function () {
    User::factory()->create(['name' => 'Zoe Quinn', 'username' => 'zoe']);
    User::factory()->create(['name' => 'Bob Ross', 'username' => 'bob']);

    $this->actingAs($this->user)
        ->get(route('friends.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('friends/index')
            ->has('friends', 2)
            ->where('friends.0', ['name' => 'Bob Ross', 'username' => 'bob', 'avatar_url' => null])
            ->where('friends.1.username', 'zoe')
        );
});

test('you are not your own friend, and neither is anyone still onboarding', function () {
    User::factory()->create(['username' => null]);

    $this->actingAs($this->user)
        ->get(route('friends.index'))
        ->assertInertia(fn (Assert $page) => $page->has('friends', 0));
});
