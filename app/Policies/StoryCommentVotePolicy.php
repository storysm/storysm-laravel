<?php

namespace App\Policies;

use App\Models\StoryCommentVote;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StoryCommentVotePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, StoryCommentVote $storyCommentVote): bool
    {
        if ($user->isNot($storyCommentVote->creator) && ! $this->viewAll($user)) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can view all models.
     */
    public function viewAll(User $user): bool
    {
        return $user->can('view_all_story::comment::vote');
    }

    /**
     * Determine whether the user can create the model.
     * Handled via the frontend Livewire components, not the admin panel.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     * Handled via the frontend Livewire components, not the admin panel.
     */
    public function update(User $user, StoryCommentVote $storyCommentVote): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, StoryCommentVote $storyCommentVote): bool
    {
        if ($user->isNot($storyCommentVote->creator) && ! $this->viewAll($user)) {
            return false;
        }

        return true;
    }
}
