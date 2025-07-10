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
        Schema::create('story_comment_votes', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('creator_id')->constrained('users');
            $table->foreignUlid('story_comment_id')->constrained();
            $table->string('type');
            $table->timestamps();

            $table->unique(['creator_id', 'story_comment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('story_comment_votes');
    }
};
