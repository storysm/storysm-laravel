<?php

namespace App\Scopes;

use App\Models\Story;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class GuestStoryFilterScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  Builder<Story>  $builder
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::guest()) {
            $guestAgeLimit = Config::get('age_rating.guest_limit_years', 16);
            $builder->whereNotNull('age_rating_effective_value')
                ->where('age_rating_effective_value', '<=', $guestAgeLimit);
        }
    }
}
