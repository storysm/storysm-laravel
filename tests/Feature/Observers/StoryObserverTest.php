<?php

namespace Tests\Feature\Observers;

use App\Models\Language;
use App\Models\Story;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoryObserverTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the StoryObserver synchronizes languages correctly.
     */
    public function test_story_observer_synchronizes_languages(): void
    {
        // Explicitly create languages
        Language::factory()->create(['code' => 'en', 'name' => 'English']);
        Language::factory()->create(['code' => 'fr', 'name' => 'French']);
        Language::factory()->create(['code' => 'es', 'name' => 'Spanish']);

        // Create a story
        $story = Story::factory()->create(['content' => ['en' => 'This is some content.']]);

        // Update the story content with language codes
        $story->update(['content' => [
            'en' => 'This is some content with [en]English[/en] and [fr]French[/fr] languages.',
            'fr' => 'Ceci est un contenu avec les langues [en]anglais[/en] et [fr]français[/fr].',
        ]]);

        // Assert that the languages are synchronized
        $this->assertCount(2, $story->languages);
        $this->assertTrue($story->languages->contains('code', 'en'));
        $this->assertTrue($story->languages->contains('code', 'fr'));

        // Update the story content with a new language and remove an old one
        $story->update(['content' => [
            'en' => 'This is some content with [es]Spanish[/es] and [en]English[/en] languages.',
            'es' => 'Este es un contenido con los idiomas [es]español[/es] y [en]inglés[/en].',
            'fr' => '',
        ]]);

        // Assert that the languages are synchronized
        $story->refresh(); // Refresh the story to get the updated languages
        $this->assertCount(2, $story->languages);
        $this->assertTrue($story->languages->contains('code', 'en'));
        $this->assertTrue($story->languages->contains('code', 'es'));
        $this->assertFalse($story->languages->contains('code', 'fr'));
    }

    /**
     * Test that the StoryObserver handles unknown language codes gracefully.
     */
    public function test_story_observer_handles_unknown_language_codes(): void
    {
        // Create a story
        $story = Story::factory()->create(['content' => ['en' => 'This is some content.']]);

        // Update the story content with an unknown language code
        $story->update(['content' => ['en' => 'This is some content with [xx]Unknown[/xx] language.']]);

        // Assert that no languages are synchronized (or only known ones if any)
        $this->assertCount(0, $story->languages); // Assuming 'xx' is not a known language

        // Assert that the unknown language is not created in the database
        $this->assertDatabaseMissing('languages', ['code' => 'xx']);
    }
}
