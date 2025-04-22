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

    /**
     * Data provider for test_formatted_view_count_formats_correctly.
     *
     * @return array<string, array<int|string>>
     */
    public static function viewCountFormattingProvider(): array
    {
        return [
            'less than 1000' => [999, '999'],
            'exactly 1000' => [1000, '1K'],
            'thousands with decimal' => [1300, '1.3K'],
            'thousands with whole number' => [2500, '2.5K'],
            'thousands just under 1000K' => [999000, '999K'],
            'just under 1 million' => [999999, '999.9K'],
            'exactly 1 million' => [1000000, '1M'],
            'millions with decimal' => [1500000, '1.5M'],
            'millions with whole number' => [5000000, '5M'],
            'millions just under 1000M' => [999000000, '999M'],
            'just under 1 billion' => [999999999, '999.9M'],
            'exactly 1 billion' => [1000000000, '1B'],
            'billions with decimal' => [1200000000, '1.2B'],
            'billions just under 1000B' => [999000000000, '999B'],
            'just under 1 trillion' => [999999999999, '999.9B'],
            'exactly 1 trillion' => [1000000000000, '1T'],
            'trillions with decimal' => [2500000000000, '2.5T'],
            'zero' => [0, '0'],
        ];
    }

    /**
     * Test that formattedViewCount formats the view count correctly with suffixes.
     *
     * @dataProvider viewCountFormattingProvider
     */
    public function test_formatted_view_count_formats_correctly(int $viewCount, string $expectedFormat): void
    {
        $story = Story::factory()->create(['view_count' => $viewCount]);

        $this->assertEquals($expectedFormat, $story->formattedViewCount());
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
