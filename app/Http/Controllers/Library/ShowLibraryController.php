<?php

namespace App\Http\Controllers\Library;

use App\Domain\Library\Models\LibraryEntry;
use App\Domain\Library\Queries\LibraryStatsQuery;
use App\Domain\Users\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Library\ShowLibraryRequest;
use App\Http\Resources\Library\LibraryEntryResource;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The signed-in user's library, filterable by status, most recently
 * touched first.
 */
class ShowLibraryController extends Controller
{
    private const PER_PAGE = 48;

    public function __invoke(ShowLibraryRequest $request, LibraryStatsQuery $stats): Response
    {
        /** @var User $user */
        $user = $request->user();
        $status = $request->statusFilter();

        $entries = LibraryEntry::query()
            ->where('user_id', $user->id)
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->with(['game.platforms', 'platform'])
            ->latest('updated_at')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return Inertia::render('library/index', [
            'status' => $status?->value,
            'counts' => $stats->countsByStatus($user),
            'entries' => LibraryEntryResource::collection($entries->getCollection())->resolve($request),
            'pagination' => [
                'current_page' => $entries->currentPage(),
                'last_page' => $entries->lastPage(),
                'prev_url' => $entries->previousPageUrl(),
                'next_url' => $entries->nextPageUrl(),
            ],
        ]);
    }
}
