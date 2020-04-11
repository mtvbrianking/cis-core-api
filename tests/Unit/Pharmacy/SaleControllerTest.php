<?php

namespace Tests\Unit\Pharmacy;

use App\Models\Pharmacy\Product;
use App\Models\Pharmacy\Sale;
use App\Models\Pharmacy\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Pharmacy\SaleController
 */
class SaleControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cant_get_sales_if_unauthorized()
    {
        $user = factory(User::class)->create();

        $store = factory(Store::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/pharmacy/stores/{$store->id}/sales");

        $response->assertStatus(403);
    }

    public function test_cant_get_sales_for_non_related_store()
    {
        $user = $this->getAuthorizedUser('view-any', 'pharm-sales');

        $store = factory(Store::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/pharmacy/stores/{$store->id}/sales");

        $response->assertStatus(404);
    }

    public function test_can_get_sales()
    {
        $user = $this->getAuthorizedUser('view-any', 'pharm-sales');

        $store = factory(Store::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        factory(Sale::class)->create([
            'store_id' => $store->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/pharmacy/stores/{$store->id}/sales");

        $response->assertStatus(206);

        $response->assertJsonStructure([
            'current_page',
            'data' => [
                '*' => [
                    'id',
                    'store_id',
                    'user_id',
                    'patient_id',
                    'tax_rate',
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

    public function test_can_get_sale_details()
    {
        $user = $this->getAuthorizedUser('view', 'pharm-sales');

        $store = factory(Store::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $sale = factory(Sale::class)->create([
            'store_id' => $store->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/pharmacy/stores/{$store->id}/sales/{$sale->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'store_id',
            'user_id',
            'patient_id',
            'tax_rate',
            'total',
            'created_at',
            'updated_at',
            'store',
            'user',
            // 'patient',
            'products',
        ]);
    }

    public function test_cant_sell_unknown_product()
    {
        $user = $this->getAuthorizedUser('create', 'pharm-sales');

        $store = factory(Store::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $store->users()->sync($user->id, true);
        $store->save();

        // ...

        $random_inventory_id = base_convert(microtime(true), 10, 16);

        $response = $this->actingAs($user, 'api')->json('POST', "api/v1/pharmacy/stores/{$store->id}/sales", [
            'products' => [
                [
                    'id' => $random_inventory_id,
                    'quantity' => 5,
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

    public function test_cant_sell_more_than_available_stock()
    {
        $user = $this->getAuthorizedUser('create', 'pharm-sales');

        $store = factory(Store::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $store->users()->sync($user->id, true);
        $store->save();

        // ...

        $product = factory(Product::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $store->products()->attach([
            $product->id => [
                'quantity' => 10,
                'unit_price' => 200.50,
            ],
        ]);

        // ...

        $response = $this->actingAs($user, 'api')->json('POST', "api/v1/pharmacy/stores/{$store->id}/sales", [
            'products' => [
                [
                    'id' => $product->id,
                    'quantity' => 20,
                ],
            ],
        ]);

        $response->assertStatus(422);

        $response->assertJsonStructure([
            'message',
            'errors' => [
                'products.0.quantity',
            ],
        ]);
    }

    public function test_can_make_a_sale()
    {
        $user = $this->getAuthorizedUser('create', 'pharm-sales');

        $store = factory(Store::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $store->users()->sync($user->id, true);
        $store->save();

        // ...

        $product = factory(Product::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $store->products()->attach([
            $product->id => [
                'quantity' => 10,
                'unit_price' => 200.50,
            ],
        ]);

        // ...

        $response = $this->actingAs($user, 'api')->json('POST', "api/v1/pharmacy/stores/{$store->id}/sales", [
            'products' => [
                [
                    'id' => $product->id,
                    'quantity' => 5,
                ],
            ],
        ]);

        $response->assertStatus(201);

        $response->assertJsonStructure([
            'id',
            'store_id',
            'user_id',
            'patient_id',
            'tax_rate',
            'total',
            'created_at',
            'updated_at',
            'store',
            'user',
            // 'patient',
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
                ],
            ],
        ]);

        $response->assertJson([
            'total' => '1002.5',
            'products' => [
                [
                    'id' => $product->id,
                    'pivot' => [
                        'quantity' => 5,
                        'price' => '200.50',
                    ],
                ],
            ],
        ]);

        $this->assertDatabaseHas('pharm_store_product', [
            'store_id' => $store->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);
    }
}
