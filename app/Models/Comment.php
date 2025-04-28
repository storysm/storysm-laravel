<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Spatie\Translatable\HasTranslations;

/**
 * @property string $id
 * @property string $story_id
 * @property string $creator_id
 * @property ?string $parent_id
 * @property string $body
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 * @property-read Collection<int, Comment> $comments
 * @property-read User $creator
 * @property-read ?Comment $parent
 * @property-read Story $story
 */
class Comment extends Model
{
    /** @use HasFactory<\Database\Factories\CommentFactory> */
    use HasFactory;

    use HasTranslations;
    use HasUlids;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be translated.
     *
     * @var array<string>
     */
    public $translatable = ['body'];

    protected static function boot(): void
    {
        parent::boot();

        static::created(function (Comment $comment) {
            self::updateStoryCommentCount($comment);
        });

        static::deleted(function (Comment $comment) {
            self::updateStoryCommentCount($comment);
        });
    }

    /**
     * Recalculates and updates the comment_count on the associated story.
     * Handles guarded attribute by using direct assignment and save().
     */
    private static function updateStoryCommentCount(Comment $comment): void
    {
        $story = $comment->story;
        $story->comment_count = $story->comments()->count();
        $story->save();
    }

    /**
     * Get the children for the comment.
     *
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * Get the creator (user) of the comment.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get the parent of the comment.
     *
     * @return BelongsTo<Comment, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get the story that the comment belongs to.
     *
     * @return BelongsTo<Story, $this>
     */
    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }
}
