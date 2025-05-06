<?php

namespace App\Models;

use App\Concerns\CanFormatCount;
use App\Concerns\HasCreatorAttribute;
use App\Enums\Story\Status;
use App\Enums\Vote\Type;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Spatie\Translatable\HasTranslations;

/**
 * @property string $id
 * @property string $creator_id
 * @property ?string $cover_media_id
 * @property string $title
 * @property string $content
 * @property Status $status
 * @property int $upvote_count
 * @property int $downvote_count
 * @property int $vote_count
 * @property float $vote_score
 * @property int $comment_count
 * @property ?Carbon $published_at
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 * @property-read User $creator
 * @property-read ?Media $coverMedia
 */
class Story extends Model
{
    use CanFormatCount;
    use HasCreatorAttribute;

    /** @use HasFactory<\Database\Factories\StoryFactory> */
    use HasFactory;

    use HasTranslations;
    use HasUlids;

    /**
     * The penalty weight applied to downvotes when calculating the score.
     * A weight of 2 means each downvote subtracts 2 from the score.
     */
    private const DOWNVOTE_PENALTY_WEIGHT = 1.1;

    /**
     * @var array<int, string>
     */
    public $translatable = ['title', 'content'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'string',
        'published_at' => 'datetime',
        'status' => Status::class,
        'view_count' => 'integer',
        'upvote_count' => 'integer',
        'downvote_count' => 'integer',
        'vote_count' => 'integer',
        'vote_score' => 'float',
        'comment_count' => 'integer',
    ];

    /**
     * @var array<int, string>
     */
    protected $guarded = [
        'view_count',
        'upvote_count',
        'downvote_count',
        'vote_count',
        'vote_score',
        'comment_count',
    ];

    /**
     * Get the comments for the story.
     *
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    /**
     * Get the vote of the currently authenticated user for this story.
     */
    public function currentUserVote(): ?Vote
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        // Use the votes relationship to find the vote by the current user
        return $this->votes()->where('creator_id', $user->id)->first();
    }

    /**
     * Get the comment count formatted with suffixes (K, M, B, T).
     */
    public function formattedCommentCount(): string
    {
        return $this->formatCount($this->comment_count);
    }

    /**
     * Get the downvote count formatted with suffixes (K, M, B, T).
     */
    public function formattedDownvoteCount(): string
    {
        return $this->formatCount($this->downvote_count);
    }

    /**
     * Get the upvote count formatted with suffixes (K, M, B, T).
     */
    public function formattedUpvoteCount(): string
    {
        return $this->formatCount($this->upvote_count);
    }

    /**
     * Get the view count formatted with suffixes (K, M, B, T).
     */
    public function formattedViewCount(): string
    {
        return $this->formatCount($this->view_count);
    }

    /**
     * Get the total vote count formatted with suffixes (K, M, B, T).
     */
    public function formattedVoteCount(): string
    {
        return $this->formatCount($this->vote_count);
    }

    /**
     * Increment the view count, protected by session to prevent abuse.
     */
    public function incrementViewCount(): void
    {
        // Get the array of viewed story IDs and their last view timestamps from the session
        // The structure will be [story_id => last_view_timestamp]
        /** @var array<string, int> $viewedStories */
        $viewedStories = Session::get('viewed_stories', []);

        $storyId = $this->id;
        $currentTime = intval(Carbon::now()->timestamp); // Get current Unix timestamp

        // Check if the story has been viewed in this session before
        if (! isset($viewedStories[$storyId])) {
            // First view in this session: Increment and record timestamp
            $this->increment('view_count');
            $viewedStories[$storyId] = $currentTime;
        } else {
            // Story has been viewed before in this session
            $lastViewTime = $viewedStories[$storyId];

            // Check if more than 60 seconds (1 minute) have passed since the last view
            if ($currentTime - $lastViewTime > 60) {
                // More than 1 minute passed: Increment and update timestamp
                $this->increment('view_count');
                $viewedStories[$storyId] = $currentTime; // Update the timestamp
            }
            // If less than or equal to 1 minute passed, do nothing.
        }

        // Store the updated list back in the session
        Session::put('viewed_stories', $viewedStories);
    }

    public function isReferenced(): bool
    {
        if ($this->comments()->exists()) {
            return true;
        }

        return false;
    }

    /**
     * @param  Builder<Story>  $query
     */
    public function scopePending(Builder $query): void
    {
        $query->where('status', Status::Publish)
            ->where('published_at', '>', now());
    }

    /**
     * @param  Builder<Story>  $query
     */
    public function scopeOrderByVoteScore(Builder $query, string $direction = 'desc'): void
    {
        $query->orderBy('vote_score', $direction);
    }

    /**
     * @param  Builder<Story>  $query
     */
    public function scopeOrderByUpvotes(Builder $query, string $direction = 'desc'): void
    {
        $query->orderBy('upvote_count', $direction);
    }

    /**
     * @param  Builder<Story>  $query
     */
    public function scopeOrderByDownvotes(Builder $query, string $direction = 'desc'): void
    {
        $query->orderBy('downvote_count', $direction);
    }

    /**
     * @param  Builder<Story>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', Status::Publish)
            ->where('published_at', '<=', now());
    }

    /**
     * Get the users who have voted on this story.
     *
     * @return BelongsToMany<User, $this>
     */
    public function voters(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'votes', 'story_id', 'creator_id')
            ->withTimestamps();
    }

    /**
     * @return HasMany<Vote, $this>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Recalculate and update the vote counts and score for the story.
     */
    public function updateVoteCountsAndScore(): void
    {
        $upvotes = $this->votes()->where('type', Type::Up)->count();
        $downvotes = $this->votes()->where('type', Type::Down)->count();

        $this->upvote_count = $upvotes;
        $this->downvote_count = $downvotes;
        $this->vote_count = $upvotes + $downvotes;
        $this->vote_score = $upvotes - ($downvotes * self::DOWNVOTE_PENALTY_WEIGHT);

        $this->save();
    }

    /**
     * Handle the voting action for the currently authenticated user.
     *
     * @param  ?Type  $type  The type of vote (Upvote, Downvote) or null to remove vote.
     */
    public function vote(?Type $type): void
    {
        $user = Auth::user();

        if (! $user) {
            // Or throw an exception, depending on desired behavior for unauthenticated users
            return;
        }

        $existingVote = $this->currentUserVote();

        if ($existingVote) {
            if ($existingVote->type === $type) {
                // User clicked the same vote type again, remove the vote
                $existingVote->delete();
            } elseif ($type === null) {
                // User clicked remove vote, delete the vote
                $existingVote->delete();
            } else {
                // User changed their vote type
                $existingVote->type = $type;
                $existingVote->save();
            }
        } elseif ($type !== null) {
            // No existing vote, create a new one
            $this->votes()->create([
                'creator_id' => $user->id,
                'story_id' => $this->id,
                'type' => $type,
            ]);
        }

        // Recalculate and update vote counts/score after the change
        $this->updateVoteCountsAndScore();
    }
}
