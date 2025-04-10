<?php

namespace App\Models;

use App\Concerns\HasCreatorAttribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * @property string $id
 * @property string $creator_id
 * @property ?string $cover_media_id
 * @property string $title
 * @property string $content
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
    ];

    /**
     * @return HasOne<Media, $this>
     */
    public function coverMedia(): HasOne
    {
        return $this->hasOne(Media::class, 'cover_media_id');
    }
}
