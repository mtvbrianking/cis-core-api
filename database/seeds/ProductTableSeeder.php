<?php

use App\Models\Facility;
use App\Models\Pharmacy\Product;
use Illuminate\Database\Seeder;

class ProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $facility = Facility::first();

        // $product = new Product();
        // $product->name = 'Developer';
        // $product->facility()->associate($facility);
        // $product->save();

        factory(Product::class, 5)->create(['facility_id' => $facility->id]);
    }
}
