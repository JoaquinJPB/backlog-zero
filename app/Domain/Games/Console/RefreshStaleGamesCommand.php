<?php

namespace App\Domain\Games\Console;

use App\Domain\Games\Jobs\RefreshGameJob;
use App\Domain\Games\Models\Game;
use Illuminate\Console\Command;

/**
 * Queues a refresh for games not synced in the last week, oldest first.
 * The per-run limit keeps one execution from flooding the queue.
 */
class RefreshStaleGamesCommand extends Command
{
    private const DEFAULT_LIMIT = 1000;

    protected $signature = 'games:refresh-stale {--limit='.self::DEFAULT_LIMIT.' : Max games to queue in this run}';

    protected $description = 'Queue a catalog refresh for games synced more than a week ago';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));

        $games = Game::query()->stale()->oldest('synced_at')->limit($limit)->get();

        foreach ($games as $game) {
            RefreshGameJob::dispatch($game);
        }

        $this->info("Queued {$games->count()} stale game(s) for refresh.");

        return self::SUCCESS;
    }
}
