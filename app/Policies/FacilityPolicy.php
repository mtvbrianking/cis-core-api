<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FacilityPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any facilities.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view-any', 'facilities');
    }

    /**
     * Determine whether the user can view the facility.
     *
     * @param \App\Models\User $user
     * @param string           $facilityId
     *
     * @return bool
     */
    public function view(User $user, string $facilityId)
    {
        if ($user->facility_id == $facilityId) {
            return true;
        }

        return $user->hasPermissionTo('view', 'facilities');
    }

    /**
     * Determine whether the user can create facilities.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('create', 'facilities');
    }

    /**
     * Determine whether the user can update the facility.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function update(User $user)
    {
        return $user->hasPermissionTo('update', 'facilities');
    }

    /**
     * Determine whether the user can delete the facility.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function softDelete(User $user)
    {
        return $user->hasPermissionTo('soft-delete', 'facilities');
    }

    /**
     * Determine whether the user can restore the facility.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function restore(User $user)
    {
        return $user->hasPermissionTo('restore', 'facilities');
    }

    /**
     * Determine whether the user can permanently delete the facility.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function forceDelete(User $user)
    {
        return $user->hasPermissionTo('force-delete', 'facilities');
    }
}
