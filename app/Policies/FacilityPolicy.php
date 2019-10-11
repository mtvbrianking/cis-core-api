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
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the facility.
     *
     * @param \App\Models\User $user
     * @param \App\Facility    $facility
     *
     * @return mixed
     */
    public function view(User $user, Facility $facility)
    {
        //
    }

    /**
     * Determine whether the user can create facilities.
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
     * Determine whether the user can update the facility.
     *
     * @param \App\Models\User $user
     * @param \App\Facility    $facility
     *
     * @return mixed
     */
    public function update(User $user, Facility $facility)
    {
        //
    }

    /**
     * Determine whether the user can delete the facility.
     *
     * @param \App\Models\User $user
     * @param \App\Facility    $facility
     *
     * @return mixed
     */
    public function delete(User $user, Facility $facility)
    {
        //
    }

    /**
     * Determine whether the user can restore the facility.
     *
     * @param \App\Models\User $user
     * @param \App\Facility    $facility
     *
     * @return mixed
     */
    public function restore(User $user, Facility $facility)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the facility.
     *
     * @param \App\Models\User $user
     * @param \App\Facility    $facility
     *
     * @return mixed
     */
    public function forceDelete(User $user, Facility $facility)
    {
        //
    }
}
