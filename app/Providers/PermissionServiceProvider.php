<?php

namespace App\Providers;

use App\Models\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Permission service provider.
 */
class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Models\Facility' => 'App\Policies\FacilityPolicy',
        'App\Models\Module' => 'App\Policies\ModulePolicy',
        'App\Models\Permission' => 'App\Policies\PermissionPolicy',
        'App\Models\Role' => 'App\Policies\RolePolicy',
        'App\Models\User' => 'App\Policies\UserPolicy',

        'App\Models\Pharmacy\Store' => 'App\Policies\Pharmacy\StorePolicy',
        'App\Models\Pharmacy\Product' => 'App\Policies\Pharmacy\ProductPolicy',
        'App\Models\Pharmacy\Sale' => 'App\Policies\Pharmacy\SalePolicy',
        'App\Models\Pharmacy\Purchase' => 'App\Policies\Pharmacy\PurchasePolicy',

        'App\Models\Patient' => 'App\Policies\PatientPolicy',
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Register gates.
        // Permission::get()->map(function($permission) {
        //     Gate::define($permission->name.' '.$permission->module_name, function($user) use ($permission) {
        //        return $user->hasPermission($permission);
        //     });
        // });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Register the application's policies.
     *
     * @return void
     */
    public function registerPolicies()
    {
        foreach ($this->policies as $key => $value) {
            Gate::policy($key, $value);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        // What to do here...
        return [];
    }
}
