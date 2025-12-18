<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Home;
use App\Models\Story;
use App\Models\User;
use Filament\Tables;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_renders_successfully(): void
    {
        $this->get('/')
            ->assertStatus(200);
        Livewire::test(Home::class)
            ->assertStatus(200);
    }

    public function test_table_is_configured_correctly(): void
    {
        /** @var Testable */
        $testable = Livewire::test(Home::class);
        $testable->assertOk();

        /** @var Home */
        $instance = $testable->instance();

        $table = $instance->getTable();
        $this->assertFalse($table->isPaginated());
        $this->assertFalse($table->isSearchable());

        $columns = $table->getColumns();
        collect($columns)->each(function ($column) {
            $this->assertFalse($column->isSortable());
        });
    }

    public function test_view_all_stories_action_has_correct_properties(): void
    {
        /** @var Testable */
        $testable = Livewire::test(Home::class);
        $testable->assertOk();

        /** @var Home */
        $instance = $testable->instance();

        $table = $instance->getTable();
        $headerActions = collect($table->getHeaderActions());
        $action = $headerActions->filter(function ($action) {
            return $action->getLabel() === __('story.table.view_all');
        })->first();

        $this->assertNotNull($action, "The 'view_all_stories' header action does not exist.");
        $this->assertTrue($action instanceof Tables\Actions\Action);
        $this->assertEquals(__('story.table.view_all'), $action->getLabel());
        $this->assertEquals('heroicon-o-arrow-right', $action->getIcon());
        $this->assertEquals(\Filament\Support\Enums\IconPosition::After, $action->getIconPosition());
        $this->assertEquals(route('stories.index'), $action->getUrl());
    }

    public function test_home_table_limits_records_to_twelve(): void
    {
        // Create a user for story ownership
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create 20 stories in the database
        Story::factory()->count(20)->ensurePublished()->create([
            'creator_id' => $user->id,
        ]);

        // Get the table data by accessing the component directly
        /** @var Home */
        $component = Livewire::test(Home::class)->instance();
        $table = $component->getTable();

        // Get the query builder from the table
        $query = $table->getQuery();

        // Execute the query and count the results
        $results = $query->get();
        $resultCount = $results->count();

        // Assert that we get exactly 12 records (the limit)
        $this->assertEquals(12, $resultCount, 'Home table should limit results to exactly 12 records');

        // Additional assertion: ensure we don't get all 20 records
        $this->assertLessThan(20, $resultCount, 'Home table should not return all 20 records');
    }

    public function test_home_table_with_less_than_twelve_records(): void
    {
        // Create a user for story ownership
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create only 5 stories in the database
        Story::factory()->count(5)->ensurePublished()->create([
            'creator_id' => $user->id,
        ]);

        // Get the table data by accessing the component directly
        /** @var Home */
        $component = Livewire::test(Home::class)->instance();
        $table = $component->getTable();

        // Get the query builder from the table
        $query = $table->getQuery();

        // Execute the query and count the results
        $results = $query->get();
        $resultCount = $results->count();

        // Assert that we get all 5 records (since there are fewer than the limit)
        $this->assertEquals(5, $resultCount, 'Home table should return all records when less than limit');
    }
}
