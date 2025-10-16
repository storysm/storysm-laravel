<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\CategoryResource\Pages\CreateCategory;
use App\Filament\Resources\CategoryResource\Pages\EditCategory;
use App\Filament\Resources\CategoryResource\Pages\ListCategories;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Story;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $unauthorizedUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->create();
        $this->unauthorizedUser = User::factory()->create();

        // Ensure permissions exist for Category resource
        foreach (CategoryResource::getPermissionPrefixes() as $prefix) {
            Permission::firstOrCreate(['name' => $prefix.'_category']);
        }

        // Assign all category permissions to adminUser
        $this->adminUser->givePermissionTo(collect(CategoryResource::getPermissionPrefixes())->map(fn ($prefix) => $prefix.'_category')->toArray());

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_admin_user_can_view_list_of_categories(): void
    {
        $this->actingAs($this->adminUser);
        Category::factory()->count(3)->create();

        Livewire::test(ListCategories::class)
            ->assertCanSeeTableRecords(Category::all());
    }

    public function test_unauthorized_user_cannot_view_list_of_categories(): void
    {
        $this->actingAs($this->unauthorizedUser);
        Category::factory()->count(3)->create();

        $this->get(CategoryResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_admin_user_can_create_category(): void
    {
        $this->actingAs($this->adminUser);

        $livewire = Livewire::test(CreateCategory::class);
        $livewire->fillForm([
            'name' => [
                'en' => 'Test Category EN',
                'id' => 'Test Category ID',
            ],
            'description' => [
                'en' => 'Test Description EN',
                'id' => 'Test Description ID',
            ],
        ]);
        $livewire->call('create');
        $livewire->assertHasNoFormErrors();

        $this->assertDatabaseHas('categories', [
            'name' => json_encode([
                'en' => 'Test Category EN',
                'id' => 'Test Category ID',
            ]),
            'description' => '{"en":"<p>Test Description EN</p>","id":"<p>Test Description ID</p>"}',
        ]);
    }

    public function test_unauthorized_user_cannot_create_category(): void
    {
        $this->actingAs($this->unauthorizedUser);

        $this->get(CategoryResource::getUrl('create'))
            ->assertForbidden();
    }

    public function test_user_with_only_view_permissions_cannot_create_category(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['view_any_category', 'view_category']);
        $this->actingAs($user);

        // Attempt to access the create page
        $this->get(CategoryResource::getUrl('create'))
            ->assertForbidden();

        // Attempt to create a category via Livewire
        $livewire = Livewire::test(CreateCategory::class);
        $livewire->assertForbidden(); // Expecting a forbidden response
    }

    public function test_category_creation_requires_name(): void
    {
        $this->actingAs($this->adminUser);

        $livewire = Livewire::test(CreateCategory::class);
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

    public function test_category_creation_with_existing_name_fails_validation(): void
    {
        $this->actingAs($this->adminUser);

        Category::factory()->create([
            'name' => [
                'en' => 'Existing Category EN',
                'id' => 'Existing Category ID',
            ],
        ]);

        $livewire = Livewire::test(CreateCategory::class);
        $livewire->fillForm([
            'name' => [
                'en' => 'Existing Category EN',
                'id' => 'New Category ID',
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

        $livewire = Livewire::test(CreateCategory::class);
        $livewire->fillForm([
            'name' => [
                'en' => 'New Category EN',
                'id' => 'Existing Category ID',
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

    public function test_admin_user_can_update_category(): void
    {
        $this->actingAs($this->adminUser);
        $category = Category::factory()->create([
            'name' => [
                'en' => 'Original Name EN',
                'id' => 'Original Name ID',
            ],
            'description' => [
                'en' => 'Original Description EN',
                'id' => 'Original Description ID',
            ],
        ]);

        $livewire = Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()]);
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

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => json_encode([
                'en' => 'Updated Name EN',
                'id' => 'Updated Name ID',
            ]),
            'description' => '{"en":"<p>Updated Description EN</p>","id":"<p>Updated Description ID</p>"}',
        ]);
    }

    public function test_unauthorized_user_cannot_update_category(): void
    {
        $this->actingAs($this->unauthorizedUser);
        $category = Category::factory()->create();

        $this->get(CategoryResource::getUrl('edit', ['record' => $category->getRouteKey()]))
            ->assertForbidden();
    }

    public function test_unauthorized_user_cannot_delete_category(): void
    {
        $this->unauthorizedUser->givePermissionTo([
            'view_any_category',
            'update_category',
        ]);
        $this->actingAs($this->unauthorizedUser);
        $category = Category::factory()->create();

        Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
            ->assertActionHidden('delete');
    }

    public function test_category_update_requires_name(): void
    {
        $this->actingAs($this->adminUser);
        $category = Category::factory()->create([
            'name' => [
                'en' => 'Original Name EN',
                'id' => 'Original Name ID',
            ],
        ]);

        $livewire = Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()]);
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

    public function test_admin_user_can_delete_category(): void
    {
        $this->actingAs($this->adminUser);
        $category = Category::factory()->create();

        Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
            ->call('mountAction', 'delete')
            ->call('callMountedAction');

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_admin_user_cannot_delete_category_assigned_to_story(): void
    {
        $this->actingAs($this->adminUser);
        $category = Category::factory()->create();
        $story = Story::factory()->create();
        $story->categories()->attach($category);

        Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
            ->call('mountAction', 'delete')
            ->call('callMountedAction');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_admin_user_can_bulk_delete_categories(): void
    {
        $this->actingAs($this->adminUser);
        $categories = Category::factory()->count(3)->create();

        Livewire::test(ListCategories::class)
            ->callTableBulkAction('delete', $categories);

        foreach ($categories as $category) {
            $this->assertDatabaseMissing('categories', [
                'id' => $category->id,
            ]);
        }
    }

    public function test_admin_user_cannot_bulk_delete_categories_if_any_are_assigned_to_story(): void
    {
        $this->actingAs($this->adminUser);
        $categories = Category::factory()->count(3)->create();
        $story = Story::factory()->create();
        $story->categories()->attach($categories->first());

        Livewire::test(ListCategories::class)
            ->callTableBulkAction('delete', $categories);

        $this->assertDatabaseHas('categories', [
            'id' => $categories->first()?->id,
        ]);
    }

    public function test_categories_can_be_sorted_by_stories_count(): void
    {
        $this->actingAs($this->adminUser);

        $categoryA = Category::factory()->create(['name' => ['en' => 'Category A']]);
        Story::factory()->count(5)->create()->each(fn ($story) => $story->categories()->attach($categoryA));

        $categoryB = Category::factory()->create(['name' => ['en' => 'Category B']]);
        Story::factory()->count(2)->create()->each(fn ($story) => $story->categories()->attach($categoryB));

        $categoryC = Category::factory()->create(['name' => ['en' => 'Category C']]);
        Story::factory()->count(8)->create()->each(fn ($story) => $story->categories()->attach($categoryC));

        $livewire = Livewire::test(ListCategories::class);
        $livewire->sortTable('stories_count', 'asc');
        $livewire->assertCanSeeTableRecords([$categoryB, $categoryA, $categoryC], inOrder: true);

        $livewire = Livewire::test(ListCategories::class);
        $livewire->sortTable('stories_count', 'desc');
        $livewire->assertCanSeeTableRecords([$categoryC, $categoryA, $categoryB], inOrder: true);
    }

    public function test_category_can_be_created_with_name_in_only_one_locale(): void
    {
        $this->actingAs($this->adminUser);

        $livewire = Livewire::test(CreateCategory::class);
        $livewire->fillForm([
            'name' => [
                'en' => 'Single Locale Category',
                'id' => null,
            ],
            'description' => [
                'en' => 'Description for single locale',
                'id' => null,
            ],
        ]);
        $livewire->call('create');
        $livewire->assertHasNoFormErrors();

        $this->assertDatabaseHas('categories', [
            'name' => json_encode([
                'en' => 'Single Locale Category',
                'id' => null,
            ]),
            'description' => '{"en":"<p>Description for single locale</p>","id":null}',
        ]);
    }
}
