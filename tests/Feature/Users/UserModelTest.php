<?php

use App\Domain\Users\Models\SocialAccount;
use App\Domain\Users\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Schema;

test('users table has no password columns', function () {
    expect(Schema::hasColumn('users', 'password'))->toBeFalse()
        ->and(Schema::hasTable('password_reset_tokens'))->toBeFalse();
});

test('a user can have a google social account', function () {
    $user = User::factory()->withGoogleAccount('google-123')->create();

    expect($user->socialAccounts)->toHaveCount(1)
        ->and($user->socialAccounts->first()?->provider)->toBe('google')
        ->and($user->socialAccounts->first()?->provider_user_id)->toBe('google-123');
});

test('the same provider identity cannot belong to two users', function () {
    SocialAccount::factory()->create(['provider' => 'google', 'provider_user_id' => 'dup']);

    SocialAccount::factory()->create(['provider' => 'google', 'provider_user_id' => 'dup']);
})->throws(UniqueConstraintViolationException::class);

test('usernames are unique', function () {
    User::factory()->create(['username' => 'taken']);

    User::factory()->create(['username' => 'taken']);
})->throws(UniqueConstraintViolationException::class);

test('hasUsername reflects onboarding state', function () {
    expect(User::factory()->make()->hasUsername())->toBeTrue()
        ->and(User::factory()->withoutUsername()->make()->hasUsername())->toBeFalse();
});
