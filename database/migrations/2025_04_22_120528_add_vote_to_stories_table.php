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
        Schema::table('stories', function (Blueprint $table) {
            $table->unsignedBigInteger('upvote_count')->default(0)->after('view_count')->index();
            $table->unsignedBigInteger('downvote_count')->default(0)->after('upvote_count')->index();
            $table->unsignedBigInteger('vote_count')->default(0)->after('downvote_count')->index();
            $table->unsignedBigInteger('vote_score')->default(0)->after('vote_count')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->dropIndex(['upvote_count']);
            $table->dropColumn('upvote_count');

            $table->dropIndex(['downvote_count']);
            $table->dropColumn('downvote_count');

            $table->dropIndex(['vote_count']);
            $table->dropColumn('vote_count');

            $table->dropIndex(['vote_score']);
            $table->dropColumn('vote_score');
        });
    }
};
