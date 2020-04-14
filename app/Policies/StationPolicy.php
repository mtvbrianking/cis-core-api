<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any station.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view-any', 'stations');
    }

    /**
     * Determine whether the user can view this station.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function view(User $user)
    {
        return $user->hasPermissionTo('view', 'stations');
    }

    /**
     * Determine whether the user can resgiter stations.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('create', 'stations');
    }

    /**
     * Determine whether the user can update the stations.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function update(User $user)
    {
        return $user->hasPermissionTo('update', 'stations');
    }

    /**
     * Determine whether the user can revoke a station.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function softDelete(User $user)
    {
        return $user->hasPermissionTo('soft-delete', 'stations');
    }

    /**
     * Determine whether the user can restore the station.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function restore(User $user)
    {
        return $user->hasPermissionTo('restore', 'stations');
    }

    /**
     * Determine whether the user can sync users for a  station.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function syncStationUsers(User $user)
    {
        return $user->hasPermissionTo('sync-station-users', 'stations');
    }
}
