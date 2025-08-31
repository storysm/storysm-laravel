<?php

namespace App\Models;

use App\Concerns\CanFormatCount;
use App\Enums\Vote\Type;
use Carbon\Carbon;
use Database\Factories\StoryCommentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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
 * @property-read Collection<int, StoryComment> $storyComments
 * @property-read User $creator
 * @property-read ?StoryComment $parent
 * @property-read Story $story
 *
 * @method static StoryCommentFactory factory(...$parameters)
 * @method static Builder|StoryComment newModelQuery()
 * @method static Builder|StoryComment newQuery()
 * @method static Builder|StoryComment query()
 * @method static Builder|StoryComment find(string|array<string> $id, array<string> $columns = ['*'])
 */
class StoryComment extends Model
{
    use CanFormatCount;

    /** @use HasFactory<StoryCommentFactory> */
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

        static::created(function (StoryComment $storyComment) {
            // If this StoryComment is a reply, update the parent's reply count
            if ($storyComment->parent_id !== null) {
                $storyComment->parent()->increment('reply_count');
            }

            // Update story StoryComment count
            $storyComment->story()->increment('comment_count');
        });
    }

    // Add a deleted event listener to decrement counts
    protected static function booted(): void
    {
        parent::booted();

        static::deleted(function (StoryComment $storyComment) {
            if ($storyComment->parent_id !== null) {
                StoryComment::where('id', $storyComment->parent_id)->decrement('reply_count');
            }
            Story::where('id', $storyComment->story_id)->decrement('comment_count');
        });
    }

    /**
     * Get the children for the StoryComment.
     *
     * @return HasMany<StoryComment, $this>
     */
    public function storyComments(): HasMany
    {
        return $this->hasMany(StoryComment::class, 'parent_id');
    }

    /**
     * Get the creator (user) of the StoryComment.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function isReferenced(): bool
    {
        if ($this->storyComments()->exists()) {
            return true;
        }

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
     * Get the upvote count formatted with suffixes (K, M, B, T).
     */
    public function formattedUpvoteCount(): string
    {
        return $this->formatCount($this->upvote_count);
    }

    /**
     * Get the downvote count formatted with suffixes (K, M, B, T).
     */
    public function formattedDownvoteCount(): string
    {
        return $this->formatCount($this->downvote_count);
    }

    /**
     * Get the parent of the StoryComment.
     *
     * @return BelongsTo<StoryComment, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(StoryComment::class, 'parent_id');
    }

    /**
     * Get the story that the StoryComment belongs to.
     *
     * @return BelongsTo<Story, $this>
     */
    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }

    /**
     * Get the votes for the StoryComment.
     *
     * @return HasMany<StoryCommentVote, $this>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(StoryCommentVote::class, 'story_comment_id');
    }

    /**
     * Get the user's vote for the StoryComment.
     *
     * @return HasOne<StoryCommentVote, $this>
     */
    public function userVote(): HasOne
    {
        return $this->hasOne(StoryCommentVote::class, 'story_comment_id')->where('creator_id', Auth::id());
    }

    /**
     * Get the current user's vote for the StoryComment.
     */
    public function currentUserVote(): ?StoryCommentVote
    {
        return $this->userVote()->first();
    }

    /**
     * Handle voting for the StoryComment.
     */
    public function vote(Type $type): void
    {
        if (! Auth::check()) {
            return;
        }

        $existingVote = $this->userVote()->first();

        if ($existingVote) {
            if ($existingVote->type === $type) {
                // User is unvoting
                $existingVote->delete();
            } else {
                // User is changing their vote
                $existingVote->type = $type;
                $existingVote->save();
            }
        } else {
            // User is casting a new vote
            $this->votes()->create([
                'creator_id' => Auth::id(),
                'type' => $type,
            ]);
        }
    }
}
