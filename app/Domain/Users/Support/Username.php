<?php

namespace App\Domain\Users\Support;

use Illuminate\Support\Str;

/**
 * Rules for usernames. They appear in public URLs (/u/{username}), so they
 * are lowercase ASCII and some words are reserved for the app's own routes.
 */
final class Username
{
    public const MIN_LENGTH = 3;

    public const MAX_LENGTH = 20;

    public const PATTERN = '/^[a-z0-9_]+$/';

    /** @var list<string> */
    public const RESERVED = [
        'about', 'admin', 'administrator', 'api', 'app', 'assets', 'auth',
        'backlog', 'backlogzero', 'game', 'games', 'help', 'igdb', 'journal',
        'library', 'list', 'lists', 'login', 'logout', 'me', 'mod',
        'moderator', 'null', 'onboarding', 'profile', 'register', 'root',
        'search', 'settings', 'staff', 'static', 'storage', 'support',
        'system', 'u', 'undefined', 'user', 'users',
    ];

    public static function normalize(string $username): string
    {
        return Str::lower(trim($username));
    }

    public static function isReserved(string $username): bool
    {
        return in_array(self::normalize($username), self::RESERVED, true);
    }

    /**
     * Turns free text (a display name, an email local part) into a candidate
     * that satisfies the format rules, or null if nothing usable remains.
     */
    public static function slugFrom(string $text): ?string
    {
        $slug = Str::of($text)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->limit(self::MAX_LENGTH, '')
            ->trim('_')
            ->toString();

        return strlen($slug) >= self::MIN_LENGTH ? $slug : null;
    }
}
