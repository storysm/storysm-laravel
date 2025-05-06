<?php

namespace App\Models;

use App\Concerns\CanFormatCount;
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
 * @property int $reply_count
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 * @property-read Collection<int, Comment> $comments
 * @property-read User $creator
 * @property-read ?Comment $parent
 * @property-read Story $story
 */
class Comment extends Model
{
    use CanFormatCount;

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
            // If this comment is a reply, update the parent's reply count
            if ($comment->parent_id !== null) {
                $comment->parent()->increment('reply_count');
            }

            // Update story comment count
            $comment->story()->increment('comment_count');
        });
    }

    // Add a deleted event listener to decrement counts
    protected static function booted(): void
    {
        parent::booted();

        static::deleted(function (Comment $comment) {
            if ($comment->parent_id !== null) {
                Comment::where('id', $comment->parent_id)->decrement('reply_count');
            }
            Story::where('id', $comment->story_id)->decrement('comment_count');
        });
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

    public function isReferenced(): bool
    {
        return false;
    }

    /**
     * Get the reply count formatted with suffixes (K, M, B, T).
     */
    public function formattedReplyCount(): string
    {
        return $this->formatCount($this->reply_count);
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
