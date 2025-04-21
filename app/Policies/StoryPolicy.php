<?php

namespace App\Policies;

use App\Enums\Story\Status;
use App\Models\Story;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StoryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Story $story): bool
    {
        if ($user->isNot($story->creator) && ! $this->viewAll($user)) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can view all models.
     */
    public function viewAll(User $user): bool
    {
        return $user->can('view_all_story');
    }

    /**
     * Determine whether the user can view the model on the public frontend.
     */
    public function viewPublic(?User $user, Story $story): bool
    {
        // If the story is a draft, only the creator or someone with 'view_all_story' can view it.
        return $story->status !== Status::Draft || ($user && ($user->is($story->creator) || $this->viewAll($user)));
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Story $story): bool
    {
        if ($user->isNot($story->creator) && ! $this->viewAll($user)) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Story $story): bool
    {
        if ($story->isReferenced()) {
            return false;
        }
        if ($user->isNot($story->creator) && ! $this->viewAll($user)) {
            return false;
        }

        return true;
    }
}
