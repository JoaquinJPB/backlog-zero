<?php

namespace App\Http\Controllers\Games;

use App\Domain\Games\Models\Game;
use App\Http\Controllers\Controller;
use App\Http\Resources\Games\GameResource;
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
        $game->loadMissing(['platforms', 'genres']);

        return Inertia::render('games/show', [
            'game' => (new GameResource($game))->resolve($request),
        ]);
    }
}
