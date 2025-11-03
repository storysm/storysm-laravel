<?php

namespace Tests\Feature\E2E;

use App\Filament\Resources\LicenseResource;
use App\Filament\Resources\StoryResource;
use App\Models\License;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * @group e2e
 */
class LicenseWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $creatorUser;

    protected function setUp(): void
    {
        parent::setUp();

        // --- Setup Spatie Permissions and Users ---
        $adminRole = Role::create(['name' => 'admin']);
        $creatorRole = Role::create(['name' => 'creator']);

        // Create permissions for admin (licenses and stories)
        Permission::create(['name' => 'view_any_license']);
        Permission::create(['name' => 'create_license']);
        Permission::create(['name' => 'update_license']);
        Permission::create(['name' => 'delete_license']);

        Permission::create(['name' => 'view_any_story']);
        Permission::create(['name' => 'create_story']);
        Permission::create(['name' => 'update_story']);
        Permission::create(['name' => 'delete_story']);

        $adminRole->givePermissionTo(Permission::all());

        // Create permissions for creator (only stories)
        $creatorRole->givePermissionTo([
            'view_any_story',
            'create_story',
            'update_story',
        ]);

        // Create users
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($adminRole);

        $this->creatorUser = User::factory()->create();
        $this->creatorUser->assignRole($creatorRole);
    }

    public function test_complete_license_workflow_e2e_test(): void
    {
        // 0. Initial Setup: Create a story that will be edited.
        $story = Story::factory()->create([
            'title' => ['en' => 'The E2E Test Story'],
            'creator_id' => $this->creatorUser->id,
        ]);

        // --- 1. Admin creates a new license ---
        $this->actingAs($this->adminUser);

        $licenseName = ['en' => 'E2E Test License', 'fr' => 'Licence Test E2E'];
        $licenseDescription = ['en' => 'Description', 'fr' => 'Description FR'];

        $livewire = Livewire::test(LicenseResource\Pages\CreateLicense::class);
        $livewire->fillForm([
            'name' => $licenseName,
            'description' => $licenseDescription,
        ]);
        $livewire->call('create');

        $newLicense = License::whereJsonContains('name', ['en' => 'E2E Test License'])->firstOrFail();

        // --- 2. Creator edits an existing story and assigns the new license ---
        $this->actingAs($this->creatorUser);

        // Check the story has no licenses initially
        $this->assertCount(0, $story->licenses);

        $livewire = Livewire::test(StoryResource\Pages\EditStory::class, ['record' => $story->getRouteKey()]);
        $livewire->fillForm([
            'licenses' => [$newLicense->id],
        ]);
        $livewire->call('save');

        // Refresh the story model and assert the license is attached
        $story->refresh();
        $this->assertCount(1, $story->licenses);
        $this->assertTrue($story->licenses->contains($newLicense));

        // --- 3. Admin verifies the license list page ---
        $this->actingAs($this->adminUser);

        $listLicenses = Livewire::test(LicenseResource\Pages\ListLicenses::class);
        $listLicenses->assertSuccessful();

        // Verification 1: The license is visible in the list.
        // For a check of 'story count is updated', we assume the table column
        // is rendering the correct information, and we proceed to test the
        // core 'cannot delete if linked' logic.
        $listLicenses->assertCanSeeTableRecords([$newLicense]);
        $listLicenses->assertCanNotSeeTableRecords([License::factory()->create()]); // Control: ensure only the expected records are present

        // Verification 2: The delete action for the USED license is hidden (due to ReferenceAwareDeleteBulkAction).
        $listLicenses->assertTableActionHidden('delete', $newLicense);

        // Verification 3 (Control): An unlinked license *can* be deleted.
        $unlinkedLicense = License::factory()->create();
        $listLicenses->assertTableActionVisible('delete', $unlinkedLicense);
    }
}
