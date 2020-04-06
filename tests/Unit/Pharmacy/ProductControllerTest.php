<?php

namespace Tests\Unit\Pharmacy;

use App\Models\Pharmacy\Batch;
use App\Models\Pharmacy\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Pharmacy\ProductController
 */
class ProductControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_get_products()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/pharmacy/products');

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view-any', 'pharm-products');

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/pharmacy/products');

        $response->assertStatus(206);

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

    public function test_can_register_a_product()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('POST', 'api/v1/pharmacy/products');

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('create', 'pharm-products');

        $response = $this->actingAs($user, 'api')->json('POST', 'api/v1/pharmacy/products', [
            'name' => 'Paracentamol',
            'brand' => 'Panadol',
            'package' => 'tablet',
        ]);

        $response->assertStatus(201);

        $response->assertJsonStructure([
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
            'facility',
        ]);

        $attrs['name'] = 'Paracentamol';
        $attrs['brand'] = 'Panadol';
        $attrs['facility_id'] = $user->facility_id;

        $response->assertJson($attrs);
    }

    public function test_can_get_any_product_info()
    {
        $attrs = [
            'name' => 'Paracentamol',
            'brand' => 'Panadol',
            'package' => 'tablet',
        ];

        $product = factory(Product::class)->create($attrs);

        // ...

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/pharmacy/products/{$product->id}");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view', 'pharm-products');

        $product->facility()->associate($user->facility);
        $product->save();

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/pharmacy/products/{$product->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
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
            'facility',
        ]);

        $response->assertJson($attrs);
    }

    public function test_can_update_specified_product()
    {
        $product = factory(Product::class)->create();

        // ...

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/pharmacy/products/{$product->id}");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('update', 'pharm-products');

        $product->facility()->associate($user->facility);
        $product->save();

        $attrs = [
            'name' => 'Paracentamol',
            'brand' => 'Panadol',
            'package' => 'tablet',
        ];

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/pharmacy/products/{$product->id}", $attrs);

        $response->assertStatus(200);

        $response->assertJsonStructure([
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
            'facility',
        ]);

        $response->assertJson($attrs);
    }

    public function test_can_revoke_specified_product()
    {
        $user = factory(User::class)->create();

        // ...

        $product = factory(Product::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/pharmacy/products/{$product->id}/revoke");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('soft-delete', 'pharm-products');

        $product->facility()->associate($user->facility);
        $product->save();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/pharmacy/products/{$product->id}/revoke");

        $response->assertStatus(200);

        $response->assertJsonStructure([
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
            'facility',
        ]);

        $this->assertSoftDeleted('pharm_products', [
            'facility_id' => $user->facility_id,
        ]);
    }

    public function test_cant_restore_non_revoked_product()
    {
        $user = factory(User::class)->create();

        // ...

        $product = factory(Product::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/pharmacy/products/{$product->id}/restore");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('restore', 'pharm-products');

        $product->facility()->associate($user->facility);
        $product->save();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/pharmacy/products/{$product->id}/restore");

        $response->assertStatus(404);

        $response->assertJsonStructure([
            'message',
        ]);
    }

    public function test_can_restore_revoked_product()
    {
        $user = factory(User::class)->create();

        // ...

        $product = factory(Product::class)->create([
            'facility_id' => $user->facility_id,
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/pharmacy/products/{$product->id}/restore");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('restore', 'pharm-products');

        $product->facility()->associate($user->facility);
        $product->save();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/pharmacy/products/{$product->id}/restore");

        $response->assertStatus(200);

        $response->assertJsonStructure([
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
            'facility',
        ]);

        $this->assertDatabaseHas('pharm_products', [
            'deleted_at' => null,
        ]);
    }

    public function test_cant_delete_non_revoked_product()
    {
        $user = factory(User::class)->create();

        // ...

        $product = factory(Product::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/pharmacy/products/{$product->id}");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('force-delete', 'pharm-products');

        $product->facility()->associate($user->facility);
        $product->save();

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/pharmacy/products/{$product->id}");

        $response->assertStatus(404);

        $response->assertJsonStructure([
            'message',
        ]);
    }

    public function test_can_delete_revoked_product()
    {
        $user = factory(User::class)->create();

        $product = factory(Product::class)->create([
            'facility_id' => $user->facility_id,
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/pharmacy/products/{$product->id}");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('force-delete', 'pharm-products');

        $product->deleted_at = date('Y-m-d H:i:s');
        $product->facility()->associate($user->facility);
        $product->save();

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/pharmacy/products/{$product->id}");

        $response->assertStatus(204);

        $this->assertEquals('', $response->getContent());

        $this->assertDatabaseMissing('pharm_products', [
            'id' => $product->id,
        ]);
    }

    public function test_cant_delete_non_orphaned_product()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->expectExceptionCode('25P02');

        $user = $this->getAuthorizedUser('force-delete', 'pharm-products');

        $product = factory(Product::class)->create([
            'facility_id' => $user->facility_id,
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        // Dependant
        factory(Batch::class)->create([
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/pharmacy/products/{$product->id}");

        $response->assertStatus(500);

        $response->assertJsonStructure([
            'message',
        ]);

        $this->assertDatabaseHas('pharm_products', [
            'id' => $product->id,
        ]);
    }
}
