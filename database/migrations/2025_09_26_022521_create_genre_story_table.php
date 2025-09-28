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
        Schema::create('genre_story', function (Blueprint $table) {
            $table->primary(['genre_id', 'story_id']);
            $table->foreignUlid('genre_id')->constrained()->onDelete('cascade');
            $table->foreignUlid('story_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('genre_story');
    }
};
