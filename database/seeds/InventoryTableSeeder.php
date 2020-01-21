<?php

use App\Models\Batch;
use App\Models\Inventory;
use App\Models\Store;
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
            $inventory->batch_id = $batch->id;
            $inventory->quantity = rand(1, 20);
            $inventory->save();
        });
    }
}
