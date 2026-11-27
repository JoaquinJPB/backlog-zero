<?php

namespace App\Domain\Games;

use App\Domain\Games\Contracts\GameCatalog;
use App\Domain\Games\Igdb\IgdbClient;
use App\Domain\Games\Igdb\IgdbGameCatalog;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class GamesServiceProvider extends ServiceProvider
{
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
                limiter: $app->make(RateLimiter::class),
            );
        });

        $this->app->bind(GameCatalog::class, IgdbGameCatalog::class);
    }

    private function stringOrNull(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
