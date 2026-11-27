<?php

namespace App\Domain\Library\Exceptions;

use RuntimeException;

final class GameAlreadyInLibrary extends RuntimeException
{
    public static function make(): self
    {
        return new self('This game is already in the library.');
    }
}
