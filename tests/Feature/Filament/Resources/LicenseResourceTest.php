<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\LicenseResource;
use App\Filament\Resources\LicenseResource\Pages\CreateLicense;
use App\Filament\Resources\LicenseResource\Pages\EditLicense;
use App\Filament\Resources\LicenseResource\Pages\ListLicenses;
use App\Models\License;
use App\Models\Permission;
use App\Models\Story;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class LicenseResourceTest extends TestCase
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

        // Ensure permissions exist for License resource
        $permissions = collect(LicenseResource::getPermissionPrefixes())
            ->map(fn ($prefix) => $prefix.'_license')
            ->toArray();

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign all license permissions to adminUser
        $this->adminUser->givePermissionTo($permissions);

        // Set the Filament panel context
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    // --- Policy Enforcement Tests (Non-Admin Users) ---

    public function test_unauthorized_user_cannot_access_license_list_page(): void
    {
        $this->actingAs($this->unauthorizedUser)
            ->get(LicenseResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_unauthorized_user_cannot_access_license_create_page(): void
    {
        $this->actingAs($this->unauthorizedUser)
            ->get(LicenseResource::getUrl('create'))
            ->assertForbidden();
    }

    public function test_unauthorized_user_cannot_access_license_edit_page(): void
    {
        $license = License::factory()->create();

        $this->actingAs($this->unauthorizedUser)
            ->get(LicenseResource::getUrl('edit', ['record' => $license]))
            ->assertForbidden();
    }

    // --- CRUD Tests (Admin Users) ---

    public function test_admin_can_view_license_list(): void
    {
        $licenses = License::factory(3)->create();

        $this->actingAs($this->adminUser);
        $livewire = Livewire::test(ListLicenses::class);
        $livewire->assertCanSeeTableRecords($licenses);
        $livewire->assertSuccessful();
    }

    public function test_admin_can_create_a_license(): void
    {
        $this->actingAs($this->adminUser);

        $data = [
            'name' => [
                'en' => 'Test License EN '.Str::random(5), // Add random to ensure UniqueJsonTranslation doesn't fail
                'id' => 'Lisensi Uji ID '.Str::random(5),
            ],
            'description' => [
                'en' => '<p>Test Description EN</p>',
                'id' => '<p>Deskripsi Uji ID</p>',
            ],
        ];

        $this->actingAs($this->adminUser);
        $livewire = Livewire::test(CreateLicense::class);
        $livewire->fillForm($data);
        $livewire->call('create');
        $livewire->assertHasNoFormErrors();

        $this->assertDatabaseHas('licenses', [
            'name' => json_encode($data['name'], JSON_UNESCAPED_SLASHES),
            'description' => json_encode($data['description'], JSON_UNESCAPED_SLASHES),
        ]);
    }

    public function test_admin_can_edit_a_license(): void
    {
        $license = License::factory()->create([
            'name' => ['en' => 'Old Name', 'id' => 'Nama Lama'],
            'description' => [
                'en' => '<p>Description EN</p>',
                'id' => '<p>Deskripsi ID</p>',
            ],
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
        ];

        $this->actingAs($this->adminUser);
        $livewire = Livewire::test(EditLicense::class, ['record' => $license->id]);
        $livewire->fillForm($newData);
        $livewire->call('save');
        $livewire->assertHasNoFormErrors();

        $this->assertDatabaseHas('licenses', [
            'id' => $license->id,
            'name' => json_encode($newData['name'], JSON_UNESCAPED_SLASHES),
            'description' => json_encode($newData['description'], JSON_UNESCAPED_SLASHES),
        ]);
    }

    // --- Deletion and Reference Check Tests ---

    public function test_admin_can_delete_an_unreferenced_license(): void
    {
        $license = License::factory()->create();

        $this->actingAs($this->adminUser);
        $livewire = Livewire::test(EditLicense::class, ['record' => $license->id]);
        $livewire->callAction(DeleteAction::class);
        $livewire->assertSuccessful();

        $this->assertModelMissing($license);
    }

    public function test_delete_action_is_hidden_for_referenced_license(): void
    {
        $license = License::factory()->create();
        // Create a story and attach the license, making it 'referenced'
        Story::factory()->hasAttached($license, [], 'licenses')->create();

        // Check on the List page (Table Action)
        $this->actingAs($this->adminUser);
        $livewire = Livewire::test(ListLicenses::class);
        // Assert that the delete action is hidden for the specific record
        $livewire->assertTableActionHidden('delete', $license);

        // Check on the Edit page (Header Action)
        $livewire = Livewire::test(EditLicense::class, ['record' => $license->id]);
        $livewire->assertActionHidden('delete');
    }

    public function test_admin_can_bulk_delete_unreferenced_licenses(): void
    {
        $licenses = License::factory(3)->create();

        $this->actingAs($this->adminUser);
        $livewire = Livewire::test(ListLicenses::class);
        $livewire->callTableBulkAction(DeleteBulkAction::class, $licenses);

        foreach ($licenses as $license) {
            $this->assertModelMissing($license);
        }
    }

    public function test_admin_bulk_deletion_protects_referenced_licenses(): void
    {
        // One referenced, two unreferenced
        $referencedLicense = License::factory()->create();
        $unreferencedLicenses = License::factory(2)->create();
        Story::factory()->hasAttached($referencedLicense, [], 'licenses')->create();

        $allLicenses = collect([$referencedLicense])->merge($unreferencedLicenses);

        $this->actingAs($this->adminUser);
        $livewire = Livewire::test(ListLicenses::class);
        // Call bulk action with all three licenses
        $livewire->callTableBulkAction('delete', $allLicenses->pluck('id')->toArray());

        // Assert that the referenced license is protected and still exists
        $this->assertModelExists($referencedLicense);

        // Assert that the unreferenced licenses were successfully deleted
        foreach ($unreferencedLicenses as $license) {
            $this->assertDatabaseMissing('licenses', ['id' => $license->id]);
        }
    }
}
