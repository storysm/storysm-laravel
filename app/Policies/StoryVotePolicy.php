<?php

namespace App\Policies;

use App\Models\StoryVote;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StoryVotePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, StoryVote $storyVote): bool
    {
        if ($user->isNot($storyVote->creator) && ! $this->viewAll($user)) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can view all models.
     */
    public function viewAll(User $user): bool
    {
        return $user->can('view_all_story_vote');
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
    public function update(User $user, StoryVote $storyVote): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, StoryVote $storyVote): bool
    {
        if ($storyVote->isReferenced()) {
            return false;
        }
        if ($user->isNot($storyVote->creator) && ! $this->viewAll($user)) {
            return false;
        }

        return true;
    }
}
