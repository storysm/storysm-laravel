<?php

namespace Tests\Feature\Models;

use App\Models\Comment;
use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_comment_can_be_created(): void
    {
        // Arrange: Create a user and a story
        $user = User::factory()->create();
        $story = Story::factory()->create();

        // Act: Create a comment
        $comment = Comment::create([
            'creator_id' => $user->id,
            'story_id' => $story->id,
            'body' => ['en' => 'This is a test comment.', 'id' => 'Ini adalah komentar uji.'],
        ]);

        // Assert: Check if the comment exists in the database
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'creator_id' => $user->id,
            'story_id' => $story->id,
        ]);

        // Assert: Check the body content (Spatie handles JSON)
        $this->assertEquals('This is a test comment.', $comment->getTranslation('body', 'en'));
        $this->assertEquals('Ini adalah komentar uji.', $comment->getTranslation('body', 'id'));
    }

    public function test_comment_belongs_to_a_creator(): void
    {
        // Arrange: Create a comment using the factory
        $comment = Comment::factory()->create();

        // Act: Retrieve the creator via the relationship
        $creator = $comment->creator;

        // Assert: Check if the creator is a User instance and matches the comment's creator_id
        $this->assertInstanceOf(User::class, $creator);
        $this->assertEquals($comment->creator_id, $creator->id);
    }

    public function test_comment_belongs_to_a_story(): void
    {
        // Arrange: Create a comment using the factory
        $comment = Comment::factory()->create();

        // Act: Retrieve the story via the relationship
        $story = $comment->story;

        // Assert: Check if the story is a Story instance and matches the comment's story_id
        $this->assertInstanceOf(Story::class, $story);
        $this->assertEquals($comment->story_id, $story->id);
    }

    public function test_user_can_have_many_comments(): void
    {
        // Arrange: Create a user and multiple comments for that user
        /** @var User */
        $user = User::factory()->create();
        Comment::factory()->count(3)->create(['creator_id' => $user->id]);

        // Act: Retrieve the user's comments via the relationship
        $userComments = $user->comments;

        // Assert: Check if the relationship returns a collection of comments
        $this->assertCount(3, $userComments);
        $this->assertTrue($userComments->every(fn ($comment) => $comment->creator->is($user)));
    }

    public function test_story_can_have_many_comments(): void
    {
        // Arrange: Create a story and multiple comments for that story
        $story = Story::factory()->create();
        Comment::factory()->count(5)->create(['story_id' => $story->id]);

        // Act: Retrieve the story's comments via the relationship
        $storyComments = $story->comments;

        // Assert: Check if the relationship returns a collection of comments
        $this->assertCount(5, $storyComments);
        $this->assertTrue($storyComments->every(fn ($comment) => $comment->story_id === $story->id));
    }

    public function test_comment_can_have_a_parent(): void
    {
        // Arrange: Create a parent comment and a child comment
        $parentComment = Comment::factory()->create();
        $childComment = Comment::factory()->create(['parent_id' => $parentComment->id]);

        // Act: Retrieve the parent via the relationship from the child
        $retrievedParent = $childComment->parent;

        // Assert: Check if the retrieved parent is a Comment instance and matches the parent comment
        $this->assertInstanceOf(Comment::class, $retrievedParent);
        $this->assertEquals($parentComment->id, $retrievedParent->id);
    }

    public function test_comment_can_have_many_children(): void
    {
        // Arrange: Create a parent comment and multiple child comments
        $parentComment = Comment::factory()->create();
        $childComments = Comment::factory()->count(3)->create(['parent_id' => $parentComment->id]);

        // Act: Retrieve the children via the relationship from the parent
        $retrievedChildren = $parentComment->comments;

        // Assert: Check if the relationship returns a collection of comments and containsthe correct children
        $this->assertCount(3, $retrievedChildren);
        $this->assertTrue($retrievedChildren->every(fn ($comment) => $comment->parent_id === $parentComment->id));
        $childComments->each(fn ($child) => $this->assertTrue($retrievedChildren->contains($child)));
    }

    public function test_story_comment_count_increments_on_comment_creation(): void
    {
        // Arrange: Create a story
        $story = Story::factory()->create();
        $initialCommentCount = $story->comment_count;

        // Act: Create a comment for the story
        Comment::factory()->create(['story_id' => $story->id]);

        // Assert: Reload the story and check if the comment_count has incremented
        $story->refresh();
        $this->assertEquals($initialCommentCount + 1, $story->comment_count);
    }

    public function test_story_comment_count_decrements_on_comment_deletion(): void
    {
        // Arrange: Create a story and a comment for it
        $story = Story::factory()->create();
        $comment = Comment::factory()->create(['story_id' => $story->id]);

        // Ensure the count is 1 after creation
        $story->refresh();
        $this->assertEquals(1, $story->comment_count);

        // Act: Delete the comment
        $comment->delete();

        // Assert: Reload the story and check if the comment_count has decremented
        $story->refresh();
        $this->assertEquals(0, $story->comment_count);
    }

    public function test_parent_reply_count_increments_on_child_comment_creation(): void
    {
        // Arrange: Create a parent comment
        $parentComment = Comment::factory()->create();
        $this->assertEquals(0, $parentComment->reply_count);

        // Act: Create a child comment for the parent
        Comment::factory()->create(['parent_id' => $parentComment->id]);

        // Assert: Reload the parent and check if reply_count has incremented
        $parentComment->refresh();
        $this->assertEquals(1, $parentComment->reply_count);

        // Create another child
        Comment::factory()->create(['parent_id' => $parentComment->id]);
        $parentComment->refresh();
        $this->assertEquals(2, $parentComment->reply_count);
    }

    public function test_parent_reply_count_decrements_on_child_comment_deletion(): void
    {
        // Arrange: Create a parent comment and two child comments
        $parentComment = Comment::factory()->create();
        $childComment1 = Comment::factory()->create(['parent_id' => $parentComment->id]);
        $childComment2 = Comment::factory()->create(['parent_id' => $parentComment->id]);

        // Ensure the count is 2 after creation
        $parentComment->refresh();
        $this->assertEquals(2, $parentComment->reply_count);

        // Act: Delete one child comment
        $childComment1->delete();

        // Assert: Reload the parent and check if reply_count has decremented
        $parentComment->refresh();
        $this->assertEquals(1, $parentComment->reply_count);

        // Delete the second child comment
        $childComment2->delete();

        // Assert: Reload the parent and check if reply_count has decremented to 0
        $parentComment->refresh();
        $this->assertEquals(0, $parentComment->reply_count);
    }
}
