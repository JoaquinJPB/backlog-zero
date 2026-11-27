<?php

namespace App\Domain\Library\Actions;

use App\Domain\Library\Data\LibraryEntryData;
use App\Domain\Library\Enums\GameStatus;
use App\Domain\Library\Events\GameFinished;
use App\Domain\Library\Events\GameRated;
use App\Domain\Library\Models\LibraryEntry;
use App\Domain\Library\Support\GamePlatforms;
use Illuminate\Support\Facades\DB;

final class UpdateLibraryEntryAction
{
    public function execute(LibraryEntry $entry, LibraryEntryData $data): LibraryEntry
    {
        GamePlatforms::assertAllowed($entry->game, $data->platformId);

        $previousStatus = $entry->status;
        $previousRating = $entry->rating;

        return DB::transaction(function () use ($entry, $data, $previousStatus, $previousRating): LibraryEntry {
            $entry->update($data->toAttributes());

            if ($data->status === GameStatus::Finished && $previousStatus !== GameStatus::Finished) {
                GameFinished::dispatch($entry);
            }

            if ($data->rating !== null && $data->rating !== $previousRating) {
                GameRated::dispatch($entry, $previousRating);
            }

            return $entry;
        });
    }
}
