<?php

namespace App\Models;

use App\Concerns\HasCreatorAttribute;
use App\Enums\Vote\Type;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $creator_id
 * @property string $story_id
 * @property Type $type
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 * @property-read User $creator
 * @property-read Story $story
 */
class StoryVote extends Model
{
    use HasCreatorAttribute;

    /** @use HasFactory<\Database\Factories\StoryVoteFactory> */
    use HasFactory;

    use HasUlids;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => Type::class,
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // When a Vote is created or updated, update the story's counts/score
        static::saved(function (self $storyVote) {
            // TODO: Use a queue to avoid blocking the request, especially if stories have many votes. Ensure you have configured a queue driver other than 'sync' for production. Dispatch a job or call the method directly if not using queues.
            $storyVote->story->updateVoteCountsAndScore();
        });

        // When a Vote is deleted, update the story's counts/score
        static::deleted(function (self $storyVote) {
            // Check if the story still exists before trying to update it
            if ($storyVote->story()->exists()) {
                // TODO: Use a queue
                $storyVote->story->updateVoteCountsAndScore();
            }
        });
    }

    public function isReferenced(): bool
    {
        return false;
    }

    /**
     * Get the story that the vote belongs to.
     *
     * @return BelongsTo<Story, $this>
     */
    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class, 'story_id');
    }
}
