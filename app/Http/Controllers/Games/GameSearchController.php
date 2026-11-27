<?php

namespace App\Http\Controllers\Games;

use App\Domain\Games\Actions\SearchGamesAction;
use App\Domain\Games\Exceptions\CatalogUnavailable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Games\SearchGamesRequest;
use App\Http\Resources\Games\GameSummaryResource;
use Inertia\Inertia;
use Inertia\Response;

class GameSearchController extends Controller
{
    public function __invoke(SearchGamesRequest $request, SearchGamesAction $search): Response
    {
        $query = $request->searchQuery();
        $results = [];
        $failed = false;

        try {
            $results = GameSummaryResource::collection($search->execute($query))->resolve($request);
        } catch (CatalogUnavailable $e) {
            // Logged for us; the user just sees "search unavailable, try later".
            report($e);
            $failed = true;
        }

        return Inertia::render('games/search', [
            'query' => $query,
            'results' => $results,
            'failed' => $failed,
            'minLength' => SearchGamesAction::MIN_QUERY_LENGTH,
        ]);
    }
}
