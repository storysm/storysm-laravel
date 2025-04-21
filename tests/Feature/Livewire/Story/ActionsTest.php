<?php

namespace Tests\Feature\Livewire\Story;

use App\Livewire\Story\Actions;
use App\Models\Permission;
use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_can_be_rendered(): void
    {
        $story = Story::factory()->create();

        Livewire::test(Actions::class, ['story' => $story])
            ->assertStatus(200);
    }

    public function test_component_renders_actions_for_story(): void
    {
        $story = Story::factory()->create();

        $component = Livewire::test(Actions::class, ['story' => $story]);

        $component->assertSuccessful();

        // View action should always be visible
        $component->assertSeeHtml(route('stories.show', ['story' => $story]));

        // Edit action should NOT be visible for guests (default state without login)
        $component->assertDontSeeHtml(route('filament.admin.resources.stories.edit', ['record' => $story]));
    }

    public function test_component_renders_actions_for_logged_in_user_with_edit_permission(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actingAs($user);

        $story = Story::factory()->create([
            'creator_id' => $user->id,
        ]);

        $component = Livewire::test(Actions::class, ['story' => $story]);

        $component->assertSuccessful();

        // View action should be visible
        $component->assertSeeHtml(route('stories.show', ['story' => $story]));

        // Edit action should be visible
        $component->assertSeeHtml(route('filament.admin.resources.stories.edit', ['record' => $story]));
    }

    public function test_component_renders_actions_for_logged_in_user_without_edit_permission(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actingAs($user);

        $story = Story::factory()->create();

        $component = Livewire::test(Actions::class, ['story' => $story]);

        $component->assertSuccessful();

        // View action should be visible
        $component->assertSeeHtml(route('stories.show', ['story' => $story]));

        // Edit action should NOT be visible
        $component->assertDontSeeHtml(route('filament.admin.resources.stories.edit', ['record' => $story]));
    }

    public function test_component_renders_actions_for_logged_in_user_with_view_all_permission(): void
    {
        Permission::firstOrCreate(['name' => 'view_all_story']);

        /** @var User */
        $user = User::factory()->create();
        $this->actingAs($user);

        $user->givePermissionTo('view_all_story');

        // Create a story where the user is NOT the creator
        $story = Story::factory()->create();
        $this->assertNotEquals($user->id, $story->creator_id);

        $component = Livewire::test(Actions::class, ['story' => $story]);

        $component->assertSuccessful();

        // View action should be visible (policy allows view if viewAll is true)
        $component->assertSeeHtml(route('stories.show', ['story' => $story]));

        // Edit action should be visible (policy allows update if viewAll is true)
        $component->assertSeeHtml(route('filament.admin.resources.stories.edit', ['record' => $story]));
    }
}
