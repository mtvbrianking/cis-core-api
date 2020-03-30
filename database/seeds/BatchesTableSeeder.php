<?php

use App\Models\Batch;
use App\Models\Facility;
use App\Models\Product;
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
        Product::all()->each(function ($product) {
            $batch = new Batch();
            $batch->mfd_at = '2010-02-01';
            $batch->expires_at = '2020-08-01';
            // $batch->facility_id = $product->facility_id;
            $batch->product()->associate($product);
            $batch->cost_price = '2500';
            $batch->save();

            $batch = new Batch();
            $batch->mfd_at = '2010-03-01';
            $batch->expires_at = '2020-09-01';
            // $batch->facility_id = $product->facility_id;
            $batch->product()->associate($product);
            $batch->cost_price = '3000';
            $batch->save();

            // factory(Batch::class, 2)->create([
            //     'facility_id' => $facility->id,
            //     'product_id' => $product->id
            // ]);
        });
    }
}
