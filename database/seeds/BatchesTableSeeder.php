<?php

use App\Models\Batch;
use App\Models\Catalog;
use App\Models\Facility;
use Illuminate\Database\Seeder;

class BatchesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Catalog::all()->each(function($catalog){

            $batch = new Batch();
            $batch->manufactured_date = '2010-02-01';
            $batch->expires_at = '2020-08-01';
            $batch->facility_id = $catalog->facility_id;
            $batch->catalog()->associate($catalog);
            $batch->cost_price = '2500';
            $batch->save();


            $batch = new Batch();
            $batch->manufactured_date = '2010-03-01';
            $batch->expires_at = '2020-09-01';
            $batch->facility_id = $catalog->facility_id;
            $batch->catalog()->associate($catalog);
            $batch->cost_price = '3000';
            $batch->save();

            // factory(Batch::class, 2)->create([
            //     'facility_id' => $facility->id,
            //     'catalog_id' => $catalog->id
            // ]);
        });
    }
}