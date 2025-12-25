<?php

namespace Tests\Feature\Scopes;

use App\Facades\AgeVerification;
use App\Models\Story;
use App\Scopes\StoryFilterScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoryFilterScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_guest_cannot_see_restricted_or_unrated_content_in_listings(): void
    {
        AgeVerification::setAge(13);

        // Unrated
        Story::factory()->ensurePublished()->create();

        // Restricted
        Story::factory()->ensurePublished()->ensureHasAgeRating(18)->create();

        // Manual application of the scope since it's not a model-level global scope
        $results = Story::withGlobalScope('filter', new StoryFilterScope)->get();

        $this->assertCount(0, $results);
    }

    public function test_verified_adult_can_see_all_content(): void
    {
        // Set age to 18
        AgeVerification::setAge(18);

        // Unrated
        Story::factory()->ensurePublished()->create();

        // Restricted
        Story::factory()->ensurePublished()->ensureHasAgeRating(18)->create();

        $results = Story::withGlobalScope('filter', new StoryFilterScope)->get();

        $this->assertCount(2, $results);
    }

    public function test_minor_can_only_see_age_appropriate_content(): void
    {
        // Set age to 15
        AgeVerification::setAge(15);

        // 18+ Story
        Story::factory()->ensurePublished()->ensureHasAgeRating(18)->create();

        // 13+ Story
        Story::factory()->ensurePublished()->ensureHasAgeRating(13)->create();

        // Unrated Story
        Story::factory()->ensurePublished()->create();

        $results = Story::withGlobalScope('filter', new StoryFilterScope)->get();

        // Should only see the 13+ story
        $this->assertCount(1, $results);
        $this->assertEquals(13, $results->first()?->age_rating_effective_value);
    }
}
