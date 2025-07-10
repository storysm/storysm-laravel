<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('story_comments', function (Blueprint $table) {
            $table->unsignedInteger('upvote_count')->default(0)->after('reply_count');
            $table->unsignedInteger('downvote_count')->default(0)->after('upvote_count');
            $table->integer('vote_count')->default(0)->after('downvote_count');
            $table->integer('vote_score')->default(0)->after('vote_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('story_comments', function (Blueprint $table) {
            $table->dropColumn(['upvote_count', 'downvote_count', 'vote_count', 'vote_score']);
        });
    }
};
