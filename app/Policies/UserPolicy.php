<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the consumer can view any user.
     *
     * @param \App\Models\User $consumer
     *
     * @return mixed
     */
    public function viewAny(User $consumer)
    {
        return $consumer->hasPermissionTo('view-any', 'users');
    }

    /**
     * Determine whether the consumer can view a user.
     *
     * @param \App\Models\User $consumer
     * @param string           $userId
     *
     * @return mixed
     */
    public function view(User $consumer, string $userId)
    {
        if ($consumer->id == $userId) {
            return true;
        }

        return $consumer->hasPermissionTo('view', 'users');
    }

    /**
     * Determine whether the consumer can create users.
     *
     * @param \App\Models\User $consumer
     *
     * @return mixed
     */
    public function create(User $consumer)
    {
        return $consumer->hasPermissionTo('create', 'users');
    }

    /**
     * Determine whether the consumer can update a user.
     *
     * @param \App\Models\User $consumer
     * @param string           $userId
     *
     * @return mixed
     */
    public function update(User $consumer, string $userId)
    {
        if ($consumer->id == $userId) {
            return true;
        }

        return $consumer->hasPermissionTo('update', 'users');
    }

    /**
     * Determine whether the consumer can soft delete any user.
     *
     * @param \App\Models\User $consumer
     *
     * @return mixed
     */
    public function softDelete(User $consumer)
    {
        return $consumer->hasPermissionTo('soft-delete', 'users');
    }

    /**
     * Determine whether the consumer can restore any user.
     *
     * @param \App\Models\User $consumer
     *
     * @return mixed
     */
    public function restore(User $consumer)
    {
        return $consumer->hasPermissionTo('restore', 'users');
    }

    /**
     * Determine whether the consumer can permanently delete any user.
     *
     * @param \App\Models\User $consumer
     *
     * @return mixed
     */
    public function forceDelete(User $consumer)
    {
        return $consumer->hasPermissionTo('force-delete', 'users');
    }
}
