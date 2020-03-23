<?php

namespace Tests\Unit\Pharmacy;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Pharmacy\ProductController
 */
class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_products()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/pharmacy/products');

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view-any', 'pharm-products');

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/pharmacy/products');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'current_page',
            'data' => [
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

    public function test_can_get_non_paginated_products()
    {
        $user = $this->getAuthorizedUser('view-any', 'pharm-products');

        $query = http_build_query([
            'paginate' => 0,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/pharmacy/products?{$query}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
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
    }
}
