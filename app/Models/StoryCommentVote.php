<?php

namespace App\Models;

use App\Enums\Vote\Type;
use App\Observers\StoryCommentVoteObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $creator_id
 * @property string $story_comment_id
 * @property Type $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $creator
 * @property-read StoryComment $comment
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

    use HasUlids;

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
        'type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => Type::class,
    ];

    /**
     * Get the comment that the vote belongs to.
     *
     * @return BelongsTo<StoryComment, $this>
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(StoryComment::class, 'story_comment_id');
    }

    /**
     * Get the user that owns the vote.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
