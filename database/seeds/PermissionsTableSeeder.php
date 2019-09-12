<?php

use App\Models\Module;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $module = Module::first();

        Permission::insert([
            [
                'module_name' => $module->name,
                'name' => 'Create',
            ],
            [
                'module_name' => $module->name,
                'name' => 'Retrieve',
            ],
            [
                'module_name' => $module->name,
                'name' => 'Update',
            ],
            [
                'module_name' => $module->name,
                'name' => 'Delete',
            ],
        ]);
    }
}
