<?php

namespace Tests\Unit\Models;

use App\Models\License;
use App\Models\Story;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoryLicenseRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_license_can_be_assigned_to_a_story(): void
    {
        $story = Story::factory()->create();
        $license = License::factory()->create();

        // Attach the license to the story
        $story->licenses()->attach($license->id);

        // Assert that the license is associated with the story
        $this->assertCount(1, $story->licenses);
        $this->assertTrue($story->licenses->contains($license));
        $this->assertEquals($story->id, $license->stories->first()?->id);
    }

    public function test_a_story_can_have_multiple_licenses(): void
    {
        $story = Story::factory()->create();
        $licenses = License::factory()->count(3)->create();

        // Sync the licenses to the story
        $story->licenses()->sync($licenses->pluck('id'));

        // Assert the count
        $this->assertCount(3, $story->licenses);
    }

    public function test_deleting_a_story_detaches_the_licenses(): void
    {
        $story = Story::factory()->create();
        $license = License::factory()->create();
        $story->licenses()->attach($license->id);

        // Assert the pivot table has an entry
        $this->assertDatabaseHas('license_story', [
            'story_id' => $story->id,
            'license_id' => $license->id,
        ]);

        // Delete the story
        $story->delete();

        // Assert the license still exists, but the pivot entry is gone
        $this->assertDatabaseMissing('license_story', [
            'story_id' => $story->id,
            'license_id' => $license->id,
        ]);
        $this->assertDatabaseHas('licenses', ['id' => $license->id]);
    }

    public function test_deleting_a_license_with_linked_stories_throws_query_exception_instead_of_detaching(): void
    {
        $story = Story::factory()->create();
        $license = License::factory()->create();
        $story->licenses()->attach($license->id);

        // Assert the pivot table has an entry
        $this->assertDatabaseHas('license_story', [
            'story_id' => $story->id,
            'license_id' => $license->id,
        ]);

        // Expect a QueryException because of restrictOnDelete
        $this->expectException(\Illuminate\Database\QueryException::class);

        // Delete the license
        $license->delete();
    }
}
