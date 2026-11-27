<?php

use App\Domain\Users\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->withoutUsername()->create([
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
    ]);
});

test('users without username are sent to onboarding', function () {
    $this->actingAs($this->user)
        ->get(route('home'))
        ->assertRedirect(route('onboarding.username'));
});

test('users without username can still log out', function () {
    $this->actingAs($this->user)
        ->post(route('logout'))
        ->assertRedirect(route('home'));

    $this->assertGuest();
});

test('guests are not sent to onboarding', function () {
    $this->get(route('home'))->assertOk();
    $this->get(route('onboarding.username'))->assertRedirect('/');
});

test('onboarding suggests a username from the name', function () {
    $this->actingAs($this->user)
        ->get(route('onboarding.username'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('onboarding/username')
            ->where('suggestion', 'ada_lovelace')
            ->where('rules', ['min' => 3, 'max' => 20])
        );
});

test('the suggestion skips usernames that are taken', function () {
    User::factory()->create(['username' => 'ada_lovelace']);
    User::factory()->create(['username' => 'ada_lovelace2']);

    $this->actingAs($this->user)
        ->get(route('onboarding.username'))
        ->assertInertia(fn (Assert $page) => $page->where('suggestion', 'ada_lovelace3'));
});

test('choosing a username completes onboarding', function () {
    $this->actingAs($this->user)
        ->post(route('onboarding.username.store'), ['username' => '  Ada_L '])
        ->assertRedirect(route('home'))
        ->assertInertiaFlash('success');

    expect($this->user->fresh()?->username)->toBe('ada_l');

    $this->get(route('home'))->assertOk();
});

test('invalid usernames are rejected', function (string $username, string $messageKey) {
    $this->actingAs($this->user)
        ->from(route('onboarding.username'))
        ->post(route('onboarding.username.store'), ['username' => $username])
        ->assertRedirect(route('onboarding.username'))
        ->assertSessionHasErrors(['username' => __($messageKey, ['min' => 3, 'max' => 20])]);

    expect($this->user->fresh()?->username)->toBeNull();
})->with([
    'too short' => ['ab', 'validation.username.length'],
    'too long' => [str_repeat('a', 21), 'validation.username.length'],
    'bad characters' => ['ada-lovelace', 'validation.username.format'],
    'accents' => ['adá', 'validation.username.format'],
    'reserved' => ['Settings', 'validation.username.reserved'],
]);

test('usernames are unique regardless of case', function () {
    User::factory()->create(['username' => 'ada']);

    $this->actingAs($this->user)
        ->post(route('onboarding.username.store'), ['username' => 'ADA'])
        ->assertSessionHasErrors('username');

    expect($this->user->fresh()?->username)->toBeNull();
});

test('users who already have a username cannot use onboarding', function () {
    $user = User::factory()->create(['username' => 'ada']);

    $this->actingAs($user)->get(route('onboarding.username'))->assertRedirect(route('home'));

    $this->actingAs($user)
        ->post(route('onboarding.username.store'), ['username' => 'other'])
        ->assertForbidden();

    expect($user->fresh()?->username)->toBe('ada');
});
