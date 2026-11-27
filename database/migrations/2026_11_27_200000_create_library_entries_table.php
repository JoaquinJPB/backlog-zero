<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A user's games. One entry per user and game; replays will be journal
 * sessions on the same entry.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->string('status', 16);
            // 1-10 = half stars (0.5-5 in the UI).
            $table->unsignedSmallInteger('rating')->nullable();
            $table->foreignId('platform_id')->nullable()->constrained()->nullOnDelete();
            $table->date('started_on')->nullable();
            $table->date('finished_on')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'game_id']);
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'finished_on']);
        });

        // Last line of defence behind validation and the domain DTO.
        DB::statement('ALTER TABLE library_entries ADD CONSTRAINT library_entries_rating_range CHECK (rating BETWEEN 1 AND 10)');
        DB::statement("ALTER TABLE library_entries ADD CONSTRAINT library_entries_status_valid CHECK (status IN ('playing', 'finished', 'abandoned', 'backlog'))");
        DB::statement('ALTER TABLE library_entries ADD CONSTRAINT library_entries_dates_ordered CHECK (started_on IS NULL OR finished_on IS NULL OR started_on <= finished_on)');
    }

    public function down(): void
    {
        Schema::dropIfExists('library_entries');
    }
};
