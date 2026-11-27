<?php

namespace App\Http\Controllers\Profile;

use App\Domain\Library\Queries\LibraryStatsQuery;
use App\Domain\Stats\Actions\BuildProfileStatsAction;
use App\Domain\Stats\Data\ProfileStats;
use App\Domain\Users\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\Library\LibraryEntryResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * /u/{username}: the user's stats and the games they played.
 */
class ShowProfileController extends Controller
{
    private const PER_PAGE = 48;

    public function __invoke(
        Request $request,
        User $user,
        BuildProfileStatsAction $buildStats,
        LibraryStatsQuery $library,
    ): Response {
        Gate::authorize('viewProfile', $user);

        $stats = $buildStats->execute($user);
        $games = $library->played($user, self::PER_PAGE)->withQueryString();

        return Inertia::render('profile/show', [
            'profile' => [
                'name' => $user->name,
                'username' => $user->username,
                'avatar_url' => $user->avatar_url,
            ],
            'stats' => $this->statsProps($stats),
            'games' => LibraryEntryResource::collection($games->getCollection())->resolve($request),
            'pagination' => [
                'current_page' => $games->currentPage(),
                'last_page' => $games->lastPage(),
                'prev_url' => $games->previousPageUrl(),
                'next_url' => $games->nextPageUrl(),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function statsProps(ProfileStats $stats): array
    {
        return [
            'counts' => [
                'library' => $stats->libraryCount,
                'finished' => $stats->finishedCount,
                'finished_this_year' => $stats->finishedThisYear,
                'playing' => $stats->playingCount,
                'backlog' => $stats->backlogCount,
                'abandoned' => $stats->abandonedCount,
            ],
            'current_year' => now()->year,
            'average_rating' => $stats->averageRating,
            'rating_histogram' => array_values($stats->ratingHistogram),
            'finished_per_year' => array_map(
                fn (int $year, int $count): array => ['year' => $year, 'count' => $count],
                array_keys($stats->finishedPerYear),
                array_values($stats->finishedPerYear),
            ),
            'top_platforms' => $stats->topPlatforms,
            'top_genres' => $stats->topGenres,
        ];
    }
}
