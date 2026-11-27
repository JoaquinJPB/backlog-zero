<?php

namespace App\Domain\Games\Igdb;

use App\Domain\Games\Exceptions\CatalogUnavailable;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Throwable;

/**
 * Low-level IGDB v4 client: app access token (Twitch client credentials),
 * the 4 requests/second limit and retries on 429. Server-side only.
 */
final class IgdbClient
{
    private const TOKEN_CACHE_KEY = 'igdb.access_token';

    /** Refresh the token this long before Twitch says it expires. */
    private const TOKEN_EXPIRY_MARGIN_SECONDS = 300;

    private const LIMITER_KEY = 'igdb-api';

    private const MAX_THROTTLE_WAITS = 30;

    private const RETRIES_ON_429 = 3;

    private const RETRY_DELAY_MS = 500;

    private const TIMEOUT_SECONDS = 10;

    public function __construct(
        private readonly ?string $clientId,
        private readonly ?string $clientSecret,
        private readonly string $baseUrl,
        private readonly string $tokenUrl,
        private readonly int $requestsPerSecond,
        private readonly Cache $cache,
        private readonly RateLimiter $limiter,
    ) {}

    /**
     * Runs an Apicalypse query (e.g. `fields name; where id = 1;`) against
     * an endpoint such as "games".
     *
     * @return list<array<mixed>>
     *
     * @throws CatalogUnavailable
     */
    public function query(string $endpoint, string $body): array
    {
        if (blank($this->clientId) || blank($this->clientSecret)) {
            throw CatalogUnavailable::notConfigured();
        }

        try {
            $response = $this->send($endpoint, $body);

            if ($response->status() === 401) {
                // Token revoked or expired early: get a new one and retry once.
                $this->cache->forget(self::TOKEN_CACHE_KEY);
                $response = $this->send($endpoint, $body);
            }

            $response->throw();
        } catch (ConnectionException|RequestException $e) {
            throw CatalogUnavailable::requestFailed($e);
        }

        return $this->rows($response);
    }

    private function send(string $endpoint, string $body): Response
    {
        $token = $this->token();
        $this->waitForSlot();

        return Http::baseUrl($this->baseUrl)
            ->timeout(self::TIMEOUT_SECONDS)
            ->acceptJson()
            ->withHeaders(['Client-ID' => (string) $this->clientId])
            ->withToken($token)
            ->withBody($body, 'text/plain')
            ->retry(
                self::RETRIES_ON_429,
                fn (int $attempt): int => $attempt * self::RETRY_DELAY_MS,
                fn (Throwable $e): bool => $e instanceof RequestException && $e->response->status() === 429,
                throw: false,
            )
            ->post('/'.ltrim($endpoint, '/'));
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     * @throws CatalogUnavailable
     */
    private function token(): string
    {
        $cached = $this->cache->get(self::TOKEN_CACHE_KEY);

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        // Sent as a form body, not in the query string as IGDB's docs show:
        // connection errors include the URL, which would leak the secret to logs.
        $response = Http::asForm()
            ->timeout(self::TIMEOUT_SECONDS)
            ->post($this->tokenUrl, [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'client_credentials',
            ])
            ->throw();

        $token = $response->json('access_token');
        $expiresIn = $response->json('expires_in');

        if (! is_string($token) || $token === '' || ! is_int($expiresIn)) {
            throw CatalogUnavailable::unexpectedResponse('token payload');
        }

        $ttl = max($expiresIn - self::TOKEN_EXPIRY_MARGIN_SECONDS, 60);
        $this->cache->put(self::TOKEN_CACHE_KEY, $token, $ttl);

        return $token;
    }

    /**
     * Blocks until the shared limiter allows another request this second.
     */
    private function waitForSlot(): void
    {
        $waits = 0;

        while (! $this->limiter->attempt(self::LIMITER_KEY, $this->requestsPerSecond, static fn (): bool => true, 1)) {
            if (++$waits > self::MAX_THROTTLE_WAITS) {
                throw CatalogUnavailable::throttled();
            }

            Sleep::for(max($this->limiter->availableIn(self::LIMITER_KEY), 1))->seconds();
        }
    }

    /**
     * @return list<array<mixed>>
     */
    private function rows(Response $response): array
    {
        $data = $response->json();

        if (! is_array($data) || ! array_is_list($data)) {
            throw CatalogUnavailable::unexpectedResponse('expected a JSON array');
        }

        return array_values(array_filter($data, is_array(...)));
    }
}
