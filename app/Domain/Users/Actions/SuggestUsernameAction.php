<?php

namespace App\Domain\Users\Actions;

use App\Domain\Users\Models\User;
use App\Domain\Users\Support\Username;
use Illuminate\Support\Str;

/**
 * Proposes a free, valid username from the user's name or email so the
 * onboarding form starts pre-filled. It's only a suggestion: availability
 * is checked again when the user submits.
 */
final class SuggestUsernameAction
{
    private const FALLBACK_BASE = 'player';

    private const MAX_NUMBERED_ATTEMPTS = 50;

    public function execute(User $user): string
    {
        $base = Username::slugFrom($user->name)
            ?? Username::slugFrom(Str::before($user->email, '@'))
            ?? self::FALLBACK_BASE;

        if ($this->isAvailable($base)) {
            return $base;
        }

        for ($suffix = 2; $suffix <= self::MAX_NUMBERED_ATTEMPTS; $suffix++) {
            $candidate = $this->withSuffix($base, (string) $suffix);

            if ($this->isAvailable($candidate)) {
                return $candidate;
            }
        }

        return $this->withSuffix($base, (string) random_int(1000, 999999));
    }

    private function withSuffix(string $base, string $suffix): string
    {
        $room = Username::MAX_LENGTH - strlen($suffix);

        return rtrim(substr($base, 0, $room), '_').$suffix;
    }

    private function isAvailable(string $candidate): bool
    {
        return ! Username::isReserved($candidate)
            && ! User::query()->where('username', $candidate)->exists();
    }
}
