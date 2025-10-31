<?php

namespace Tests\Feature\Http\Middleware;

use App\Enums\Story\Status;
use App\Models\AgeRating;
use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class RedirectRestrictedContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('age_rating.guest_limit_years', 16);
    }

    public function test_guest_can_access_story_rated_below_limit(): void
    {
        $ageRating15 = AgeRating::factory()->create(['age_representation' => 15]);
        $story = Story::factory()->create(['status' => Status::Publish]);
        $story->ageRatings()->attach($ageRating15);
        $story->save();

        $response = $this->get(route('stories.show', $story));

        $response->assertOk();
    }

    public function test_guest_is_redirected_from_story_rated_at_or_above_limit(): void
    {
        $ageRating16 = AgeRating::factory()->create(['age_representation' => 16]);
        $ageRating18 = AgeRating::factory()->create(['age_representation' => 18]);

        $storyAtLimit = Story::factory()->create(['status' => Status::Publish]);
        $storyAtLimit->ageRatings()->attach($ageRating16);
        $storyAtLimit->save();

        $storyAboveLimit = Story::factory()->create(['status' => Status::Publish]);
        $storyAboveLimit->ageRatings()->attach($ageRating18);
        $storyAboveLimit->save();

        $responseAtLimit = $this->get(route('stories.show', $storyAtLimit));
        $responseAboveLimit = $this->get(route('stories.show', $storyAboveLimit));

        $responseAtLimit->assertRedirect(route('login', ['next' => route('stories.show', $storyAtLimit)]));
        $responseAboveLimit->assertRedirect(route('login', ['next' => route('stories.show', $storyAboveLimit)]));
    }

    public function test_guest_is_redirected_from_unrated_story(): void
    {
        $story = Story::factory()->create(['status' => Status::Publish]);
        $story->save();

        $response = $this->get(route('stories.show', $story));

        $response->assertRedirect(route('login', ['next' => route('stories.show', $story)]));
    }

    public function test_authenticated_user_can_access_story_rated_above_limit(): void
    {
        $user = User::factory()->create();
        $ageRating18 = AgeRating::factory()->create(['age_representation' => 18]);
        $story = Story::factory()->create(['status' => Status::Publish]);
        $story->ageRatings()->attach($ageRating18);
        $story->save();

        $response = $this->actingAs($user)->get(route('stories.show', $story));

        $response->assertOk();
    }
}
