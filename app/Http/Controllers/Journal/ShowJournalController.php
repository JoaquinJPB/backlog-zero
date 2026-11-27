<?php

namespace App\Http\Controllers\Journal;

use App\Domain\Games\Models\Platform;
use App\Domain\Journal\Actions\BuildYearJournalAction;
use App\Domain\Library\Queries\FinishedGamesQuery;
use App\Domain\Library\Queries\PlayingGamesQuery;
use App\Domain\Users\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Journal\ShowJournalRequest;
use App\Http\Resources\Library\LibraryEntryResource;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Games finished in a year, grouped by month, with filters.
 */
class ShowJournalController extends Controller
{
    public function __invoke(
        ShowJournalRequest $request,
        FinishedGamesQuery $finishedGames,
        PlayingGamesQuery $playingGames,
        BuildYearJournalAction $buildJournal,
    ): Response {
        /** @var User $user */
        $user = $request->user();
        $includeAbandoned = $request->includeAbandoned();
        $years = $finishedGames->years($user, $includeAbandoned);

        // Default to the most recent year with activity, or this year.
        $year = $request->year() ?? (array_key_first($years) ?? now()->year);
        $platformId = $request->platformId();

        $journal = $buildJournal->execute($user, $year, $platformId, $includeAbandoned);

        $months = [];

        foreach ($journal->months as $month => $entries) {
            $months[] = [
                'month' => $month,
                'entries' => LibraryEntryResource::collection(collect($entries))->resolve($request),
            ];
        }

        return Inertia::render('journal/index', [
            'year' => $year,
            'years' => array_map(
                fn (int $value, int $count): array => ['year' => $value, 'count' => $count],
                array_keys($years),
                array_values($years),
            ),
            'filters' => [
                'platform' => $platformId,
                'abandoned' => $includeAbandoned,
            ],
            'platforms' => array_values($finishedGames->platformsForYear($user, $year, $includeAbandoned)
                ->map(fn (Platform $platform): array => ['id' => $platform->id, 'name' => $platform->name])
                ->all()),
            'summary' => [
                'finished' => $journal->finishedCount,
                'abandoned' => $journal->abandonedCount,
                'average_rating' => $journal->averageRating,
            ],
            'months' => $months,
            // What the user plays now, independent of the year shown.
            'playing' => LibraryEntryResource::collection($playingGames->forUser($user))->resolve($request),
        ]);
    }
}
