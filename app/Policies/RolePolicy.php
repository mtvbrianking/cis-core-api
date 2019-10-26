<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any roles.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view-any', 'roles');
    }

    /**
     * Determine whether the user can view the role.
     *
     * @param \App\Models\User $user
     * @param string           $roleId
     *
     * @return bool
     */
    public function view(User $user, string $roleId)
    {
        if ($user->role_id == $roleId) {
            return true;
        }

        return $user->hasPermissionTo('view', 'roles');
    }

    /**
     * Determine whether the user can create roles.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('create', 'roles');
    }

    /**
     * Determine whether the user can update the role.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function update(User $user)
    {
        return $user->hasPermissionTo('update', 'roles');
    }

    /**
     * Determine whether the user can delete the role.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function softDelete(User $user)
    {
        return $user->hasPermissionTo('soft-delete', 'roles');
    }

    /**
     * Determine whether the user can restore the role.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function restore(User $user)
    {
        return $user->hasPermissionTo('restore', 'roles');
    }

    /**
     * Determine whether the user can permanently delete the role.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function forceDelete(User $user)
    {
        return $user->hasPermissionTo('force-delete', 'roles');
    }

    /**
     * Determine whether the user can view any permissions on this role.
     *
     * @param \App\Models\User $user
     * @param string           $roleId
     *
     * @return bool
     */
    public function viewPermissions(User $user, string $roleId = null)
    {
        if ($roleId && $user->role_id == $roleId) {
            return true;
        }

        return $user->hasPermissionTo('view-permissions', 'roles');
    }
}
