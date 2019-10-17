<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PermissionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any permissions.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view-any', 'permissions');
    }

    /**
     * Determine whether the user can view the permission.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function view(User $user)
    {
        return $user->hasPermissionTo('view', 'permissions');
    }

    /**
     * Determine whether the user can create permissions.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('create', 'permissions');
    }

    /**
     * Determine whether the user can update the permission.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function update(User $user)
    {
        return $user->hasPermissionTo('update', 'permissions');
    }

    /**
     * Determine whether the user can permanently delete the permission.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function delete(User $user)
    {
        return $user->hasPermissionTo('delete', 'permissions');
    }

    /**
     * Determine whether the user can assign permissions to roles.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function assignPermissions(User $user)
    {
        return $user->hasPermissionTo('assign-permissions', 'permissions');
    }
}
