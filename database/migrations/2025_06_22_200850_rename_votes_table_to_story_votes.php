<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('votes', 'story_votes');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('story_votes', 'votes');
    }
};
