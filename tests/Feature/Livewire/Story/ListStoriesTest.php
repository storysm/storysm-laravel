<?php

namespace Tests\Feature\Livewire\Story;

use App\Livewire\Story\ListStories;
use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ListStoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_can_be_rendered(): void
    {
        Livewire::test(ListStories::class)
            ->assertStatus(200);
    }

    public function test_component_renders_and_contains_stories_in_table(): void
    {
        // Without login, user cannot access unrated stories
        $this->actingAs(User::factory()->create());

        /** @var Story[] */
        $stories = Story::factory()
            ->count(5)
            ->ensurePublished()
            ->create();

        $component = Livewire::test(ListStories::class);
        $component->assertSuccessful();
        $component->assertSeeHtml('class="fi-ta"');

        foreach ($stories as $story) {
            $component->assertSee($story->title);
            $component->assertSee($story->creator->name);
        }
    }

    public function test_story_view_count_is_conditionally_displayed_in_table(): void
    {
        $this->actingAs(User::factory()->create());

        // Story with view count <= 500 (should not be displayed)
        $storyLowViews = Story::factory()
            ->ensurePublished()
            ->create(['view_count' => 499]);

        // Story with view count > 500 (should be displayed)
        $storyHighViews = Story::factory()
            ->ensurePublished()
            ->create(['view_count' => 501]);

        $component = Livewire::test(ListStories::class);

        // Assert that the high view count is visible
        $component->assertSee($storyHighViews->formattedViewCount());

        // Assert that the low view count is NOT visible
        $component->assertDontSeeHtml('<p class="text-sm">'.$storyLowViews->formattedViewCount().'</p>');
    }
}
