<?php

namespace Tests\Unit\Pharmacy;

use App\Models\Pharmacy\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Pharmacy\StoreProductController
 */
class StoreProductControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_get_store_products()
    {
        // $this->withoutExceptionHandling();

        $user = factory(User::class)->create();

        $store = factory(Store::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/pharmacy/stores/{$store->id}/products");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view-products', 'pharm-stores');

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/pharmacy/stores/{$store->id}/products");

        $response->assertStatus(404);

        $response->assertJsonStructure([
            'message',
        ]);

        // ...

        $store->facility()->associate($user->facility);
        $store->save();

        $query = http_build_query([
            'filters' => [
                'select' => [
                    'quantity',
                    'unit_price',
                    'store.name',
                    'product.id',
                    'product.name',
                ],
                'offset' => 0,
                'limit' => 10,
            ],
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/pharmacy/stores/{$store->id}/products?{$query}");

        $response->assertStatus(206);

        $response->assertJsonStructure([
            'current_page',
            'data' => [
                '*' => [
                    'quantity',
                    'unit_price',
                    'store' => [
                        'name',
                    ],
                    'product' => [
                        'id',
                        'name',
                    ],
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

    public function test_can_get_store_products_for_datatables()
    {
        // $this->withoutExceptionHandling();

        $user = factory(User::class)->create();

        $store = factory(Store::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/pharmacy/stores/{$store->id}/products/datatables");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view-products', 'pharm-stores');

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/pharmacy/stores/{$store->id}/products/datatables");

        $response->assertStatus(404);

        $response->assertJsonStructure([
            'message',
        ]);

        // ...

        $store->facility()->associate($user->facility);
        $store->save();

        $query = http_build_query([
            'start' => 0,
            'length' => 10,
            'draw' => 1,
            'columns' => [
                [
                    'data' => 'quantity',
                    'name' => 'quantity',
                    'searchable' => false,
                    'orderable' => true,
                    'search' => [
                        'value' => null,
                        'regex' => false,
                    ],
                ],
                [
                    'data' => 'unit_price',
                    'name' => 'unit_price',
                    'searchable' => false,
                    'orderable' => true,
                    'search' => [
                        'value' => null,
                        'regex' => false,
                    ],
                ],
                [
                    'data' => 'store.name',
                    'name' => 'store.name',
                    'searchable' => true,
                    'orderable' => true,
                    'search' => [
                        'value' => null,
                        'regex' => false,
                    ],
                ],
                [
                    'data' => 'product.id',
                    'name' => 'product.id',
                    'searchable' => false,
                    'orderable' => false,
                    'search' => [
                        'value' => null,
                        'regex' => false,
                    ],
                ],
                [
                    'data' => 'product.name',
                    'name' => 'product.name',
                    'searchable' => true,
                    'orderable' => true,
                    'search' => [
                        'value' => null,
                        'regex' => false,
                    ],
                ],
            ],
            'order' => [
                [
                    'column' => 1,
                    'dir' => 'asc',
                ],
            ],
            'search' => [
                'value' => null,
                'regex' => false,
            ],
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/pharmacy/stores/{$store->id}/products/datatables?{$query}");

        $response->assertStatus(206);

        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data' => [
                '*' => [
                    'quantity',
                    'unit_price',
                    'store' => [
                        'name',
                    ],
                    'product' => [
                        'id',
                        'name',
                    ],
                ],
            ],
        ]);
    }
}
