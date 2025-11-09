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
        Schema::table('language_story', function (Blueprint $table) {
            $table->dropForeign(['language_id']);
            $table->foreign('language_id')
                ->references('id')
                ->on('languages')
                ->restrictOnDelete();
        });

        Schema::table('genre_story', function (Blueprint $table) {
            $table->dropForeign(['genre_id']);
            $table->foreign('genre_id')
                ->references('id')
                ->on('genres')
                ->restrictOnDelete();
        });

        Schema::table('category_story', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->restrictOnDelete();
        });

        Schema::table('license_story', function (Blueprint $table) {
            $table->dropForeign(['license_id']);
            $table->foreign('license_id')
                ->references('id')
                ->on('licenses')
                ->restrictOnDelete();
        });

        Schema::table('age_rating_story', function (Blueprint $table) {
            $table->dropForeign(['age_rating_id']);
            $table->foreign('age_rating_id')
                ->references('id')
                ->on('age_ratings')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('language_story', function (Blueprint $table) {
            $table->dropForeign(['language_id']);
            $table->foreign('language_id')
                ->references('id')
                ->on('languages')
                ->cascadeOnDelete();
        });

        Schema::table('genre_story', function (Blueprint $table) {
            $table->dropForeign(['genre_id']);
            $table->foreign('genre_id')
                ->references('id')
                ->on('genres')
                ->cascadeOnDelete();
        });

        Schema::table('category_story', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->cascadeOnDelete();
        });

        Schema::table('license_story', function (Blueprint $table) {
            $table->dropForeign(['license_id']);
            $table->foreign('license_id')
                ->references('id')
                ->on('licenses')
                ->cascadeOnDelete();
        });

        Schema::table('age_rating_story', function (Blueprint $table) {
            $table->dropForeign(['age_rating_id']);
            $table->foreign('age_rating_id')
                ->references('id')
                ->on('age_ratings')
                ->cascadeOnDelete();
        });
    }
};
