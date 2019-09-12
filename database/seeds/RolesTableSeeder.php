<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = new Role();
        $role->name = 'Developer';
        $role->save();

        // Assign all permissions to this role.

        $permissions = range(1, Permission::count());
        $role->permissions()->attach($permissions);

        // Assign role to default user.

        $user = User::first();
        $user->role()->associate($role);
        $user->save();
    }
}
