<?php

namespace App\Domain\Games\Models;

use Database\Factories\GameFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * A game imported from the catalog (IGDB). Pages always read this table.
 *
 * @property int $id
 * @property int $igdb_id
 * @property string $slug
 * @property string $name
 * @property string|null $summary
 * @property string|null $cover_image_id
 * @property Carbon|null $first_release_date
 * @property int|null $game_type_id
 * @property Carbon|null $igdb_updated_at
 * @property Carbon $synced_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Platform> $platforms
 * @property-read Collection<int, Genre> $genres
 */
#[Fillable([
    'igdb_id', 'slug', 'name', 'summary', 'cover_image_id',
    'first_release_date', 'game_type_id', 'igdb_updated_at', 'synced_at',
])]
#[UseFactory(GameFactory::class)]
class Game extends Model
{
    /** @use HasFactory<GameFactory> */
    use HasFactory;

    public const STALE_AFTER_DAYS = 7;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'first_release_date' => 'date',
            'igdb_updated_at' => 'datetime',
            'synced_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsToMany<Platform, $this>
     */
    public function platforms(): BelongsToMany
    {
        return $this->belongsToMany(Platform::class)->orderBy('name');
    }

    /**
     * @return BelongsToMany<Genre, $this>
     */
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class)->orderBy('name');
    }

    public function isStale(): bool
    {
        return $this->synced_at->lt(now()->subDays(self::STALE_AFTER_DAYS));
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeStale(Builder $query): Builder
    {
        return $query->where('synced_at', '<', now()->subDays(self::STALE_AFTER_DAYS));
    }
}
