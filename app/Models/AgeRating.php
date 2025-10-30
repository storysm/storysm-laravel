<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

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
    use HasUlids;

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
