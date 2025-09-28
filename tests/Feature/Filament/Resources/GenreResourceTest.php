<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\GenreResource;
use App\Filament\Resources\GenreResource\Pages\CreateGenre;
use App\Filament\Resources\GenreResource\Pages\EditGenre;
use App\Filament\Resources\GenreResource\Pages\ListGenres;
use App\Models\Genre;
use App\Models\Permission;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GenreResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $unauthorizedUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->create();
        $this->unauthorizedUser = User::factory()->create();

        // Ensure permissions exist for Genre resource
        foreach (GenreResource::getPermissionPrefixes() as $prefix) {
            Permission::firstOrCreate(['name' => $prefix.'_genre']);
        }

        // Assign all genre permissions to adminUser
        $this->adminUser->givePermissionTo(collect(GenreResource::getPermissionPrefixes())->map(fn ($prefix) => $prefix.'_genre')->toArray());

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_admin_user_can_view_list_of_genres(): void
    {
        $this->actingAs($this->adminUser);
        Genre::factory()->count(3)->create();

        Livewire::test(ListGenres::class)
            ->assertCanSeeTableRecords(Genre::all());
    }

    public function test_unauthorized_user_cannot_view_list_of_genres(): void
    {
        $this->actingAs($this->unauthorizedUser);
        Genre::factory()->count(3)->create();

        $this->get(GenreResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_admin_user_can_create_genre(): void
    {
        $this->actingAs($this->adminUser);

        $livewire = Livewire::test(CreateGenre::class);
        $livewire->fillForm([
            'name' => [
                'en' => 'Test Genre EN',
                'id' => 'Test Genre ID',
            ],
            'description' => [
                'en' => 'Test Description EN',
                'id' => 'Test Description ID',
            ],
        ]);
        $livewire->call('create');
        $livewire->assertHasNoFormErrors();

        $this->assertDatabaseHas('genres', [
            'name' => json_encode([
                'en' => 'Test Genre EN',
                'id' => 'Test Genre ID',
            ]),
            'description' => '{"en":"<p>Test Description EN</p>","id":"<p>Test Description ID</p>"}',
        ]);
    }

    public function test_unauthorized_user_cannot_create_genre(): void
    {
        $this->actingAs($this->unauthorizedUser);

        $this->get(GenreResource::getUrl('create'))
            ->assertForbidden();
    }

    public function test_user_with_only_view_permissions_cannot_create_genre(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['view_any_genre', 'view_genre']);
        $this->actingAs($user);

        // Attempt to access the create page
        $this->get(GenreResource::getUrl('create'))
            ->assertForbidden();

        // Attempt to create a genre via Livewire
        $livewire = Livewire::test(CreateGenre::class);
        $livewire->assertForbidden(); // Expecting a forbidden response
    }

    public function test_genre_creation_requires_name(): void
    {
        $this->actingAs($this->adminUser);

        $livewire = Livewire::test(CreateGenre::class);
        $livewire->fillForm([
            'name' => [
                'en' => '',
                'id' => '',
            ],
            'description' => [
                'en' => 'Some description',
                'id' => 'Some description',
            ],
        ]);
        $livewire->call('create');
        $livewire->assertHasFormErrors([
            'name.en' => 'required',
            'name.id' => 'required',
        ]);
    }

    public function test_genre_creation_with_existing_name_fails_validation(): void
    {
        $this->actingAs($this->adminUser);

        Genre::factory()->create([
            'name' => [
                'en' => 'Existing Genre EN',
                'id' => 'Existing Genre ID',
            ],
        ]);

        $livewire = Livewire::test(CreateGenre::class);
        $livewire->fillForm([
            'name' => [
                'en' => 'Existing Genre EN',
                'id' => 'New Genre ID',
            ],
            'description' => [
                'en' => 'Some description',
                'id' => 'Some description',
            ],
        ]);
        $livewire->call('create');
        $livewire->assertHasFormErrors([
            'name.en',
        ]);

        $livewire = Livewire::test(CreateGenre::class);
        $livewire->fillForm([
            'name' => [
                'en' => 'New Genre EN',
                'id' => 'Existing Genre ID',
            ],
            'description' => [
                'en' => 'Some description',
                'id' => 'Some description',
            ],
        ]);
        $livewire->call('create');
        $livewire->assertHasFormErrors([
            'name.id',
        ]);
    }

    public function test_admin_user_can_update_genre(): void
    {
        $this->actingAs($this->adminUser);
        $genre = Genre::factory()->create([
            'name' => [
                'en' => 'Original Name EN',
                'id' => 'Original Name ID',
            ],
            'description' => [
                'en' => 'Original Description EN',
                'id' => 'Original Description ID',
            ],
        ]);

        $livewire = Livewire::test(EditGenre::class, ['record' => $genre->getRouteKey()]);
        $livewire->fillForm([
            'name' => [
                'en' => 'Updated Name EN',
                'id' => 'Updated Name ID',
            ],
            'description' => [
                'en' => 'Updated Description EN',
                'id' => 'Updated Description ID',
            ],
        ]);
        $livewire->call('save');
        $livewire->assertHasNoFormErrors();

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => json_encode([
                'en' => 'Updated Name EN',
                'id' => 'Updated Name ID',
            ]),
            'description' => '{"en":"<p>Updated Description EN</p>","id":"<p>Updated Description ID</p>"}',
        ]);
    }

    public function test_unauthorized_user_cannot_update_genre(): void
    {
        $this->actingAs($this->unauthorizedUser);
        $genre = Genre::factory()->create();

        $this->get(GenreResource::getUrl('edit', ['record' => $genre->getRouteKey()]))
            ->assertForbidden();
    }

    public function test_unauthorized_user_cannot_delete_genre(): void
    {
        $this->unauthorizedUser->givePermissionTo([
            'view_any_genre',
            'update_genre',
        ]);
        $this->actingAs($this->unauthorizedUser);
        $genre = Genre::factory()->create();

        Livewire::test(EditGenre::class, ['record' => $genre->getRouteKey()])
            ->assertActionHidden('delete');
    }

    public function test_genre_update_requires_name(): void
    {
        $this->actingAs($this->adminUser);
        $genre = Genre::factory()->create([
            'name' => [
                'en' => 'Original Name EN',
                'id' => 'Original Name ID',
            ],
        ]);

        $livewire = Livewire::test(EditGenre::class, ['record' => $genre->getRouteKey()]);
        $livewire->fillForm([
            'name' => [
                'en' => '',
                'id' => '',
            ],
        ]);
        $livewire->call('save');
        $livewire->assertHasFormErrors([
            'name.en' => 'required',
            'name.id' => 'required',
        ]);
    }

    public function test_admin_user_can_delete_genre(): void
    {
        $this->actingAs($this->adminUser);
        $genre = Genre::factory()->create();

        Livewire::test(EditGenre::class, ['record' => $genre->getRouteKey()])
            ->call('mountAction', 'delete')
            ->call('callMountedAction');

        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
        ]);
    }

    public function test_admin_user_cannot_delete_genre_assigned_to_story(): void
    {
        $this->actingAs($this->adminUser);
        $genre = Genre::factory()->create();
        $story = \App\Models\Story::factory()->create();
        $story->genres()->attach($genre);

        Livewire::test(EditGenre::class, ['record' => $genre->getRouteKey()])
            ->call('mountAction', 'delete')
            ->call('callMountedAction');

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
        ]);
    }

    public function test_admin_user_can_bulk_delete_genres(): void
    {
        $this->actingAs($this->adminUser);
        $genres = Genre::factory()->count(3)->create();

        Livewire::test(ListGenres::class)
            ->callTableBulkAction('delete', $genres);

        foreach ($genres as $genre) {
            $this->assertDatabaseMissing('genres', [
                'id' => $genre->id,
            ]);
        }
    }

    public function test_admin_user_cannot_bulk_delete_genres_if_any_are_assigned_to_story(): void
    {
        $this->actingAs($this->adminUser);
        $genres = Genre::factory()->count(3)->create();
        $story = \App\Models\Story::factory()->create();
        $story->genres()->attach($genres->first());

        Livewire::test(ListGenres::class)
            ->callTableBulkAction('delete', $genres);

        $this->assertDatabaseHas('genres', [
            'id' => $genres->first()?->id,
        ]);
    }
}
