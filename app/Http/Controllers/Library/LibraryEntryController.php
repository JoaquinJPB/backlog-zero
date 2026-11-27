<?php

namespace App\Http\Controllers\Library;

use App\Domain\Games\Models\Game;
use App\Domain\Library\Actions\AddGameToLibraryAction;
use App\Domain\Library\Actions\RemoveFromLibraryAction;
use App\Domain\Library\Actions\UpdateLibraryEntryAction;
use App\Domain\Library\Exceptions\GameAlreadyInLibrary;
use App\Domain\Library\Models\LibraryEntry;
use App\Domain\Users\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Library\SaveLibraryEntryRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class LibraryEntryController extends Controller
{
    public function store(SaveLibraryEntryRequest $request, Game $game, AddGameToLibraryAction $add): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $add->execute($user, $game, $request->toData());
            Inertia::flash('success', __('ui.library.added'));
        } catch (GameAlreadyInLibrary) {
            Inertia::flash('error', __('ui.library.already_added'));
        }

        return redirect()->route('games.show', $game);
    }

    public function update(SaveLibraryEntryRequest $request, LibraryEntry $entry, UpdateLibraryEntryAction $update): RedirectResponse
    {
        $update->execute($entry, $request->toData());

        Inertia::flash('success', __('ui.library.saved'));

        return redirect()->back();
    }

    public function destroy(LibraryEntry $entry, RemoveFromLibraryAction $remove): RedirectResponse
    {
        Gate::authorize('delete', $entry);

        $remove->execute($entry);

        Inertia::flash('success', __('ui.library.removed'));

        return redirect()->back();
    }
}
