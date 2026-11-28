<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * "100%": every achievement of the game. Defaults to false so the running
 * version keeps working during a deploy.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('library_entries', function (Blueprint $table) {
            $table->boolean('completed')->default(false)->after('rating');
        });

        // A game still in the backlog can't be at 100%.
        DB::statement("ALTER TABLE library_entries ADD CONSTRAINT library_entries_completed_played CHECK (NOT completed OR status <> 'backlog')");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE library_entries DROP CONSTRAINT IF EXISTS library_entries_completed_played');

        Schema::table('library_entries', function (Blueprint $table) {
            $table->dropColumn('completed');
        });
    }
};
