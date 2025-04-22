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
     * Get the view count formatted with suffixes (K, M, B, T).
     */
    public function formattedViewCount(): string
    {
        $count = $this->view_count;

        if ($count < 1000) {
            return (string) $count;
        }

        $suffixes = ['', 'K', 'M', 'B', 'T'];
        $thresholds = [1, 1000, 1000000, 1000000000, 1000000000000];

        // Find the appropriate suffix and threshold
        $i = count($thresholds) - 1;
        while ($i > 0 && $count < $thresholds[$i]) {
            $i--;
        }

        // Calculate the raw value scaled by the threshold
        $rawValue = $count / $thresholds[$i];

        // Calculate the value rounded to 1 decimal place to check for the edge case
        $roundedToOneDecimal = round($rawValue, 1);

        $finalValue = $rawValue;
        $finalPrecision = ($rawValue == floor($rawValue)) ? 0 : 1; // Default precision

        // Check if rounding to 1 decimal place results in 1000 or more (the next magnitude base)
        // This happens for values like 999999, 999999999, etc., when divided by their threshold (1000, 1000000, etc.)
        // Also ensure we are not already at the highest suffix ('T')
        if ($roundedToOneDecimal >= 1000 && $i < count($suffixes) - 1) {
            // This is the edge case where we want 999.9 followed by the current suffix
            // Calculate 999.9 by flooring after multiplying by 10 and then dividing by 10.
            $finalValue = floor($rawValue * 10) / 10;
            $finalPrecision = 1; // Always 1 decimal place for this specific edge case format
        }

        // Return the rounded value with the determined precision and the suffix
        // Use number_format to ensure the correct number of decimal places are shown,
        // especially for the edge case (e.g. 999.9).
        // number_format handles rounding correctly based on the specified precision.
        // We use '.' for decimal point and '' for thousands separator.
        $formattedNumber = number_format($finalValue, $finalPrecision, '.', '');

        return $formattedNumber . $suffixes[$i];
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
