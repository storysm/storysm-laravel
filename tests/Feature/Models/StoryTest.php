<?php

namespace Tests\Feature\Models;

use App\Enums\Story\Status;
use App\Models\Story;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
