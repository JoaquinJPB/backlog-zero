<?php

namespace App\Domain\Library\Enums;

enum GameStatus: string
{
    case Playing = 'playing';
    case Finished = 'finished';
    case Abandoned = 'abandoned';
    case Backlog = 'backlog';

    /**
     * Only finished or abandoned games have an end date (for abandoned ones,
     * the day you dropped it).
     */
    public function allowsFinishDate(): bool
    {
        return $this === self::Finished || $this === self::Abandoned;
    }

    /**
     * Only games you actually played can be at 100% (all achievements).
     */
    public function allowsCompletion(): bool
    {
        return $this !== self::Backlog;
    }

    /**
     * Statuses of games you actually played (everything but the backlog).
     *
     * @return list<self>
     */
    public static function played(): array
    {
        return [self::Playing, self::Finished, self::Abandoned];
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $status): string => $status->value, self::cases());
    }
}
