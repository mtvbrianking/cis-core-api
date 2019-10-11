<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Module;
use Illuminate\Auth\Access\HandlesAuthorization;

class ModulePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any app models modules.
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
     * Determine whether the user can view the app models module.
     *
     * @param \App\Models\User     $user
     * @param \App\AppModelsModule $appModelsModule
     *
     * @return mixed
     */
    public function view(User $user, AppModelsModule $appModelsModule)
    {
        //
    }

    /**
     * Determine whether the user can create app models modules.
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
     * Determine whether the user can update the app models module.
     *
     * @param \App\Models\User     $user
     * @param \App\AppModelsModule $appModelsModule
     *
     * @return mixed
     */
    public function update(User $user, AppModelsModule $appModelsModule)
    {
        //
    }

    /**
     * Determine whether the user can delete the app models module.
     *
     * @param \App\Models\User     $user
     * @param \App\AppModelsModule $appModelsModule
     *
     * @return mixed
     */
    public function delete(User $user, AppModelsModule $appModelsModule)
    {
        //
    }

    /**
     * Determine whether the user can restore the app models module.
     *
     * @param \App\Models\User     $user
     * @param \App\AppModelsModule $appModelsModule
     *
     * @return mixed
     */
    public function restore(User $user, AppModelsModule $appModelsModule)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the app models module.
     *
     * @param \App\Models\User     $user
     * @param \App\AppModelsModule $appModelsModule
     *
     * @return mixed
     */
    public function forceDelete(User $user, AppModelsModule $appModelsModule)
    {
        //
    }
}
