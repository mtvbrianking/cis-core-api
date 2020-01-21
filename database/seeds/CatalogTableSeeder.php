<?php

use App\Models\Catalog;
use App\Models\Facility;
use Illuminate\Database\Seeder;

class CatalogTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $facility = Facility::first();

        // $catalog = new Catalog();
        // $catalog->name = 'Developer';
        // $catalog->facility()->associate($facility);
        // $catalog->save();

        factory(Catalog::class, 5)->create(['facility_id' => $facility->id]);
    }
}
