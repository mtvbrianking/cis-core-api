<?php

use App\Models\Facility;
use App\Models\Permission;
use App\Models\Role;
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
        $facility = Facility::first();

        $role = new Role();
        $role->name = 'Developer';
        $role->facility()->associate($facility);
        $role->save();

        // Assign all existing permissions to this role.

        $permissions = range(1, Permission::count());
        $role->permissions()->attach($permissions);
    }
}
