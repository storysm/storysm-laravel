<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\LanguageResource;
use App\Filament\Resources\LanguageResource\Pages\ListLanguages;
use App\Models\Language;
use App\Models\Permission;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LanguageResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $unauthorizedUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->create();
        $this->unauthorizedUser = User::factory()->create();

        // Ensure permissions exist for Language resource
        foreach (LanguageResource::getPermissionPrefixes() as $prefix) {
            Permission::firstOrCreate(['name' => $prefix.'_language']);
        }

        // Assign all language permissions to adminUser
        $this->adminUser->givePermissionTo(collect(LanguageResource::getPermissionPrefixes())->map(fn ($prefix) => $prefix.'_language')->toArray());

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_admin_user_can_view_list_of_languages(): void
    {
        $this->actingAs($this->adminUser);
        Language::factory()->count(3)->create();

        Livewire::test(ListLanguages::class)
            ->assertCanSeeTableRecords(Language::all());
    }

    public function test_unauthorized_user_cannot_view_list_of_languages(): void
    {
        $this->actingAs($this->unauthorizedUser);
        Language::factory()->count(3)->create();

        $this->get(LanguageResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_admin_user_can_create_language(): void
    {
        $this->actingAs($this->adminUser);

        $livewire = Livewire::test(LanguageResource\Pages\CreateLanguage::class);
        $livewire->fillForm([
            'code' => 'es',
            'name' => 'Spanish',
        ]);
        $livewire->call('create');
        $livewire->assertHasNoFormErrors();

        $this->assertDatabaseHas('languages', [
            'code' => 'es',
            'name' => 'Spanish',
        ]);
    }

    public function test_unauthorized_user_cannot_create_language(): void
    {
        $this->actingAs($this->unauthorizedUser);

        $this->get(LanguageResource::getUrl('create'))
            ->assertForbidden();
    }

    public function test_language_creation_requires_code_and_name(): void
    {
        $this->actingAs($this->adminUser);

        $livewire = Livewire::test(LanguageResource\Pages\CreateLanguage::class);
        $livewire->fillForm([
            'code' => '',
            'name' => '',
        ]);
        $livewire->call('create');
        $livewire->assertHasFormErrors([
            'code' => 'required',
            'name' => 'required',
        ]);
    }

    public function test_language_code_must_be_unique(): void
    {
        $this->actingAs($this->adminUser);

        Language::factory()->create(['code' => 'en']);

        $livewire = Livewire::test(LanguageResource\Pages\CreateLanguage::class);
        $livewire->fillForm([
            'code' => 'en',
            'name' => 'English Duplicate',
        ]);
        $livewire->call('create');
        $livewire->assertHasFormErrors([
            'code' => 'unique',
        ]);
    }

    public function test_admin_user_can_update_language(): void
    {
        $this->actingAs($this->adminUser);
        $language = Language::factory()->create(['code' => 'en', 'name' => 'English']);

        $livewire = Livewire::test(LanguageResource\Pages\EditLanguage::class, ['record' => $language->getRouteKey()]);
        $livewire->fillForm([
            'code' => 'en-us',
            'name' => 'American English',
        ]);
        $livewire->call('save');
        $livewire->assertHasNoFormErrors();

        $this->assertDatabaseHas('languages', [
            'id' => $language->id,
            'code' => 'en-us',
            'name' => 'American English',
        ]);
    }

    public function test_unauthorized_user_cannot_update_language(): void
    {
        $this->actingAs($this->unauthorizedUser);
        $language = Language::factory()->create(['code' => 'de', 'name' => 'German']);

        $this->get(LanguageResource::getUrl('edit', ['record' => $language->getRouteKey()]))
            ->assertForbidden();

        $this->assertDatabaseHas('languages', [
            'id' => $language->id,
            'code' => 'de',
            'name' => 'German',
        ]);
    }

    public function test_language_update_requires_code_and_name(): void
    {
        $this->actingAs($this->adminUser);
        $language = Language::factory()->create(['code' => 'fr', 'name' => 'French']);

        $livewire = Livewire::test(LanguageResource\Pages\EditLanguage::class, ['record' => $language->getRouteKey()]);
        $livewire->fillForm([
            'code' => '',
            'name' => '',
        ]);
        $livewire->call('save');
        $livewire->assertHasFormErrors([
            'code' => 'required',
            'name' => 'required',
        ]);
    }

    public function test_language_code_update_must_be_unique_ignoring_self(): void
    {
        $this->actingAs($this->adminUser);
        Language::factory()->create(['code' => 'es', 'name' => 'Spanish']);
        $languageToUpdate = Language::factory()->create(['code' => 'it', 'name' => 'Italian']);

        $livewire = Livewire::test(LanguageResource\Pages\EditLanguage::class, ['record' => $languageToUpdate->getRouteKey()]);
        $livewire->fillForm([
            'code' => 'es',
            'name' => 'Italian Updated',
        ]);
        $livewire->call('save');
        $livewire->assertHasFormErrors([
            'code' => 'unique',
        ]);

        // Ensure it can save with its own code
        $livewire = Livewire::test(LanguageResource\Pages\EditLanguage::class, ['record' => $languageToUpdate->getRouteKey()]);
        $livewire->fillForm([
            'code' => 'it',
            'name' => 'Italian Updated',
        ]);
        $livewire->call('save');
        $livewire->assertHasNoFormErrors();
    }

    public function test_admin_user_can_delete_language(): void
    {
        $this->actingAs($this->adminUser);
        $language = Language::factory()->create();

        Livewire::test(LanguageResource\Pages\EditLanguage::class, ['record' => $language->getRouteKey()])
            ->call('mountAction', 'delete')
            ->call('callMountedAction');

        $this->assertDatabaseMissing('languages', [
            'id' => $language->id,
        ]);
    }

    public function test_admin_user_can_bulk_delete_languages(): void
    {
        $this->actingAs($this->adminUser);
        $languages = Language::factory()->count(3)->create();

        Livewire::test(LanguageResource\Pages\ListLanguages::class)
            ->callTableBulkAction('delete', $languages);

        foreach ($languages as $language) {
            $this->assertDatabaseMissing('languages', [
                'id' => $language->id,
            ]);
        }
    }
}
