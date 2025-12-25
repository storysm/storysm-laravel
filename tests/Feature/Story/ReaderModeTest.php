<?php

namespace Tests\Feature\Story;

use App\Livewire\Story\ViewStory;
use App\Models\Story;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReaderModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_story_page_renders_successfully(): void
    {
        $story = Story::factory()->ensurePublished()->create();

        Livewire::test(ViewStory::class, ['story' => $story])
            ->assertSuccessful();
    }

    public function test_story_page_contains_reader_controls(): void
    {
        $story = Story::factory()->ensurePublished()->ensureHasAgeRating(13)->create();

        Livewire::test(ViewStory::class, ['story' => $story])
            ->assertSuccessful()
            ->assertSee('x-data="readerControls"', false); // Verify Alpine component is present
    }
}
