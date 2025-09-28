<?php

namespace Tests\Unit\Models;

use App\Models\Genre;
use App\Models\Story;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_story_can_have_many_genres(): void
    {
        $story = Story::factory()->create();
        $genre = Genre::factory()->create();

        $story->genres()->attach($genre);

        $this->assertInstanceOf(BelongsToMany::class, $story->genres());
        $this->assertInstanceOf(Genre::class, $story->genres->first());
        $this->assertEquals(1, $story->genres->count());
    }

    public function test_genres_relationship_is_correctly_defined(): void
    {
        $story = new Story;
        $relation = $story->genres();

        $this->assertInstanceOf(BelongsToMany::class, $relation);
        $this->assertEquals('genres', $relation->getRelationName());
        $this->assertEquals('genre_story', $relation->getTable());
        $this->assertEquals('story_id', $relation->getForeignPivotKeyName());
        $this->assertEquals('genre_id', $relation->getRelatedPivotKeyName());
        $this->assertEquals(Genre::class, $relation->getRelated()->getMorphClass());
    }
}
