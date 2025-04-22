<?php

namespace Tests\Feature\Models;

use App\Enums\Story\Status;
use App\Models\Story;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class StoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, Story>
     */
    private function createStories(): array
    {
        return [
            'publishedStoryPast' => Story::factory()->create([
                'status' => Status::Publish,
                'published_at' => Carbon::now()->subDay(),
            ]),
            'pendingStory' => Story::factory()->create([
                'status' => Status::Publish,
                'published_at' => Carbon::now()->addDay(),
            ]),
            'draftStory' => Story::factory()->create([
                'status' => Status::Draft,
                'published_at' => Carbon::now()->subDay(),
            ]),
        ];
    }

    public function test_increment_view_count_protected_by_session_and_time(): void
    {
        // Use Carbon to control time during the test
        $now = Carbon::now();
        Carbon::setTestNow($now);

        $story = Story::factory()->create(['view_count' => 0]);

        // 1. First call: Should increment
        $story->incrementViewCount();
        /** @var array<string, int> */
        $viewedStories = Session::get('viewed_stories');
        $this->assertEquals(1, $story->fresh()?->view_count, 'First call should increment');
        $this->assertArrayHasKey($story->id, $viewedStories, 'Session should record first view');
        $this->assertEquals($now->timestamp, $viewedStories[$story->id], 'Session timestamp should be recorded');

        // 2. Second call immediately: Should NOT increment (session protection)
        $story->incrementViewCount();
        /** @var array<string, int> */
        $viewedStories = Session::get('viewed_stories');
        $this->assertEquals(1, $story->fresh()?->view_count, 'Second call immediately should not increment');
        $this->assertEquals($now->timestamp, $viewedStories[$story->id], 'Session timestamp should not change on immediate second call');

        // 3. Advance time by less than 60 seconds (e.g., 30 seconds)
        Carbon::setTestNow($now->copy()->addSeconds(30));
        $story = $story->fresh(); // Reload the model to ensure state is fresh if needed, though increment updates it

        $story?->incrementViewCount();
        /** @var array<string, int> */
        $viewedStories = Session::get('viewed_stories');
        $this->assertEquals(1, $story?->fresh()?->view_count, 'Call after < 60s should not increment');
        $this->assertEquals($now->timestamp, $viewedStories[$story?->id], 'Session timestamp should not change after < 60s');

        // 4. Advance time by more than 60 seconds (e.g., 61 seconds from the *original* $now)
        Carbon::setTestNow($now->copy()->addSeconds(61));
        $story = $story?->fresh(); // Reload

        $story?->incrementViewCount();
        /** @var array<string, int> */
        $viewedStories = Session::get('viewed_stories');
        $this->assertEquals(2, $story?->fresh()?->view_count, 'Call after > 60s should increment');
        $this->assertEquals(Carbon::now()->timestamp, $viewedStories[$story?->id], 'Session timestamp should be updated after > 60s');

        // 5. Fifth call immediately after the time elapsed increment: Should NOT increment (session protection resets)
        $story?->incrementViewCount();
        /** @var array<string, int> */
        $viewedStories = Session::get('viewed_stories');
        $this->assertEquals(2, $story?->fresh()?->view_count, 'Fifth call immediately should not increment');
        $this->assertEquals(Carbon::now()->timestamp, $viewedStories[$story?->id], 'Session timestamp should not change on immediate fifth call');

        // Clean up Carbon test time
        Carbon::setTestNow(null);
    }

    public function test_only_returns_published_stories(): void
    {
        $stories = $this->createStories();
        $publishedStories = Story::published()->get();

        $this->assertCount(1, $publishedStories);
        $this->assertTrue($publishedStories->contains($stories['publishedStoryPast']));
        $this->assertFalse($publishedStories->contains($stories['pendingStory']));
        $this->assertFalse($publishedStories->contains($stories['draftStory']));
    }

    public function test_only_returns_pending_stories(): void
    {
        $stories = $this->createStories();
        $pendingStories = Story::pending()->get();

        $this->assertCount(1, $pendingStories);
        $this->assertTrue($pendingStories->contains($stories['pendingStory']));
        $this->assertFalse($pendingStories->contains($stories['publishedStoryPast']));
        $this->assertFalse($pendingStories->contains($stories['draftStory']));
    }
}
