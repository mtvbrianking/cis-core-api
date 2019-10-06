<?php

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
    }
}
