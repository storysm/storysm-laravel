<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\AgeRatingResource;
use App\Filament\Resources\AgeRatingResource\Pages\CreateAgeRating;
use App\Filament\Resources\AgeRatingResource\Pages\EditAgeRating;
use App\Filament\Resources\AgeRatingResource\Pages\ListAgeRatings;
use App\Models\AgeRating;
use App\Models\Story;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class AgeRatingResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $unauthorizedUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the users
        $this->adminUser = User::factory()->create();
        $this->unauthorizedUser = User::factory()->create();

        Config::set('auth.super_users', [$this->adminUser->email]);

        // Set the Filament panel context
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    // --- Policy Enforcement Tests (Non-Admin Users) ---

    public function test_unauthorized_user_cannot_access_age_rating_list_page(): void
    {
        $this->actingAs($this->unauthorizedUser)
            ->get(AgeRatingResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_unauthorized_user_cannot_access_age_rating_create_page(): void
    {
        $this->actingAs($this->unauthorizedUser)
            ->get(AgeRatingResource::getUrl('create'))
            ->assertForbidden();
    }

    public function test_unauthorized_user_cannot_access_age_rating_edit_page(): void
    {
        $ageRating = AgeRating::factory()->create();

        $this->actingAs($this->unauthorizedUser)
            ->get(AgeRatingResource::getUrl('edit', ['record' => $ageRating]))
            ->assertForbidden();
    }

    // --- CRUD Tests (Admin Users) ---

    public function test_admin_can_view_age_rating_list(): void
    {
        $ageRatings = AgeRating::factory(3)->create();

        $this->actingAs($this->adminUser);
        $livewire = Livewire::test(ListAgeRatings::class);
        $livewire->assertCanSeeTableRecords($ageRatings);
        $livewire->assertSuccessful();
    }

    public function test_admin_can_create_an_age_rating(): void
    {
        $this->actingAs($this->adminUser);

        $data = [
            'name' => [
                'en' => 'Test Age Rating EN '.Str::random(5),
                'id' => 'Peringkat Usia Uji ID '.Str::random(5),
            ],
            'description' => [
                'en' => '<p>Test Description EN</p>',
                'id' => '<p>Deskripsi Uji ID</p>',
            ],
            'age_representation' => rand(1, 18),
        ];

        $this->actingAs($this->adminUser);
        $livewire = Livewire::test(CreateAgeRating::class);
        $livewire->fillForm($data);
        $livewire->call('create');
        $livewire->assertHasNoFormErrors();

        $this->assertDatabaseHas('age_ratings', [
            'name' => json_encode($data['name'], JSON_UNESCAPED_SLASHES),
            'description' => json_encode($data['description'], JSON_UNESCAPED_SLASHES),
            'age_representation' => $data['age_representation'],
        ]);
    }

    public function test_admin_can_edit_an_age_rating(): void
    {
        $ageRating = AgeRating::factory()->create([
            'name' => ['en' => 'Old Name', 'id' => 'Nama Lama'],
            'description' => [
                'en' => '<p>Description EN</p>',
                'id' => '<p>Deskripsi ID</p>',
            ],
            'age_representation' => 10,
        ]);

        $this->actingAs($this->adminUser);

        $newData = [
            'name' => [
                'en' => 'New Name EN '.Str::random(5),
                'id' => 'Nama Baru ID '.Str::random(5),
            ],
            'description' => [
                'en' => '<p>Updated Description EN</p>',
                'id' => '<p>Deskripsi Diperbarui ID</p>',
            ],
            'age_representation' => 15,
        ];

        $this->actingAs($this->adminUser);
        $livewire = Livewire::test(EditAgeRating::class, ['record' => $ageRating->id]);
        $livewire->fillForm($newData);
        $livewire->call('save');
        $livewire->assertHasNoFormErrors();

        $this->assertDatabaseHas('age_ratings', [
            'id' => $ageRating->id,
            'name' => json_encode($newData['name'], JSON_UNESCAPED_SLASHES),
            'description' => json_encode($newData['description'], JSON_UNESCAPED_SLASHES),
            'age_representation' => $newData['age_representation'],
        ]);
    }

    // --- Deletion and Reference Check Tests ---

    public function test_admin_can_delete_an_unreferenced_age_rating(): void
    {
        $ageRating = AgeRating::factory()->create();

        $this->actingAs($this->adminUser);
        $livewire = Livewire::test(EditAgeRating::class, ['record' => $ageRating->id]);
        $livewire->callAction(DeleteAction::class);
        $livewire->assertSuccessful();

        $this->assertModelMissing($ageRating);
    }

    public function test_delete_action_is_hidden_for_referenced_age_rating(): void
    {
        $ageRating = AgeRating::factory()->create();
        // Create a story and attach the age rating, making it 'referenced'
        Story::factory()->hasAttached($ageRating, [], 'ageRatings')->create();

        // Check on the List page (Table Action)
        $this->actingAs($this->adminUser);
        $livewire = Livewire::test(ListAgeRatings::class);
        // Assert that the delete action is hidden for the specific record
        $livewire->assertTableActionHidden('delete', $ageRating);

        // Check on the Edit page (Header Action)
        $livewire = Livewire::test(EditAgeRating::class, ['record' => $ageRating->id]);
        $livewire->assertActionHidden('delete');
    }

    public function test_admin_can_bulk_delete_unreferenced_age_ratings(): void
    {
        $ageRatings = AgeRating::factory(3)->create();

        $this->actingAs($this->adminUser);
        $livewire = Livewire::test(ListAgeRatings::class);
        $livewire->callTableBulkAction(DeleteBulkAction::class, $ageRatings);

        foreach ($ageRatings as $ageRating) {
            $this->assertModelMissing($ageRating);
        }
    }

    public function test_admin_bulk_deletion_protects_referenced_age_ratings(): void
    {
        // One referenced, two unreferenced
        $referencedAgeRating = AgeRating::factory()->create();
        $unreferencedAgeRatings = AgeRating::factory(2)->create();
        Story::factory()->hasAttached($referencedAgeRating, [], 'ageRatings')->create();

        $allAgeRatings = collect([$referencedAgeRating])->merge($unreferencedAgeRatings);

        $this->actingAs($this->adminUser);
        $livewire = Livewire::test(ListAgeRatings::class);
        // Call bulk action with all three age ratings
        $livewire->callTableBulkAction('delete', $allAgeRatings->pluck('id')->toArray());

        // Assert that the referenced age rating is protected and still exists
        $this->assertModelExists($referencedAgeRating);

        // Assert that the unreferenced age ratings were successfully deleted
        foreach ($unreferencedAgeRatings as $ageRating) {
            $this->assertDatabaseMissing('age_ratings', ['id' => $ageRating->id]);
        }
    }

    public function test_assigning_age_ratings_to_story_updates_effective_age_rating(): void
    {
        /** @var Story */
        $story = Story::factory()->create();
        $ageRating1 = AgeRating::factory()->create(['age_representation' => 10]);
        $ageRating2 = AgeRating::factory()->create(['age_representation' => 15]);

        $this->actingAs($this->adminUser);

        // Test attaching age ratings
        $livewire = Livewire::test(\App\Filament\Resources\StoryResource\Pages\EditStory::class, ['record' => $story->id]);
        $livewire->fillForm(['ageRatings' => [$ageRating1->id, $ageRating2->id]]);
        $livewire->call('save');
        $livewire->assertHasNoFormErrors();

        $story = $story->refresh();
        $this->assertEquals(15, $story->age_rating_effective_value);

        // Test detaching an age rating
        $livewire = Livewire::test(\App\Filament\Resources\StoryResource\Pages\EditStory::class, ['record' => $story->id]);
        $livewire->fillForm(['ageRatings' => [$ageRating1->id]]);
        $livewire->call('save');
        $livewire->assertHasNoFormErrors();

        $story->refresh();
        $this->assertEquals(10, $story->age_rating_effective_value);

        // Test detaching all age ratings
        $livewire = Livewire::test(\App\Filament\Resources\StoryResource\Pages\EditStory::class, ['record' => $story->id]);
        $livewire->fillForm(['ageRatings' => []]);
        $livewire->call('save');
        $livewire->assertHasNoFormErrors();

        $story->refresh();
        $this->assertNull($story->age_rating_effective_value);
    }
}
