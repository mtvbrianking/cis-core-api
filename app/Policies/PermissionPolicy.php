<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Auth\Access\HandlesAuthorization;

class PermissionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any permissions.
     *
     * @param \App\Models\User $user
     *
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the permission.
     *
     * @param \App\Models\User $user
     * @param \App\Permission  $permission
     *
     * @return mixed
     */
    public function view(User $user, Permission $permission)
    {
        //
    }

    /**
     * Determine whether the user can create permissions.
     *
     * @param \App\Models\User $user
     *
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the permission.
     *
     * @param \App\Models\User $user
     * @param \App\Permission  $permission
     *
     * @return mixed
     */
    public function update(User $user, Permission $permission)
    {
        //
    }

    /**
     * Determine whether the user can delete the permission.
     *
     * @param \App\Models\User $user
     * @param \App\Permission  $permission
     *
     * @return mixed
     */
    public function delete(User $user, Permission $permission)
    {
        //
    }

    /**
     * Determine whether the user can restore the permission.
     *
     * @param \App\Models\User $user
     * @param \App\Permission  $permission
     *
     * @return mixed
     */
    public function restore(User $user, Permission $permission)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the permission.
     *
     * @param \App\Models\User $user
     * @param \App\Permission  $permission
     *
     * @return mixed
     */
    public function forceDelete(User $user, Permission $permission)
    {
        //
    }
}
