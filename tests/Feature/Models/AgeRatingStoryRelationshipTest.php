<?php

namespace Tests\Feature\Models;

use App\Models\AgeRating;
use App\Models\Story;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgeRatingStoryRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_age_rating_with_linked_stories_throws_query_exception(): void
    {
        $ageRating = AgeRating::factory()->create();
        $story = Story::factory()->create();

        // Link the age rating to the story
        $story->ageRatings()->attach($ageRating->id);

        // Expect a QueryException when trying to delete the age rating
        $this->expectException(\Illuminate\Database\QueryException::class);

        $ageRating->delete();
    }
}
