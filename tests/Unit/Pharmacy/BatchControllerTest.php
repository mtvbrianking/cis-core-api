<?php

namespace Tests\Unit\Pharmacy;

use App\Models\Pharmacy\Product;
use App\Models\Pharmacy\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Pharmacy\BatchController
 */
class BatchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_a_batch()
    {
        // $user = factory(User::class)->create();

        // $response = $this->actingAs($user, 'api')->json('POST', 'api/v1/pharmacy/batches');

        // $response->assertStatus(403);

        // ...

        $this->withoutExceptionHandling();

        $user = $this->getAuthorizedUser('create', 'pharm-batches');

        $store = factory(Store::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $product = factory(Product::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $attrs = [
            'store_id' => $store->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 2500.00,
        ];

        $response = $this->actingAs($user, 'api')->json('POST', 'api/v1/pharmacy/batches', $attrs);

        $response->assertStatus(201);

        $response->assertJsonStructure([
            'id',
            'store_id',
            'product_id',
            'quantity',
            'unit_price',
            'mfr_batch_no',
            'mfd_at',
            'expires_at',
            'store',
            'product',
        ]);

        $response->assertJson($attrs);
    }
}
