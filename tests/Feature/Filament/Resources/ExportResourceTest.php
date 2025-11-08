<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ExportResource;
use App\Filament\Resources\ExportResource\Pages\ListExports;
use App\Models\Export;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ExportResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        Permission::firstOrCreate(['name' => 'view_any_export']);
        $this->user->givePermissionTo('view_any_export');
    }

    public function test_export_table_can_be_rendered(): void
    {
        $this->get(ExportResource::getUrl('index'))->assertSuccessful();
    }

    public function test_columns_are_displayed(): void
    {
        Export::factory()->create([
            'user_id' => $this->user->id,
            'exporter' => 'App\\Exporters\\PageExporter',
            'file_name' => 'test.csv',
            'file_disk' => 'local',
            'total_rows' => 100,
            'processed_rows' => 50,
            'successful_rows' => 50,
            'completed_at' => now(),
        ]);

        $this->get(ExportResource::getUrl('index'))
            ->assertSee('Page') // From exporter column
            ->assertSee('local')
            ->assertSee('100')
            ->assertSee('50');
    }

    public function test_download_actions_are_present(): void
    {
        $export = Export::factory()->create([
            'user_id' => $this->user->id,
            'file_name' => 'test.csv',
            'file_disk' => 'local',
        ]);

        $this->get(ExportResource::getUrl('index'))
            ->assertSee(__('export.resource.download_name', ['name' => 'CSV']))
            ->assertSee(__('export.resource.download_name', ['name' => 'XLSX']));
    }

    public function test_get_eloquent_query_filters_results_for_non_admin_users(): void
    {
        // Create an export with the current user as the user_id
        Export::factory()->create(['user_id' => $this->user->id, 'creator_id' => User::factory()->create()->id]);
        // Create an export with the current user as the creator_id
        Export::factory()->create(['user_id' => User::factory()->create()->id, 'creator_id' => $this->user->id]);
        // Create an export that should not be included in the results
        Export::factory()->create(['user_id' => User::factory()->create()->id, 'creator_id' => User::factory()->create()->id]);

        $exports = ExportResource::getEloquentQuery()->get();

        $this->assertCount(2, $exports);

        $this->assertTrue($exports->contains(function ($export) {
            return $export->user_id === $this->user->id;
        }));

        $this->assertTrue($exports->contains(function ($export) {
            return $export->creator_id === $this->user->id;
        }));
    }

    public function test_get_eloquent_query_does_not_filter_results_for_view_all_users(): void
    {
        // Assign 'view_all_export' permission to the user
        Permission::firstOrCreate(['name' => 'view_all_export']);
        $this->user->givePermissionTo('view_all_export');

        Export::factory()->create(['user_id' => $this->user->id]);
        Export::factory()->create(['user_id' => User::factory()->create()->id]); // Another user's export

        $exports = ExportResource::getEloquentQuery()->get();

        $this->assertCount(2, $exports);
    }

    public function test_user_name_column_visibility_for_view_all_users(): void
    {
        // Assign 'view_all_export' permission to the user
        Permission::firstOrCreate(['name' => 'view_all_export']);
        $this->user->givePermissionTo('view_all_export');

        Export::factory()->create(['user_id' => $this->user->id]);

        Livewire::test(ListExports::class)
            ->assertTableColumnVisible('user.name');
    }

    public function test_user_name_column_hidden_for_non_view_all_users(): void
    {
        // Ensure the user does NOT have 'view_all_export' permission
        // No permission assignment needed here as it's the default state

        Export::factory()->create(['user_id' => $this->user->id]);

        Livewire::test(ListExports::class)
            ->assertTableColumnHidden('user.name');
    }

    public function test_correct_records_are_listed(): void
    {
        // Arrange
        $userA = $this->user;
        $userB = User::factory()->create();
        $userC = User::factory()->create();

        Export::factory()->create([
            'user_id' => $userA->id,
            'creator_id' => $userA->id,
        ]);

        Export::factory()->create([
            'user_id' => $userB->id,
            'creator_id' => $userA->id,
        ]);

        $this->actingAs($userB);

        Export::factory()->create([
            'user_id' => $userC->id,
            'creator_id' => $userB->id,
        ]);

        $this->actingAs($userA);

        // Act & Assert
        $exportsUserA = Export::where(function ($query) use ($userA) {
            $query->where('user_id', $userA->id)
                ->orWhere('creator_id', $userA->id);
        })->get();

        $exportsNotUserA = Export::where(function ($query) use ($userA) {
            $query->where('user_id', '!=', $userA->id)
                ->where('creator_id', '!=', $userA->id);
        })->get();

        $livewire = Livewire::test(ExportResource\Pages\ListExports::class);
        $livewire->assertCanSeeTableRecords($exportsUserA);
        $livewire->assertCanNotSeeTableRecords($exportsNotUserA);
    }
}
