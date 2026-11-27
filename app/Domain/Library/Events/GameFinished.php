<?php

namespace App\Domain\Library\Events;

use App\Domain\Library\Models\LibraryEntry;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Raised when an entry becomes "finished" (on add or on status change).
 */
final class GameFinished implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly LibraryEntry $entry) {}
}
