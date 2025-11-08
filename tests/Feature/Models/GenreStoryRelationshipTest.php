<?php

namespace Tests\Feature\Models;

use App\Models\Genre;
use App\Models\Story;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreStoryRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_genre_with_linked_stories_throws_query_exception(): void
    {
        $genre = Genre::factory()->create();
        $story = Story::factory()->create();

        // Link the genre to the story
        $story->genres()->attach($genre->id);

        // Expect a QueryException when trying to delete the genre
        $this->expectException(\Illuminate\Database\QueryException::class);

        $genre->delete();
    }
}
