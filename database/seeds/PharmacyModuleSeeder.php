<?php

use App\Models\Facility;
use App\Models\Pharmacy\Product;
use App\Models\Pharmacy\Purchase;
use App\Models\Pharmacy\Sale;
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

        // store

        $store = factory(Store::class)->create([
            'facility_id' => $facility->id,
            'name' => 'Back Office',
        ]);

        // store-user

        $store->users()->attach($user);
        $store->save();

        // Stock-product

        $productNames = ['Panadol', 'Paracetamol', 'Sodium Chloride', 'Ethanol', 'Magnesium'];

        foreach ($productNames as $key => $productName) {
            factory(Product::class)->create([
                'facility_id' => $facility->id,
                'name' => $productName,
            ]);
        }

        $products = Product::all();

        foreach ($products as $key => $product) {
            $store->products()->attach([
                $product->id => [
                    'quantity' => 40,
                    'unit_price' => 1200,
                ],
            ]);
        }

        // purchase

        $products = Product::first();

        $purchase = factory(Purchase::class)->create([
            'store_id' => $store->id,
            'user_id' => $user->id,
            'total' => 50000,
        ]);

        // purchase-product

        $purchase->products()->attach([
            $product->id => [
                'quantity' => 50,
                'unit_price' => 1000,
            ],
        ]);

        // sale

        $sale = factory(Sale::class)->create([
            'store_id' => $store->id,
            'user_id' => $user->id,
            'tax_rate' => 0,
            'total' => 12000,
        ]);

        // sale-product

        $sale->products()->attach([
            $product->id => [
                'quantity' => 10,
                'price' => 1200,
            ],
        ]);
    }
}
