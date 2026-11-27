<?php

namespace App\Domain\Library\Models;

use App\Domain\Games\Models\Game;
use App\Domain\Games\Models\Platform;
use App\Domain\Library\Enums\GameStatus;
use App\Domain\Library\Policies\LibraryEntryPolicy;
use App\Domain\Users\Models\User;
use Database\Factories\LibraryEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A game in a user's library. Rating is 1-10 (half stars).
 *
 * @property int $id
 * @property int $user_id
 * @property int $game_id
 * @property GameStatus $status
 * @property int|null $rating
 * @property int|null $platform_id
 * @property Carbon|null $started_on
 * @property Carbon|null $finished_on
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Game $game
 * @property-read Platform|null $platform
 */
#[Fillable(['user_id', 'game_id', 'status', 'rating', 'platform_id', 'started_on', 'finished_on'])]
#[UseFactory(LibraryEntryFactory::class)]
#[UsePolicy(LibraryEntryPolicy::class)]
class LibraryEntry extends Model
{
    /** @use HasFactory<LibraryEntryFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => GameStatus::class,
            'rating' => 'integer',
            'started_on' => 'date',
            'finished_on' => 'date',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Game, $this>
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * @return BelongsTo<Platform, $this>
     */
    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }
}
