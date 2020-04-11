<?php

namespace Tests\Unit\Pharmacy;

use App\Models\Pharmacy\Product;
use App\Models\Pharmacy\Purchase;
use App\Models\Pharmacy\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Pharmacy\PurchaseController
 */
class PurchaseControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cant_get_purchases_if_unauthorized()
    {
        $user = factory(User::class)->create();

        $store = factory(Store::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/pharmacy/stores/{$store->id}/purchases");

        $response->assertStatus(403);
    }

    public function test_cant_get_purchases_for_non_related_store()
    {
        $user = $this->getAuthorizedUser('view-any', 'pharm-purchases');

        $store = factory(Store::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/pharmacy/stores/{$store->id}/purchases");

        $response->assertStatus(404);
    }

    public function test_can_get_purchases()
    {
        $user = $this->getAuthorizedUser('view-any', 'pharm-purchases');

        $store = factory(Store::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        factory(Purchase::class)->create([
            'store_id' => $store->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/pharmacy/stores/{$store->id}/purchases");

        $response->assertStatus(206);

        $response->assertJsonStructure([
            'current_page',
            'data' => [
                '*' => [
                    'id',
                    'store_id',
                    'user_id',
                    'total',
                    'created_at',
                    'updated_at',
                ],
            ],
            'first_page_url',
            'from',
            'last_page',
            'last_page_url',
            'next_page_url',
            'path',
            'per_page',
            'prev_page_url',
            'to',
            'total',
        ]);
    }

    public function test_can_get_purchase_details()
    {
        $user = $this->getAuthorizedUser('view', 'pharm-purchases');

        $store = factory(Store::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $purchase = factory(Purchase::class)->create([
            'store_id' => $store->id,
            'user_id' => $user->id,
        ]);

        $product = factory(Product::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $purchase->products()->attach([
            $product->id => [
                'supplier_id' => null,
                'quantity' => 1,
                'unit_price' => 12.25,
                'mfr_batch_no' => null,
                'mfd_at' => null,
                'expires_at' => null,
            ],
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/pharmacy/stores/{$store->id}/purchases/{$purchase->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'store_id',
            'user_id',
            'total',
            'created_at',
            'updated_at',
            'store',
            'user',
            'products' => [
                '*' => [
                    'pivot' => [
                        'purchase_id',
                        'product_id',
                        'supplier_id',
                        'quantity',
                        'unit_price',
                        'mfr_batch_no',
                        'mfd_at',
                        'expires_at',
                    ],
                ],
            ],
        ]);
    }

    public function test_cant_stock_unknown_product()
    {
        $user = $this->getAuthorizedUser('create', 'pharm-purchases');

        $store = factory(Store::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $store->users()->sync($user->id, true);
        $store->save();

        // ...

        $random_inventory_id = base_convert(microtime(true), 10, 16);

        $response = $this->actingAs($user, 'api')->json('POST', "api/v1/pharmacy/stores/{$store->id}/purchases", [
            'products' => [
                [
                    'id' => $random_inventory_id,
                    'quantity' => 10,
                    'cost_price' => 100,
                    'unit_retail_price' => 12,
                ],
            ],
        ]);

        $response->assertStatus(422);

        $response->assertJsonStructure([
            'message',
            'errors' => [
                'products.0.id',
            ],
        ]);
    }

    public function test_can_make_a_purchase()
    {
        $user = $this->getAuthorizedUser('create', 'pharm-purchases');

        $store = factory(Store::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $store->users()->sync($user->id, true);
        $store->save();

        // ...

        $productA = factory(Product::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $productB = factory(Product::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $store->products()->attach([
            $productB->id => [
                'quantity' => 5,
                'unit_price' => 100,
            ],
        ]);

        // ...

        $response = $this->actingAs($user, 'api')->json('POST', "api/v1/pharmacy/stores/{$store->id}/purchases", [
            'products' => [
                [
                    'id' => $productA->id,
                    'quantity' => 5,
                    'cost_price' => 100,
                    'unit_retail_price' => 25,
                ],
                [
                    'id' => $productB->id,
                    'quantity' => 10,
                    'cost_price' => 1000,
                    'unit_retail_price' => 120,
                ],
            ],
        ]);

        $response->assertStatus(201);

        $response->assertJsonStructure([
            'id',
            'store_id',
            'user_id',
            'total',
            'created_at',
            'updated_at',
            'store',
            'user',
            // 'supplier',
            'products' => [
                '*' => [
                    'id',
                    'facility_id',
                    'name',
                    'brand',
                    'manufacturer',
                    'category',
                    'concentration',
                    'package',
                    'description',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                    'pivot' => [
                        'purchase_id',
                        'product_id',
                        'supplier_id',
                        'quantity',
                        'unit_price',
                        'mfr_batch_no',
                        'mfd_at',
                        'expires_at',
                    ],
                ],
            ],
        ]);

        $response->assertJson([
            'total' => '1100.00',
            'products' => [
                [
                    'id' => $productA->id,
                    'pivot' => [
                        'quantity' => 5,
                        'unit_price' => '25',
                    ],
                ],
                [
                    'id' => $productB->id,
                    'pivot' => [
                        'quantity' => 10,
                        'unit_price' => '120',
                    ],
                ],
            ],
        ]);

        $this->assertDatabaseHas('pharm_store_product', [
            'store_id' => $store->id,
            'product_id' => $productA->id,
            'quantity' => 5,
            'unit_price' => '25.00',
        ]);

        $this->assertDatabaseHas('pharm_store_product', [
            'store_id' => $store->id,
            'product_id' => $productB->id,
            'quantity' => 15,
            'unit_price' => '120.00',
        ]);
    }
}
