<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Story\ViewStory;
use App\Models\Media;
use App\Models\Story;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Livewire\Livewire;
use Tests\TestCase;

class ViewStoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_view_story_component_renders_with_story(): void
    {
        $story = Story::factory()->create([
            'title' => 'Test Story Title',
            'content' => '<p>This is the test story content.</p>',
        ]);

        Livewire::test(ViewStory::class, ['story' => $story])
            ->assertViewIs('livewire.story.view-story')
            ->assertSee($story->title)
            ->assertSee(strip_tags($story->content));
    }

    public function test_view_story_component_sets_seo_metadata_without_cover(): void
    {
        $story = Story::factory()->create([
            'title' => 'Test Story Title',
            'content' => '<p>This is the test story content.</p>',
        ]);

        $expectedDescription = Str::limit(strip_tags($story->content), 160);

        SEOTools::shouldReceive('setTitle')->once()->with($story->title);
        SEOTools::shouldReceive('setDescription')->once()->with($expectedDescription);

        SEOTools::shouldReceive('opengraph->setTitle')->once()->with($story->title);
        SEOTools::shouldReceive('opengraph->setDescription')->once()->with($expectedDescription);
        SEOTools::shouldReceive('twitter->setTitle')->once()->with($story->title);
        SEOTools::shouldReceive('twitter->setDescription')->once()->with($expectedDescription);
        SEOTools::shouldReceive('jsonLd->setTitle')->once()->with($story->title);
        SEOTools::shouldReceive('jsonLd->setDescription')->once()->with($expectedDescription);
        SEOTools::shouldReceive('jsonLd->setType')->once()->with('Article');

        $mockOpengraph = \Mockery::mock();
        $mockOpengraph->shouldNotReceive('addImage');

        $mockTwitter = \Mockery::mock();
        $mockTwitter->shouldNotReceive('addImage');

        $mockJsonLd = \Mockery::mock();
        $mockJsonLd->shouldNotReceive('addImage');

        Livewire::test(ViewStory::class, ['story' => $story]);
    }

    public function test_view_story_component_sets_seo_metadata_with_cover(): void
    {
        /** @var Story */
        $story = Story::factory()->create([
            'title' => 'Test Story Title',
            'content' => '<p>This is the test story content.</p>',
        ]);

        $originalPath = 'test.png';
        $image = Image::canvas(100, 100, 'ffffff');
        Storage::disk('public')->put($originalPath, $image->stream('png'));
        $media = Media::factory()->create([
            'name' => 'Test Image',
            'path' => $originalPath,
            'disk' => 'public',
            'size' => 1024,
            'type' => 'image/png',
            'ext' => 'png',
        ]);

        $story->coverMedia()->associate($media);
        $story->save();
        $coverImageUrl = $story->coverMedia?->url;

        $this->assertNotNull($coverImageUrl);
        $this->assertNotEmpty($coverImageUrl);

        $expectedDescription = Str::limit(strip_tags($story->content), 160);

        SEOTools::shouldReceive('setTitle')->once()->with($story->title);
        SEOTools::shouldReceive('setDescription')->once()->with($expectedDescription);
        SEOTools::shouldReceive('opengraph->setTitle')->once()->with($story->title);
        SEOTools::shouldReceive('opengraph->setDescription')->once()->with($expectedDescription);
        SEOTools::shouldReceive('twitter->setTitle')->once()->with($story->title);
        SEOTools::shouldReceive('twitter->setDescription')->once()->with($expectedDescription);
        SEOTools::shouldReceive('jsonLd->setTitle')->once()->with($story->title);
        SEOTools::shouldReceive('jsonLd->setDescription')->once()->with($expectedDescription);
        SEOTools::shouldReceive('jsonLd->setType')->once()->with('Article');

        SEOTools::shouldReceive('opengraph->addImage')->once()->with($coverImageUrl);
        SEOTools::shouldReceive('twitter->addImage')->once()->with($coverImageUrl);
        SEOTools::shouldReceive('jsonLd->addImage')->once()->with($coverImageUrl);

        Livewire::test(ViewStory::class, ['story' => $story]);
    }

    public function test_view_story_component_generates_breadcrumbs(): void
    {
        $story = Story::factory()->create([
            'title' => 'A Very Long Story Title That Needs Truncating For Breadcrumbs',
        ]);

        $component = new ViewStory;
        $component->story = $story;

        $breadcrumbs = $component->getBreadcrumbs();

        $this->assertCount(3, $breadcrumbs);

        $this->assertArrayHasKey(route('home'), $breadcrumbs);
        $this->assertArrayHasKey(route('stories.index'), $breadcrumbs);
        $this->assertArrayHasKey(0, $breadcrumbs); // The last item has key 0

        $this->assertEquals(__('navigation-menu.menu.home'), $breadcrumbs[route('home')]);
        $this->assertEquals(trans_choice('story.resource.model_label', 2), $breadcrumbs[route('stories.index')]);
        $this->assertEquals(Str::limit($story->title, 50), $breadcrumbs[0]);
    }
}
