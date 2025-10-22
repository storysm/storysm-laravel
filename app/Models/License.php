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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class License extends Model
{
    /** @use HasFactory<\Database\Factories\LicenseFactory> */
    use HasFactory;

    use HasTranslations;
    use HasUlids;

    /**
     * @var array<int, string>
     */
    public $translatable = ['name', 'description'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'name' => 'array',
        'description' => 'array',
    ];

    /**
     * Determine if the license is referenced by any models.
     */
    public function isReferenced(): bool
    {
        return $this->stories()->exists();
    }

    /**
     * Get the stories associated with the license.
     *
     * @return BelongsToMany<Story, $this>
     */
    public function stories(): BelongsToMany
    {
        return $this->belongsToMany(Story::class, 'license_story');
    }
}
