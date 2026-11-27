<?php

namespace App\Domain\Games\Exceptions;

use RuntimeException;
use Throwable;

/**
 * The game catalog couldn't answer: missing credentials, network error,
 * provider outage or an unexpected payload. Messages never include secrets.
 */
final class CatalogUnavailable extends RuntimeException
{
    public static function notConfigured(): self
    {
        return new self('Game catalog credentials are not configured (IGDB_CLIENT_ID / IGDB_CLIENT_SECRET).');
    }

    public static function requestFailed(Throwable $previous): self
    {
        return new self('Game catalog request failed.', previous: $previous);
    }

    public static function unexpectedResponse(string $detail): self
    {
        return new self("Game catalog returned an unexpected response: {$detail}.");
    }

    public static function throttled(): self
    {
        return new self('Game catalog rate limit is saturated; gave up waiting for a free slot.');
    }
}
