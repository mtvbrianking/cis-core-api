<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ModulePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any modules.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view-any', 'modules');
    }

    /**
     * Determine whether the user can view the module.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function view(User $user)
    {
        return $user->hasPermissionTo('view', 'modules');
    }

    /**
     * Determine whether the user can create modules.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('update', 'modules');
    }

    /**
     * Determine whether the user can update the module.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function update(User $user)
    {
        return $user->hasPermissionTo('update', 'modules');
    }

    /**
     * Determine whether the user can delete the module.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function softDelete(User $user)
    {
        return $user->hasPermissionTo('soft-delete', 'modules');
    }

    /**
     * Determine whether the user can restore the module.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function restore(User $user)
    {
        return $user->hasPermissionTo('restore', 'modules');
    }

    /**
     * Determine whether the user can permanently delete the module.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function forceDelete(User $user)
    {
        return $user->hasPermissionTo('force-delete', 'modules');
    }
}
