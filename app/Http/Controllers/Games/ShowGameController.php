<?php

namespace App\Http\Controllers\Games;

use App\Domain\Games\Jobs\RefreshGameJob;
use App\Domain\Games\Models\Game;
use App\Domain\Games\Models\Platform;
use App\Domain\Library\Models\LibraryEntry;
use App\Domain\Users\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\Games\GameResource;
use App\Http\Resources\Library\LibraryEntryResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Public game page. Served from our database only, never from IGDB.
 */
class ShowGameController extends Controller
{
    public function __invoke(Request $request, Game $game): Response
    {
        if ($game->isStale()) {
            // Serve what we have now; the queue updates it in the background.
            RefreshGameJob::dispatch($game);
        }

        $game->loadMissing(['platforms', 'genres']);

        return Inertia::render('games/show', [
            'game' => (new GameResource($game))->resolve($request),
            'library' => $this->libraryProps($request, $game),
        ]);
    }

    /**
     * The signed-in user's entry for this game and the platforms they can
     * pick; null for guests.
     *
     * @return array{entry: array<string, mixed>|null, platforms: list<array{id: int, name: string}>}|null
     */
    private function libraryProps(Request $request, Game $game): ?array
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return null;
        }

        $entry = LibraryEntry::query()
            ->where('user_id', $user->id)
            ->where('game_id', $game->id)
            ->first();

        return [
            'entry' => $entry === null ? null : (new LibraryEntryResource($entry))->resolve($request),
            'platforms' => array_values($game->platforms
                ->map(fn (Platform $platform): array => ['id' => $platform->id, 'name' => $platform->name])
                ->all()),
        ];
    }
}
