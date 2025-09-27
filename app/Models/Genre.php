<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Collection<int, \App\Models\Story> $stories
 */
class Genre extends Model
{
    /** @use HasFactory<\Database\Factories\GenreFactory> */
    use HasFactory;

    use HasTranslations;
    use HasUlids;

    /**
     * @var string[]
     */
    public $translatable = ['name', 'description'];

    /*
     * @var string[]
     */
    protected $guarded = [];

    public function isReferenced(): bool
    {
        return $this->stories()->exists();
    }

    /**
     * @return BelongsToMany<Story, $this>
     */
    public function stories(): BelongsToMany
    {
        return $this->belongsToMany(Story::class);
    }
}
