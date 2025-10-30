<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * @property string $id
 * @property string $name
 * @property string $description
 * @property int $age_representation
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class AgeRating extends Model
{
    /** @use HasFactory<\Database\Factories\AgeRatingFactory> */
    use HasFactory;

    use HasTranslations;
    use HasUlids;

    /**
     * The attributes that are translatable.
     *
     * @var array<int, string>
     */
    public array $translatable = ['name', 'description'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'age_representation',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'age_representation' => 'integer',
    ];

    /**
     * Determine if the age rating is referenced by any stories.
     */
    public function isReferenced(): bool
    {
        return $this->stories()->exists();
    }

    /**
     * Get the stories associated with the age rating.
     *
     * @return BelongsToMany<Story, $this>
     */
    public function stories(): BelongsToMany
    {
        return $this->belongsToMany(Story::class, 'age_rating_story');
    }
}
