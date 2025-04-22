<?php

namespace App\Models;

use App\Concerns\HasCreatorAttribute;
use App\Enums\Story\Status;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;
use Spatie\Translatable\HasTranslations;

/**
 * @property string $id
 * @property string $creator_id
 * @property ?string $cover_media_id
 * @property string $title
 * @property string $content
 * @property Status $status
 * @property ?Carbon $published_at
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 * @property-read User $creator
 * @property-read ?Media $coverMedia
 */
class Story extends Model
{
    use HasCreatorAttribute;

    /** @use HasFactory<\Database\Factories\StoryFactory> */
    use HasFactory;

    use HasTranslations;
    use HasUlids;

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
    ];

    /**
     * @var array<int, string>
     */
    protected $guarded = [
        'view_count',
    ];

    /**
     * @return BelongsTo<Media, $this>
     */
    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
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
    public function scopePublished(Builder $query): void
    {
        $query->where('status', Status::Publish)
            ->where('published_at', '<=', now());
    }
}
