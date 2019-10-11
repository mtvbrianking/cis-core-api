<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;
use App\Models\Module;
use App\Models\Facility;
use App\Models\Permission;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Passport::loadKeysFrom(__DIR__.'/storage');
    }

    /**
     * Clean up the testing environment before the next test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::setUp();

        // ...
    }

    /**
     * Create a user with necessary permissions.
     *
     * @param string $permissionName
     * @param string $moduleName
     * @param string $roleName
     *
     * @return \App\Models\User
     */
    protected function getAuthorizedUser($permissionName, $moduleName, $roleName = 'Tester'): User
    {
        $facility = factory(Facility::class)->create();

        $module = factory(Module::class)->create([
            'name' => $moduleName,
        ]);

        $facility->modules()->attach($module);

        $permission = factory(Permission::class)->create([
            'name' => $permissionName,
            'module_name' => $module->name,
        ]);

        $role = factory(Role::class)->create([
            'name' => $roleName,
        ]);

        $role->permissions()->attach($permission);

        return factory(User::class)->create([
            'role_id' => $role->id,
        ]);
    }
}
