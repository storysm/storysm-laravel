<?php

namespace App\Policies;

use App\Models\StoryComment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StoryCommentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, StoryComment $storyComment): bool
    {
        if ($user->isNot($storyComment->creator) && ! $this->viewAll($user)) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can view all models.
     */
    public function viewAll(User $user): bool
    {
        return $user->can('view_all_story::comment');
    }

    /**
     * Determine whether the user can create the model.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, StoryComment $storyComment): bool
    {
        if ($storyComment->isReferenced()) {
            return false;
        }
        if ($user->isNot($storyComment->creator) && ! $this->viewAll($user)) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, StoryComment $storyComment): bool
    {
        if ($storyComment->isReferenced()) {
            return false;
        }
        if ($user->isNot($storyComment->creator) && ! $this->viewAll($user)) {
            return false;
        }

        return true;
    }
}
