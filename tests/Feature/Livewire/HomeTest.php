<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Home;
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
}
