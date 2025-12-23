<?php

namespace App\Scopes;

use App\Facades\AgeVerification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class GuestStoryFilterScope implements Scope
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

        // If age is set, filter by age rating
        $builder->where('age_rating_effective_value', '<=', AgeVerification::getAge());
    }
}
