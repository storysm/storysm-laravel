<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Story;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryStoryRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_story_can_have_multiple_categories(): void
    {
        $story = Story::factory()->create();
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        $story->categories()->attach([$category1->id, $category2->id]);

        $this->assertCount(2, $story->categories);
        $this->assertTrue($story->categories->contains($category1));
        $this->assertTrue($story->categories->contains($category2));
    }

    public function test_a_category_can_have_multiple_stories(): void
    {
        $category = Category::factory()->create();
        $story1 = Story::factory()->create();
        $story2 = Story::factory()->create();

        $category->stories()->attach([$story1->id, $story2->id]);

        $this->assertCount(2, $category->stories);
        $this->assertTrue($category->stories->contains($story1));
        $this->assertTrue($category->stories->contains($story2));
    }

    public function test_categories_can_be_detached_from_a_story(): void
    {
        $story = Story::factory()->create();
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        $story->categories()->attach([$category1->id, $category2->id]);
        $this->assertCount(2, $story->categories);

        $story->categories()->detach($category1->id);
        $story->refresh();

        $this->assertCount(1, $story->categories);
        $this->assertFalse($story->categories->contains($category1));
        $this->assertTrue($story->categories->contains($category2));
    }

    public function test_detaching_a_story_does_not_delete_the_category(): void
    {
        $story = Story::factory()->create();
        $category = Category::factory()->create();

        $story->categories()->attach($category->id);
        $story->categories()->detach($category->id);

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_deleting_a_story_detaches_its_categories(): void
    {
        $story = Story::factory()->create();
        $category = Category::factory()->create();

        $story->categories()->attach($category->id);
        $this->assertCount(1, $story->categories);

        $story->delete();

        $this->assertDatabaseMissing('category_story', [
            'story_id' => $story->id,
            'category_id' => $category->id,
        ]);
        $this->assertDatabaseHas('categories', ['id' => $category->id]); // Category should not be deleted
    }

    public function test_is_referenced_method_returns_correct_value(): void
    {
        $category = Category::factory()->create();
        $this->assertFalse($category->isReferenced());

        $story = Story::factory()->create();
        $category->stories()->attach($story->id);
        $category->refresh();

        $this->assertTrue($category->isReferenced());

        $category->stories()->detach($story->id);
        $category->refresh();

        $this->assertFalse($category->isReferenced());
    }

    public function test_deleting_category_with_linked_stories_throws_query_exception(): void
    {
        $category = Category::factory()->create();
        $story = Story::factory()->create();

        // Link the category to the story
        $story->categories()->attach($category->id);

        // Expect a QueryException when trying to delete the category
        $this->expectException(\Illuminate\Database\QueryException::class);

        $category->delete();
    }
}
