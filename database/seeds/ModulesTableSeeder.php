<?php

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Module::insert([
            [
                'name' => 'facilities',
            ],
            [
                'name' => 'modules',
            ],
            [
                'name' => 'permissions',
            ],
            [
                'name' => 'roles',
            ],
            [
                'name' => 'users',
            ],
            [
                'name' => 'pharm-stores',
            ],
            [
                'name' => 'pharm-products',
            ],
            [
                'name' => 'pharm-sales',
            ],
            [
                'name' => 'pharm-purchases',
            ],
            [
                'name' => 'patients',
            ],
        ]);
    }
}
