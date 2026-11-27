<?php

namespace App\Domain\Games\Models;

use Database\Factories\PlatformFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $igdb_id
 * @property string $name
 * @property string|null $abbreviation
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['igdb_id', 'name', 'abbreviation', 'slug'])]
#[UseFactory(PlatformFactory::class)]
class Platform extends Model
{
    /** @use HasFactory<PlatformFactory> */
    use HasFactory;
}
