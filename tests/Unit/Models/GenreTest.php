<?php

namespace Tests\Unit\Models;

use App\Models\Genre;
use App\Models\Story;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Tests\TestCase;

class GenreTest extends TestCase
{
    /**
     * Test that the genre has many stories.
     *
     * @return void
     */
    public function test_genre_has_many_stories()
    {
        $genre = new Genre;
        $this->assertInstanceOf(BelongsToMany::class, $genre->stories());
        $this->assertInstanceOf(Story::class, $genre->stories()->getRelated());
        $this->assertEquals('genre_story', $genre->stories()->getTable());
        $this->assertEquals('genre_id', $genre->stories()->getForeignPivotKeyName());
        $this->assertEquals('story_id', $genre->stories()->getRelatedPivotKeyName());
    }
}
