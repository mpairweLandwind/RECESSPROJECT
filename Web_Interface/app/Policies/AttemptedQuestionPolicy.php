<?php

namespace App\Policies;

use App\Models\AttemptedQuestion;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AttemptedQuestionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AttemptedQuestion $attemptedQuestion): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AttemptedQuestion $attemptedQuestion): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AttemptedQuestion $attemptedQuestion): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AttemptedQuestion $attemptedQuestion): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AttemptedQuestion $attemptedQuestion): bool
    {
        //
    }
}
