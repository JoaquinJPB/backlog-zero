<?php

namespace App\Domain\Users\Models;

use App\Domain\Users\Policies\UserPolicy;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * Users never have a password: they sign in through a social provider
 * (see SocialAccount). The username is null until onboarding is completed.
 *
 * @property int $id
 * @property string $name
 * @property string|null $username
 * @property string $email
 * @property string|null $avatar_url
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'username', 'email', 'avatar_url'])]
#[Hidden(['remember_token'])]
#[UseFactory(UserFactory::class)]
#[UsePolicy(UserPolicy::class)]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @return HasMany<SocialAccount, $this>
     */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function hasUsername(): bool
    {
        return $this->username !== null;
    }

    /**
     * Users have no password; this keeps Laravel's auth contract satisfied
     * without exposing a column that doesn't exist.
     */
    public function getAuthPassword(): string
    {
        return '';
    }
}
