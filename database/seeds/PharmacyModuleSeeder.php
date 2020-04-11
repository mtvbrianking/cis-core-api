<?php

use App\Models\Facility;
use App\Models\Pharmacy\Batch;
use App\Models\Pharmacy\Inventory;
use App\Models\Pharmacy\Product;
use App\Models\Pharmacy\Store;
use App\Models\User;
use Illuminate\Database\Seeder;

class PharmacyModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $facility = Facility::first();

        $user = User::first();

        $store = factory(Store::class)->create([
            'facility_id' => $facility->id,
<<<<<<< HEAD
            'name' => 'Back Office Store',
=======
>>>>>>> patients
        ]);

        $store->users()->attach($user);
        $store->save();

        $product = factory(Product::class)->create([
            'facility_id' => $facility->id,
        ]);

<<<<<<< HEAD
        // factory(Batch::class)->create([
        //     'store_id' => $store->id,
        //     'product_id' => $product->id,
        //     'quantity' => 10,
        //     'unit_price' => 100.00,
        // ]);

        // factory(Inventory::class)->create([
        //     'store_id' => $store->id,
        //     'product_id' => $product->id,
        //     'quantity' => 10,
        //     'unit_price' => 120.00,
        // ]);
=======
        factory(Batch::class)->create([
            'store_id' => $store->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 100.00,
        ]);

        factory(Inventory::class)->create([
            'store_id' => $store->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 120.00,
        ]);
>>>>>>> patients
    }
}
