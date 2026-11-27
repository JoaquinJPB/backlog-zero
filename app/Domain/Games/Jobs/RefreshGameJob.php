<?php

namespace App\Domain\Games\Jobs;

use App\Domain\Games\Actions\UpsertGameAction;
use App\Domain\Games\Contracts\GameCatalog;
use App\Domain\Games\Exceptions\CatalogUnavailable;
use App\Domain\Games\Models\Game;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Log;

/**
 * Pulls a game again from the catalog and updates the local copy.
 * Unique per game, so a busy page can't queue the same refresh twice.
 */
class RefreshGameJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public const RATE_LIMITER = 'igdb-jobs';

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [60, 300];

    public int $uniqueFor = 3600;

    public function __construct(public readonly Game $game) {}

    public function uniqueId(): string
    {
        return (string) $this->game->id;
    }

    /**
     * Background refreshes get their own, lower budget so they never starve
     * interactive searches of the shared 4 requests/second.
     *
     * @return list<object>
     */
    public function middleware(): array
    {
        return [new RateLimited(self::RATE_LIMITER)];
    }

    /**
     * @throws CatalogUnavailable so the queue retries with backoff
     */
    public function handle(GameCatalog $catalog, UpsertGameAction $upsert): void
    {
        $data = $catalog->find($this->game->igdb_id);

        if ($data === null) {
            // Gone from IGDB: keep our copy (users may have it in their
            // library) and don't retry it every day.
            Log::info('Game no longer found in catalog; keeping local copy.', ['igdb_id' => $this->game->igdb_id]);
            $this->game->forceFill(['synced_at' => now()])->save();

            return;
        }

        $upsert->execute($data);
    }
}
