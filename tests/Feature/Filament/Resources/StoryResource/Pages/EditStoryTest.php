<?php

namespace Tests\Feature\Filament\Resources\StoryResource\Pages;

use App\Filament\Resources\StoryResource\Pages\EditStory;
use App\Models\Story;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class EditStoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_renders_the_edit_story_page_with_view_action(): void
    {
        $this->actingAs($this->user);

        $story = Story::factory()->create([
            'creator_id' => $this->user->id,
        ]);

        /** @var Testable */
        $testable = Livewire::test(EditStory::class, ['record' => $story->getRouteKey()]);
        $testable->assertSuccessful();
        $testable->assertActionExists('view');
        $testable->assertActionHasUrl('view', route('stories.show', $story));
    }
}
