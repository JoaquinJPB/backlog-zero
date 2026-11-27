<?php

namespace App\Domain\Library\Events;

use App\Domain\Library\Models\LibraryEntry;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class GameAddedToLibrary implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly LibraryEntry $entry) {}
}
