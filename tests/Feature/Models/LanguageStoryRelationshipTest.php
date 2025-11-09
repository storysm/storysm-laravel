<?php

namespace Tests\Feature\Models;

use App\Models\Language;
use App\Models\Story;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageStoryRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_language_with_linked_stories_throws_query_exception(): void
    {
        $language = Language::factory()->create();
        $story = Story::factory()->create();

        // Link the language to the story
        $story->languages()->attach($language->id);

        // Expect a QueryException when trying to delete the language
        $this->expectException(\Illuminate\Database\QueryException::class);

        $language->delete();
    }
}
