<?php

namespace Tests\Feature\Filament\E2E;

use App\Filament\Resources\AgeRatingResource;
use App\Filament\Resources\StoryResource;
use App\Models\AgeRating;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Tests\TestCase;

class AgeRatingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // --- Setup Spatie Permissions and Users ---
        $adminRole = Role::create(['name' => 'admin']);

        // Create permissions for admin (age ratings and stories)
        Permission::create(['name' => 'view_any_age::rating']);
        Permission::create(['name' => 'create_age::rating']);
        Permission::create(['name' => 'update_age::rating']);
        Permission::create(['name' => 'delete_age::rating']);

        Permission::create(['name' => 'view_any_story']);
        Permission::create(['name' => 'view_all_story']);
        Permission::create(['name' => 'create_story']);
        Permission::create(['name' => 'update_story']);
        Permission::create(['name' => 'delete_story']);

        $adminRole->givePermissionTo(Permission::all());

        // Create admin user
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($adminRole);
    }

    public function test_complete_age_rating_workflow_e2e_test(): void
    {
        // 0. Initial Setup: Create a story that will be edited and guest limit years.
        /** @var Story */
        $story = Story::factory()->create([
            'creator_id' => $this->adminUser->id, // Admin is creator for simplicity
        ]);
        Config::set('age_rating.guest_limit_years', 17);

        // --- 1. Admin creates a new age rating ---
        $this->actingAs($this->adminUser);
        $ageRatingName = ['en' => 'Adults Only', 'fr' => 'Adultes Seulement'];
        $ageRatingValue = 18; // Example value

        $livewire = Livewire::test(AgeRatingResource\Pages\CreateAgeRating::class);
        $livewire->fillForm([
            'name' => $ageRatingName,
            'age_representation' => $ageRatingValue,
        ]);
        $livewire->call('create');
        $livewire->assertHasNoErrors();

        $newAgeRating = AgeRating::whereJsonContains('name', ['en' => 'Adults Only'])->firstOrFail();
        $this->assertEquals($ageRatingValue, $newAgeRating->age_representation);

        // --- 2. Admin assigns the new age rating to the story ---
        $this->actingAs($this->adminUser);

        // Check the story has no age ratings initially
        $this->assertCount(0, $story->ageRatings);
        $this->assertNull($story->age_rating_effective_value);

        $livewire = Livewire::test(StoryResource\Pages\EditStory::class, ['record' => $story->id]);
        $livewire->fillForm([
            'ageRatings' => [$newAgeRating->id],
        ]);
        $livewire->call('save');
        $livewire->assertHasNoErrors();

        // Re-fetch the story model from the database and assert the age rating is attached and effective value updated
        $story->refresh();
        $this->assertCount(1, $story->ageRatings);

        // --- 3. Guest user verification: Story with age rating above limit years should be inaccessible ---
        Auth::logout();
        $this->assertGuest(); // Ensure no user is authenticated

        // Attempt to access the story's public URL (assuming a 'story.show' route exists)
        // This part needs to be adapted based on how stories are publicly viewed.
        // For now, we'll simulate a direct HTTP GET request.
        $response = $this->get(route('stories.show', $story)); // Assuming 'story.show' route

        // Assert redirection to login page
        $response->assertRedirect(route('login', [
            'next' => route('stories.show', $story),
        ])); // Assuming Filament's login route
    }
}
