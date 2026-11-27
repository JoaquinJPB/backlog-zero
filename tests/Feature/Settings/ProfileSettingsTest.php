<?php

use App\Domain\Users\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->create([
        'name' => 'Ada Lovelace',
        'username' => 'ada',
        'email' => 'ada@example.com',
    ]);
});

test('guests cannot see profile settings', function () {
    $this->get(route('settings.profile'))->assertRedirect('/');
    $this->patch(route('settings.profile.update'), ['name' => 'X', 'username' => 'xyz'])
        ->assertRedirect('/');
});

test('the profile form shows the current values', function () {
    $this->actingAs($this->user)
        ->get(route('settings.profile'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/profile')
            ->where('profile', [
                'name' => 'Ada Lovelace',
                'username' => 'ada',
                'email' => 'ada@example.com',
            ])
            ->where('rules.name_max', 50)
        );
});

test('users can update their name and username', function () {
    $this->actingAs($this->user)
        ->patch(route('settings.profile.update'), [
            'name' => '  Augusta Ada  ',
            'username' => 'Countess_Ada',
        ])
        ->assertRedirect(route('settings.profile'))
        ->assertInertiaFlash('success', __('ui.settings.saved'));

    $user = $this->user->fresh();
    expect($user?->name)->toBe('Augusta Ada')
        ->and($user?->username)->toBe('countess_ada');
});

test('keeping your own username is not a conflict', function () {
    $this->actingAs($this->user)
        ->patch(route('settings.profile.update'), ['name' => 'Ada', 'username' => 'ADA'])
        ->assertSessionHasNoErrors();

    expect($this->user->fresh()?->name)->toBe('Ada');
});

test('another user\'s username is rejected', function () {
    User::factory()->create(['username' => 'grace']);

    $this->actingAs($this->user)
        ->patch(route('settings.profile.update'), ['name' => 'Ada', 'username' => 'Grace'])
        ->assertSessionHasErrors('username');

    expect($this->user->fresh()?->username)->toBe('ada');
});

test('name is required and limited in length', function (string $name) {
    $this->actingAs($this->user)
        ->patch(route('settings.profile.update'), ['name' => $name, 'username' => 'ada'])
        ->assertSessionHasErrors('name');
})->with([
    'empty' => [''],
    'only spaces' => ['   '],
    'too long' => [str_repeat('a', 51)],
]);

test('email cannot be changed from settings', function () {
    $this->actingAs($this->user)
        ->patch(route('settings.profile.update'), [
            'name' => 'Ada',
            'username' => 'ada',
            'email' => 'hacker@example.com',
        ]);

    expect($this->user->fresh()?->email)->toBe('ada@example.com');
});
