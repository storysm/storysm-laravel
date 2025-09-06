<?php

namespace Tests\Feature\Observers;

use App\Models\Language;
use App\Models\Story;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use Mockery\Expectation;
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
        $log = Log::spy();

        // Explicitly create 'en' language  to ensure it's known
        Language::factory()->create(['code' => 'en', 'name' => 'English']);

        // Create a story
        $story = Story::factory()->create(['content' => [
            'en' => 'This is some content.',
            'xx' => '???',
        ]]);

        // Update the story content with an unknown language code
        $story->update(['content' => ['en' => 'This is some content with [xx]Unknown[/xx] language.']]);

        // Assert that 'en' language is  synchronized and 'xx' is not
        $this->assertCount(1, $story->languages);
        $this->assertTrue($story->languages->contains('code', 'en'));
        $this->assertFalse($story->languages->contains('code', 'xx'));

        // Assert that the unknown language is not created in the database
        $this->assertDatabaseMissing('languages', ['code' => 'xx']);

        /** @var Expectation */
        $expectation = $log->shouldReceive('warning');
        $expectation->with(Mockery::on(function (string $message) {
            return str_contains($message, 'Attempted to sync story with unknown language codes');
        }));
    }

    /**
     * Test that the StoryObserver synchronizes languages correctly on story creation.
     */
    public function test_story_observer_synchronizes_languages_on_creation(): void
    {
        // Explicitly create languages
        Language::factory()->create(['code' => 'en', 'name' => 'English']);
        Language::factory()->create(['code' => 'fr', 'name' => 'French']);

        // Create a story with content containing language codes
        $story = Story::factory()->create(['content' => [
            'en' => 'This is some content with [en]English[/en] and [fr]French[/fr] languages.',
            'fr' => 'Ceci est un contenu avec les langues [en]anglais[/en] et [fr]français[/fr].',
        ]]);

        // Assert that the languages are synchronized immediately upon creation
        $this->assertCount(2, $story->languages);
        $this->assertTrue($story->languages->contains('code', 'en'));
        $this->assertTrue($story->languages->contains('code', 'fr'));
    }

    /**
     * Test that the StoryObserver does not synchronize languages if content is not dirty.
     */
    public function test_story_observer_does_not_sync_languages_if_content_not_dirty(): void
    {
        // Create a story
        $story = Story::factory()->create(['content' => ['en' => 'Initial content.']]);

        // Mock the languages relationship to ensure sync() is not called
        $mockRelation = $this->createMock(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class);
        $mockRelation->expects($this->never())
            ->method('sync');

        $story->setRelation('languages', $mockRelation);

        // Update a different field (e.g., title) without changing content
        $story->update(['title' => 'New Title']);

        // Assert that the sync method was not called
        // (This is implicitly covered by the mock expectation)
        $this->assertFalse($story->isDirty('content'));
    }
}
