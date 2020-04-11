<?php

namespace App\Policies\Pharmacy;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StorePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any stores.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view-any', 'pharm-stores');
    }

    /**
     * Determine whether the user can view the store.
     *
     * @param \App\Models\User $user
     * @param string           $storeId
     *
     * @return bool
     */
    public function view(User $user, string $storeId)
    {
        if ($user->pharm_stores()->wherePivot('store_id', $storeId)->exists()) {
            return true;
        }

        return $user->hasPermissionTo('view', 'pharm-stores');
    }

    /**
     * Determine whether the user can create stores.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('create', 'pharm-stores');
    }

    /**
     * Determine whether the user can update the store.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function update(User $user)
    {
        return $user->hasPermissionTo('update', 'pharm-stores');
    }

    /**
     * Determine whether the user can delete the store.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function softDelete(User $user)
    {
        return $user->hasPermissionTo('soft-delete', 'pharm-stores');
    }

    /**
     * Determine whether the user can restore the store.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function restore(User $user)
    {
        return $user->hasPermissionTo('restore', 'pharm-stores');
    }

    /**
     * Determine whether the user can permanently delete the store.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function forceDelete(User $user)
    {
        return $user->hasPermissionTo('force-delete', 'pharm-stores');
    }

    /**
     * Determine whether the user can sync users to stores.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function syncStoreUsers(User $user)
    {
        return $user->hasPermissionTo('sync-store-users', 'pharm-stores');
    }

    /**
     * Determine whether the user can view products belonging to this stores.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function viewProducts(User $user)
    {
        return $user->hasPermissionTo('view-products', 'pharm-stores');
    }
}
