<?php

use App\Models\Facility;
use App\Models\Pharmacy\Store;
use App\Models\User;
use Illuminate\Database\Seeder;

class StoresTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $facility = Facility::first();

        $store = factory(Store::class)->create(['facility_id' => $facility->id, 'name' => 'OPD Store']);

        // Assign the created store to the seed user.

        $store->users()->attach(User::first()->id);
    }
}
