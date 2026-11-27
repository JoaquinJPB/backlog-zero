<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Local copy of the IGDB catalog. Pages read from these tables; IGDB is only
 * hit on search and when refreshing stale rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platforms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('igdb_id')->unique();
            $table->string('name');
            $table->string('abbreviation', 32)->nullable();
            $table->string('slug');
            $table->timestamps();
        });

        Schema::create('genres', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('igdb_id')->unique();
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
        });

        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('igdb_id')->unique();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('summary')->nullable();
            $table->string('cover_image_id', 64)->nullable();
            $table->date('first_release_date')->nullable();
            $table->unsignedInteger('game_type_id')->nullable();
            $table->timestamp('igdb_updated_at')->nullable();
            // When we last pulled this row from IGDB; drives the stale refresh.
            $table->timestamp('synced_at')->index();
            $table->timestamps();
        });

        Schema::create('game_platform', function (Blueprint $table) {
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('platform_id')->constrained()->cascadeOnDelete();
            $table->primary(['game_id', 'platform_id']);
        });

        Schema::create('game_genre', function (Blueprint $table) {
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('genre_id')->constrained()->cascadeOnDelete();
            $table->primary(['game_id', 'genre_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_genre');
        Schema::dropIfExists('game_platform');
        Schema::dropIfExists('games');
        Schema::dropIfExists('genres');
        Schema::dropIfExists('platforms');
    }
};
