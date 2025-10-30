<?php

namespace App\Policies;

use App\Models\AgeRating;
use App\Models\User;

class AgeRatingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_age::rating');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AgeRating $ageRating): bool
    {
        return $user->can('view_age::rating');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_age::rating');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AgeRating $ageRating): bool
    {
        return $user->can('update_age::rating');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AgeRating $ageRating): bool
    {
        if ($ageRating->isReferenced()) {
            return false;
        }

        return $user->can('delete_age::rating');
    }
}
