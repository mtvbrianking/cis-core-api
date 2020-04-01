<?php

use App\Models\Pharmacy\Batch;
use App\Models\Pharmacy\Inventory;
use App\Models\Pharmacy\Store;
use Illuminate\Database\Seeder;

class InventoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $store = Store::first();

        Batch::all()->each(function ($batch) use ($store) {
            $inventory = new Inventory();
            $inventory->store_id = $store->id;
            $inventory->product_id = $batch->product->id;
            $inventory->quantity = rand(1, 20);
            $inventory->unit_price = 2000;
            $inventory->save();
        });
    }
}
