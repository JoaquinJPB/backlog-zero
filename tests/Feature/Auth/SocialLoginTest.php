<?php

use App\Domain\Users\Events\UserRegistered;
use App\Domain\Users\Models\SocialAccount;
use App\Domain\Users\Models\User;
use Illuminate\Support\Facades\Event;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as SocialiteUser;

function fakeGoogleUser(array $attributes = []): SocialiteUser
{
    return SocialiteUser::fake([
        'id' => 'google-42',
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'avatar' => 'https://lh3.googleusercontent.com/a/ada',
        ...$attributes,
    ]);
}

test('redirect sends the user to the provider', function () {
    Socialite::fake('google');

    $this->get(route('auth.redirect', 'google'))
        ->assertRedirect('https://socialite.fake/google/authorize');
});

test('providers that are not enabled return 404', function () {
    $this->get('/auth/github/redirect')->assertNotFound();
    $this->get('/auth/github/callback')->assertNotFound();
});

test('first sign-in registers the user and logs them in', function () {
    Event::fake([UserRegistered::class]);
    Socialite::fake('google', fakeGoogleUser(['email' => 'Ada@Example.com']));

    $this->get(route('auth.callback', 'google'))->assertRedirect(route('home'));

    $user = User::query()->sole();
    expect($user->name)->toBe('Ada Lovelace')
        ->and($user->email)->toBe('ada@example.com')
        ->and($user->username)->toBeNull()
        ->and($user->avatar_url)->toBe('https://lh3.googleusercontent.com/a/ada')
        ->and($user->socialAccounts()->sole()->provider_user_id)->toBe('google-42');

    $this->assertAuthenticatedAs($user);
    Event::assertDispatched(
        UserRegistered::class,
        fn (UserRegistered $event) => $event->user->is($user) && $event->provider === 'google',
    );
});

test('returning users are matched by provider id and their avatar refreshed', function () {
    Event::fake([UserRegistered::class]);
    $user = User::factory()->withGoogleAccount('google-42')->create(['email' => 'old@example.com']);
    Socialite::fake('google', fakeGoogleUser(['avatar' => 'https://lh3.googleusercontent.com/a/new']));

    $this->get(route('auth.callback', 'google'))->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($user);
    expect(User::query()->count())->toBe(1)
        ->and($user->fresh()?->avatar_url)->toBe('https://lh3.googleusercontent.com/a/new')
        ->and($user->fresh()?->email)->toBe('old@example.com');
    Event::assertNotDispatched(UserRegistered::class);
});

test('a new identity with an already registered email is not auto-linked', function () {
    User::factory()->create(['email' => 'ada@example.com']);
    Socialite::fake('google', fakeGoogleUser());

    $this->get(route('auth.callback', 'google'))
        ->assertRedirect(route('home'))
        ->assertInertiaFlash('error', __('ui.auth.email_taken'));

    $this->assertGuest();
    expect(SocialAccount::query()->count())->toBe(0);
});

test('an identity without email is rejected', function () {
    Socialite::fake('google', fakeGoogleUser(['email' => null]));

    $this->get(route('auth.callback', 'google'))
        ->assertRedirect(route('home'))
        ->assertInertiaFlash('error', __('ui.auth.missing_email'));

    $this->assertGuest();
    expect(User::query()->count())->toBe(0);
});

test('provider errors show a friendly message instead of crashing', function () {
    Socialite::fake('google', fn () => throw new InvalidStateException);

    $this->get(route('auth.callback', 'google'))
        ->assertRedirect(route('home'))
        ->assertInertiaFlash('error', __('ui.auth.failed'));

    $this->assertGuest();
});

test('authenticated users cannot start a new sign-in', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('auth.redirect', 'google'))
        ->assertRedirect('/');
});

test('users can log out', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('logout'))
        ->assertRedirect(route('home'));

    $this->assertGuest();
});

test('logout requires POST', function () {
    $this->actingAs(User::factory()->create())
        ->get('/logout')
        ->assertMethodNotAllowed();
});

describe('allowed emails', function () {
    test('listed emails can register, ignoring case and spaces', function () {
        config(['auth.allowed_emails' => ['bob@example.com', 'ada@example.com']]);
        Socialite::fake('google', fakeGoogleUser(['email' => ' Ada@Example.com ']));

        $this->get(route('auth.callback', 'google'))->assertRedirect(route('home'));

        $this->assertAuthenticatedAs(User::query()->sole());
    });

    test('emails not on the list cannot register', function () {
        Event::fake([UserRegistered::class]);
        config(['auth.allowed_emails' => ['bob@example.com']]);
        Socialite::fake('google', fakeGoogleUser());

        $this->get(route('auth.callback', 'google'))
            ->assertRedirect(route('home'))
            ->assertInertiaFlash('error', __('ui.auth.not_invited'));

        $this->assertGuest();
        expect(User::query()->count())->toBe(0)
            ->and(SocialAccount::query()->count())->toBe(0);
        Event::assertNotDispatched(UserRegistered::class);
    });

    test('existing users keep signing in even if they are not on the list', function () {
        $user = User::factory()->withGoogleAccount('google-42')->create();
        config(['auth.allowed_emails' => ['bob@example.com']]);
        Socialite::fake('google', fakeGoogleUser());

        $this->get(route('auth.callback', 'google'))->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    });
});
