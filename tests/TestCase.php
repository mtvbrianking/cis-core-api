<?php

namespace Tests;

use App\Models\Facility;
use App\Models\Module;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Passport\Passport;

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
            'facility_id' => $facility->id,
            'name' => $roleName,
        ]);

        $role->permissions()->attach($permission);

        return factory(User::class)->create([
            'facility_id' => $facility->id,
            'role_id' => $role->id,
        ]);
    }
}
