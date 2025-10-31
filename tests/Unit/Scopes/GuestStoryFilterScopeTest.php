<?php

namespace Tests\Unit\Scopes;

use App\Models\AgeRating;
use App\Models\Story;
use App\Scopes\GuestStoryFilterScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class GuestStoryFilterScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure the age_rating config is set for testing
        Config::set('age_rating.guest_limit_years', 16);
    }

    public function test_filters_stories_for_guest_users(): void
    {
        Auth::shouldReceive('guest')->andReturn(true);

        // Create stories with different age ratings
        // Create AgeRating instances
        $ageRating15 = \App\Models\AgeRating::factory()->create(['age_representation' => 15]);
        $ageRating16 = \App\Models\AgeRating::factory()->create(['age_representation' => 16]);
        $ageRating18 = \App\Models\AgeRating::factory()->create(['age_representation' => 18]);

        // Create stories and attach age ratings
        $story15 = Story::factory()->create();
        $story15->ageRatings()->attach($ageRating15);
        $story15->save(); // Trigger observer to calculate age_rating_effective_value

        $story16 = Story::factory()->create();
        $story16->ageRatings()->attach($ageRating16);
        $story16->save(); // Trigger observer

        $story18 = Story::factory()->create();
        $story18->ageRatings()->attach($ageRating18);
        $story18->save(); // Trigger observer

        // Story with no age rating (should have null effective value)
        $storyNull = Story::factory()->create();
        $storyNull->save(); // Trigger observer (will set age_rating_effective_value to null)

        $filteredStories = Story::withoutGlobalScope(GuestStoryFilterScope::class)
            ->withGlobalScope('guest_filter', new GuestStoryFilterScope)
            ->get();

        $this->assertCount(1, $filteredStories);
        $this->assertTrue($filteredStories->contains($story15));
    }

    public function test_does_not_filter_stories_for_authenticated_users(): void
    {
        Auth::shouldReceive('guest')->andReturn(false);

        // Create AgeRating instances
        $ageRating15 = AgeRating::factory()->create(['age_representation' => 15]);
        $ageRating16 = AgeRating::factory()->create(['age_representation' => 16]);
        $ageRating18 = AgeRating::factory()->create(['age_representation' => 18]);

        // Create stories and attach age ratings
        $story15 = Story::factory()->create();
        $story15->ageRatings()->attach($ageRating15);
        $story15->save();

        $story16 = Story::factory()->create();
        $story16->ageRatings()->attach($ageRating16);
        $story16->save();

        $story18 = Story::factory()->create();
        $story18->ageRatings()->attach($ageRating18);
        $story18->save();

        // Story with no age rating
        $storyNull = Story::factory()->create();
        $storyNull->save();

        $filteredStories = Story::withoutGlobalScope(GuestStoryFilterScope::class)
            ->withGlobalScope('guest_filter', new GuestStoryFilterScope)
            ->get();

        $this->assertCount(4, $filteredStories); // All stories should be returned
        $this->assertTrue($filteredStories->contains($story15));
        $this->assertTrue($filteredStories->contains($story16));
        $this->assertTrue($filteredStories->contains($story18));
        $this->assertTrue($filteredStories->contains($storyNull));
    }
}
