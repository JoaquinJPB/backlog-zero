<?php

namespace App\Domain\Games\Models;

use Database\Factories\GenreFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $igdb_id
 * @property string $name
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['igdb_id', 'name', 'slug'])]
#[UseFactory(GenreFactory::class)]
class Genre extends Model
{
    /** @use HasFactory<GenreFactory> */
    use HasFactory;
}
