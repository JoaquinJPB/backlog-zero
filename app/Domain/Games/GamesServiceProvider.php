<?php

namespace App\Domain\Games;

use App\Domain\Games\Contracts\GameCatalog;
use App\Domain\Games\Igdb\IgdbClient;
use App\Domain\Games\Igdb\IgdbGameCatalog;
use Illuminate\Cache\RateLimiter as CacheRateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class GamesServiceProvider extends ServiceProvider
{
    private const SEARCHES_PER_MINUTE = 30;

    public function register(): void
    {
        $this->app->singleton(IgdbClient::class, function (Application $app): IgdbClient {
            $config = $app->make('config');

            return new IgdbClient(
                clientId: $this->stringOrNull($config->get('services.igdb.client_id')),
                clientSecret: $this->stringOrNull($config->get('services.igdb.client_secret')),
                baseUrl: (string) $config->get('services.igdb.base_url'),
                tokenUrl: (string) $config->get('services.igdb.token_url'),
                requestsPerSecond: (int) $config->get('services.igdb.requests_per_second'),
                cache: $app->make(Cache::class),
                limiter: $app->make(CacheRateLimiter::class),
            );
        });

        $this->app->bind(GameCatalog::class, IgdbGameCatalog::class);
    }

    public function boot(): void
    {
        // Each search can spend IGDB quota, so cap it per user.
        RateLimiter::for('game-search', fn (Request $request): Limit => Limit::perMinute(self::SEARCHES_PER_MINUTE)
            ->by((string) ($request->user()?->getAuthIdentifier() ?? $request->ip())));
    }

    private function stringOrNull(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
