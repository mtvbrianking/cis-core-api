<?php

namespace Tests\Unit\Pharmacy;

use App\Models\Pharmacy\Batch;
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

    public function test_can_get_batches()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/pharmacy/batches');

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view-any', 'pharm-batches');

        $store = factory(Store::class)->create();

        $query = http_build_query([
            'store_id' => $store->id,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/pharmacy/batches?{$query}");

        $response->assertStatus(422);

        $response->assertJsonStructure([
            'message',
            'errors' => [
                'store_id',
            ],
        ]);

        // ...

        $user->pharm_stores()->sync([
            $store->id,
        ], true);

        $user->save();

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/pharmacy/batches?{$query}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'current_page',
            'data' => [
                '*' => [
                    'id',
                    'store_id',
                    'product_id',
                    'quantity',
                    'unit_price',
                    'mfr_batch_no',
                    'mfd_at',
                    'expires_at',
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

    public function test_can_create_a_batch()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('POST', 'api/v1/pharmacy/batches');

        $response->assertStatus(403);

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

    public function test_can_delete_specified_batch()
    {
        $user = factory(User::class)->create();

        // ...

        $batch = factory(Batch::class)->create();

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/pharmacy/batches/{$batch->id}");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('delete', 'pharm-batches');

        $random_batch_id = base_convert(microtime(true), 10, 16);

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/pharmacy/batches/{$random_batch_id}");

        $response->assertStatus(404);

        // ...

        // $user = $this->getAuthorizedUser('delete', 'pharm-batches');

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/pharmacy/batches/{$batch->id}");

        $response->assertStatus(403);

        // ...

        $user->pharm_stores()->sync([
            $batch->store_id,
        ], true);
        $batch->save();

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/pharmacy/batches/{$batch->id}");

        $response->assertStatus(204);

        $this->assertEquals('', $response->getContent());

        $this->assertDatabaseMissing('pharm_batches', [
            'id' => $batch->id,
        ]);
    }
}
