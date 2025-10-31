<?php

namespace Tests\Unit\Observers;

use App\Models\AgeRating;
use App\Models\Story;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoryObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_effective_age_rating_is_null_when_no_age_ratings_are_associated(): void
    {
        $story = Story::factory()->create();

        $this->assertNull($story->age_rating_effective_value);
    }

    public function test_effective_age_rating_is_set_to_the_highest_age_representation(): void
    {
        $story = Story::factory()->create();
        $ageRating1 = AgeRating::factory()->create(['age_representation' => 10]);
        $ageRating2 = AgeRating::factory()->create(['age_representation' => 18]);
        $ageRating3 = AgeRating::factory()->create(['age_representation' => 13]);

        $story->ageRatings()->attach([$ageRating1->id, $ageRating2->id, $ageRating3->id]);
        $story->save(); // Trigger the observer

        $this->assertEquals(18, $story->age_rating_effective_value);
    }

    public function test_effective_age_rating_updates_when_age_ratings_are_added(): void
    {
        /** @var Story */
        $story = Story::factory()->create();
        $ageRating1 = AgeRating::factory()->create(['age_representation' => 10]);

        $story->ageRatings()->attach($ageRating1->id);
        $story->save();

        $this->assertEquals(10, $story->age_rating_effective_value);

        $ageRating2 = AgeRating::factory()->create(['age_representation' => 15]);
        $story->ageRatings()->attach($ageRating2->id);
        $story->save();

        $this->assertEquals(15, $story->age_rating_effective_value);
    }

    public function test_effective_age_rating_updates_when_age_ratings_are_removed(): void
    {
        $story = Story::factory()->create();
        $ageRating1 = AgeRating::factory()->create(['age_representation' => 10]);
        $ageRating2 = AgeRating::factory()->create(['age_representation' => 15]);

        $story->ageRatings()->attach([$ageRating1->id, $ageRating2->id]);
        $story->save();

        $this->assertEquals(15, $story->age_rating_effective_value);

        $story->ageRatings()->detach($ageRating2->id);
        $story->save();

        $this->assertEquals(10, $story->age_rating_effective_value);
    }

    public function test_effective_age_rating_becomes_null_when_all_age_ratings_are_removed(): void
    {
        $story = Story::factory()->create();
        $ageRating = AgeRating::factory()->create(['age_representation' => 10]);

        $story->ageRatings()->attach($ageRating->id);
        $story->save();

        $this->assertEquals(10, $story->age_rating_effective_value);

        $story->ageRatings()->detach($ageRating->id);
        $story->save();

        $this->assertNull($story->age_rating_effective_value);
    }

    public function test_effective_age_rating_updates_when_age_rating_representation_changes(): void
    {
        $story = Story::factory()->create();
        $ageRating = AgeRating::factory()->create(['age_representation' => 10]);

        $story->ageRatings()->attach($ageRating->id);
        $story->save();

        $this->assertEquals(10, $story->age_rating_effective_value);

        $ageRating->age_representation = 20;
        $ageRating->save();

        // Re-attach to trigger observer on story, or reload story and save
        // For simplicity, we'll just save the story to trigger the observer
        $story->save();

        $this->assertEquals(20, $story->age_rating_effective_value);
    }
}
