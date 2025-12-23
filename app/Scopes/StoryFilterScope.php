<?php

namespace App\Scopes;

use App\Facades\AgeVerification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class StoryFilterScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  Builder<Model>  $builder
     */
    public function apply(Builder $builder, Model $model)
    {
        // If age is not set, return early (show all stories)
        if (! AgeVerification::hasAgeSet()) {
            return;
        }

        $age = AgeVerification::getAge();

        // If age is 18 or above, show all stories (no filtering needed)
        if ($age >= 18) {
            return;
        }

        // If age is below 18, filter by age rating
        // Show stories where age_rating_effective_value is not null and <= age
        // Stories with null age_rating_effective_value are excluded for users under 18
        $builder->where('age_rating_effective_value', '<=', $age);
    }
}
