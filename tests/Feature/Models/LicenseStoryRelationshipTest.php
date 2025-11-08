<?php

namespace Tests\Feature\Models;

use App\Models\License;
use App\Models\Story;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseStoryRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_license_with_linked_stories_throws_query_exception(): void
    {
        $license = License::factory()->create();
        $story = Story::factory()->create();

        // Link the license to the story
        $story->licenses()->attach($license->id);

        // Expect a QueryException when trying to delete the license
        $this->expectException(\Illuminate\Database\QueryException::class);

        $license->delete();
    }
}
