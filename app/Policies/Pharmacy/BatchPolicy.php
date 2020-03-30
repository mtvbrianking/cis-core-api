<?php

namespace App\Policies\Pharmacy;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BatchPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any batches.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view-any', 'pharm-batches');
    }

    /**
     * Determine whether the user can create batches.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('create', 'pharm-batches');
    }

    /**
     * Determine whether the user can permanently delete the batch.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function delete(User $user)
    {
        return $user->hasPermissionTo('delete', 'pharm-batches');
    }
}
