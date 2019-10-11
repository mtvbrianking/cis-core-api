<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Facility;
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
        //
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
        //
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
        //
    }

    /**
     * Determine whether the user can update the facility.
     *
     * @param \App\Models\User $user
     * @param string           $facilityId
     *
     * @return bool
     */
    public function update(User $user, string $facilityId)
    {
        //
    }

    /**
     * Determine whether the user can delete the facility.
     *
     * @param \App\Models\User $user
     * @param string           $facilityId
     *
     * @return bool
     */
    public function softDelete(User $user, string $facilityId)
    {
        //
    }

    /**
     * Determine whether the user can restore the facility.
     *
     * @param \App\Models\User $user
     * @param string           $facilityId
     *
     * @return bool
     */
    public function restore(User $user, string $facilityId)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the facility.
     *
     * @param \App\Models\User $user
     * @param string           $facilityId
     *
     * @return bool
     */
    public function forceDelete(User $user, string $facilityId)
    {
        //
    }
}
