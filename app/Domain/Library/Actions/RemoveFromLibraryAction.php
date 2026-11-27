<?php

namespace App\Domain\Library\Actions;

use App\Domain\Library\Events\GameRemovedFromLibrary;
use App\Domain\Library\Models\LibraryEntry;
use Illuminate\Support\Facades\DB;

final class RemoveFromLibraryAction
{
    public function execute(LibraryEntry $entry): void
    {
        DB::transaction(function () use ($entry): void {
            $entry->delete();

            GameRemovedFromLibrary::dispatch($entry->user_id, $entry->game_id);
        });
    }
}
