<?php

namespace App\Policies\Pharmacy;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchasePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any purchases.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view-any', 'pharm-purchases');
    }

    /**
     * Determine whether the user a sale details.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function view(User $user)
    {
        return $user->hasPermissionTo('view', 'pharm-purchases');
    }

    /**
     * Determine whether the user can credit store products.
     *
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('create', 'pharm-purchases');
    }
}
