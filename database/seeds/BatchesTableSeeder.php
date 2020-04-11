<?php

use App\Models\Pharmacy\Batch;
use App\Models\Pharmacy\Product;
use App\Models\Pharmacy\Store;
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
        // $store = Store::first();

        // Product::all()->each(function ($product) use ($store) {
        //     $batch = new Batch();
        //     $batch->mfd_at = '2010-02-01';
        //     $batch->expires_at = '2020-08-01';

        //     $batch->product()->associate($product);
        //     $batch->store()->associate($store);
        //     $batch->unit_price = '2500';
        //     $batch->quantity = rand(1, 20);
        //     $batch->save();

        //     $batch = new Batch();
        //     $batch->mfd_at = '2010-03-01';
        //     $batch->expires_at = '2020-09-01';
        //     $batch->product()->associate($product);
        //     $batch->store()->associate($store);
        //     $batch->unit_price = '3000';
        //     $batch->quantity = rand(1, 20);
        //     $batch->save();
        // });
    }
}
