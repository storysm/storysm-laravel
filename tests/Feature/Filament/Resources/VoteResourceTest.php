<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\VoteResource;
use App\Filament\Resources\VoteResource\Pages\ListVotes;
use App\Models\Permission;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use Tests\TestCase;

class VoteResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_vote_resource_index_page_renders(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(VoteResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_users_without_view_all_permission_only_see_their_own_votes_on_index(): void
    {
        // Create users
        /** @var User */
        $user1 = User::factory()->create();
        /** @var User */
        $user2 = User::factory()->create();

        // Create votes
        $vote1_user1 = Vote::factory()->create(['creator_id' => $user1->id]);
        $vote2_user1 = Vote::factory()->create(['creator_id' => $user1->id]);
        $vote1_user2 = Vote::factory()->create(['creator_id' => $user2->id]);
        $vote2_user2 = Vote::factory()->create(['creator_id' => $user2->id]);

        // Act as user1
        $this->actingAs($user1);

        $testable = Livewire::test(ListVotes::class);

        /** @var ListVotes */
        $instance = $testable->instance();

        // Get the table instance and its records
        $table = $instance->getTable();
        /** @var Collection<int, Vote> */
        $records = $table->getRecords();

        // Assert that user1 only sees their own votes
        $this->assertEquals(2, $records->count());
        $this->assertTrue($records->contains($vote1_user1));
        $this->assertTrue($records->contains($vote2_user1));
        $this->assertFalse($records->contains($vote1_user2));
        $this->assertFalse($records->contains($vote2_user2));
    }

    public function test_users_with_view_all_permission_see_all_votes_on_index(): void
    {
        // Create users
        /** @var User */
        $user1 = User::factory()->create(); // User with permission
        Permission::firstOrCreate(['name' => 'view_all_vote']);
        $user1->givePermissionTo('view_all_vote');

        /** @var User */
        $user2 = User::factory()->create();

        // Create votes
        $vote1_user1 = Vote::factory()->create(['creator_id' => $user1->id]);
        $vote2_user1 = Vote::factory()->create(['creator_id' => $user1->id]);
        $vote1_user2 = Vote::factory()->create(['creator_id' => $user2->id]);
        $vote2_user2 = Vote::factory()->create(['creator_id' => $user2->id]);

        // Act as user1
        $this->actingAs($user1);

        $testable = Livewire::test(ListVotes::class);

        /** @var ListVotes */
        $instance = $testable->instance();

        // Get the table instance and its records
        $table = $instance->getTable();
        /** @var Collection<int, Vote> */
        $records = $table->getRecords();

        // Assert that user1 sees all votes
        $this->assertCount(4, $records);
        $this->assertTrue($records->contains($vote1_user1));
        $this->assertTrue($records->contains($vote2_user1));
        $this->assertTrue($records->contains($vote1_user2));
        $this->assertTrue($records->contains($vote2_user2));
    }
}
