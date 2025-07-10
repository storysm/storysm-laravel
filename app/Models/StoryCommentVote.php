<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Observers\StoryCommentVoteObserver;

/**
 * @property string $id
 * @property string $creator_id
 * @property string $story_comment_id
 * @property string $vote_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @property-read \App\Models\StoryComment $comment
 *
 * @method static \Database\Factories\StoryCommentVoteFactory factory(...$parameters)
 */
#[ObservedBy([StoryCommentVoteObserver::class])]
class StoryCommentVote extends Model
{
    /**
     * @use HasFactory<\Database\Factories\StoryCommentVoteFactory>
     */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'story_comment_votes';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'creator_id',
        'story_comment_id',
        'vote_type',
    ];

    /**
     * Get the user that owns the vote.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get the comment that the vote belongs to.
     *
     * @return BelongsTo<StoryComment, $this>
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(StoryComment::class, 'story_comment_id');
    }
}
