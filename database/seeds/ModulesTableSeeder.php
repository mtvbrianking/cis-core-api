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
        $module = new Module();
        $module->name = 'modules';
        $module->description = "Modules' module.";

        $module->save();
    }
}