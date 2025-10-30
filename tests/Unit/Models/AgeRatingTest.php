<?php

namespace Tests\Unit\Models;

use App\Models\AgeRating;
use App\Models\Story;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgeRatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_age_rating_can_be_associated_with_multiple_stories(): void
    {
        $ageRating = AgeRating::factory()->create();
        $stories = Story::factory()->count(3)->create();

        $ageRating->stories()->attach($stories->pluck('id'));

        $this->assertCount(3, $ageRating->stories);
        $firstStory = $stories->first();
        $this->assertNotNull($firstStory);
        $this->assertTrue($ageRating->stories->contains($firstStory));
    }

    public function test_is_referenced_returns_true_if_associated_with_stories(): void
    {
        $ageRating = AgeRating::factory()->create();
        $story = Story::factory()->create();
        $ageRating->stories()->attach($story);

        $this->assertTrue($ageRating->isReferenced());
    }

    public function test_is_referenced_returns_false_if_not_associated_with_stories(): void
    {
        $ageRating = AgeRating::factory()->create();

        $this->assertFalse($ageRating->isReferenced());
    }
}
