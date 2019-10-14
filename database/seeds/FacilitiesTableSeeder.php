<?php

use App\Models\Module;
use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $facility = new Facility();
        $facility->name = 'Mulago Hospital';
        $facility->description = 'Regional Referral Hospital';
        $facility->address = 'Mulago Hill';
        $facility->email = 'info@mulago.com';
        $facility->website = 'https://mulago.ug';
        $facility->phone = '+256754954852';
        $facility->save();

        // Assign all existing modules to this facility.

        $modules = Module::select('name')->get();

        $module_names = $modules->map(function ($module) {
            return $module->name;
        })->all();

        $facility->modules()->attach($module_names);
    }
}
