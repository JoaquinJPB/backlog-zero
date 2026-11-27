<?php

namespace App\Domain\Library\Actions;

use App\Domain\Games\Models\Game;
use App\Domain\Library\Data\LibraryEntryData;
use App\Domain\Library\Enums\GameStatus;
use App\Domain\Library\Events\GameAddedToLibrary;
use App\Domain\Library\Events\GameFinished;
use App\Domain\Library\Events\GameRated;
use App\Domain\Library\Exceptions\GameAlreadyInLibrary;
use App\Domain\Library\Models\LibraryEntry;
use App\Domain\Library\Support\GamePlatforms;
use App\Domain\Users\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

final class AddGameToLibraryAction
{
    /**
     * @throws GameAlreadyInLibrary
     */
    public function execute(User $user, Game $game, LibraryEntryData $data): LibraryEntry
    {
        GamePlatforms::assertAllowed($game, $data->platformId);

        $alreadyAdded = LibraryEntry::query()
            ->where('user_id', $user->id)
            ->where('game_id', $game->id)
            ->exists();

        if ($alreadyAdded) {
            throw GameAlreadyInLibrary::make();
        }

        try {
            return DB::transaction(function () use ($user, $game, $data): LibraryEntry {
                $entry = LibraryEntry::query()->create([
                    'user_id' => $user->id,
                    'game_id' => $game->id,
                    ...$data->toAttributes(),
                ]);

                GameAddedToLibrary::dispatch($entry);

                if ($data->status === GameStatus::Finished) {
                    GameFinished::dispatch($entry);
                }

                if ($data->rating !== null) {
                    GameRated::dispatch($entry, null);
                }

                return $entry;
            });
        } catch (UniqueConstraintViolationException) {
            // Two concurrent adds: the unique index caught the second one.
            throw GameAlreadyInLibrary::make();
        }
    }
}
