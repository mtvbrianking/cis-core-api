<?php

namespace Tests\Unit\Pharmacy;

use App\Models\Pharmacy\Inventory;
use App\Models\Pharmacy\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Pharmacy\InventoryController
 */
class InventoryControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_get_inventories()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/pharmacy/inventories');

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view-any', 'pharm-inventories');

        $store = factory(Store::class)->create();

        $query = http_build_query([
            'store_id' => $store->id,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/pharmacy/inventories?{$query}");

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

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/pharmacy/inventories?{$query}");

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

    public function test_can_revoke_an_inventory()
    {
        $inventory = factory(Inventory::class)->create([
            'deleted_at' => null,
        ]);

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/pharmacy/inventories/{$inventory->id}/revoke");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('soft-delete', 'pharm-inventories');

        $random_inventory_id = base_convert(microtime(true), 10, 16);

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/pharmacy/inventories/{$random_inventory_id}/revoke");

        $response->assertStatus(404);

        // ...

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/pharmacy/inventories/{$inventory->id}/revoke");

        $response->assertStatus(404);

        // ...

        $user->pharm_stores()->sync([
            $inventory->store_id,
        ], true);
        $inventory->save();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/pharmacy/inventories/{$inventory->id}/revoke");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'store_id',
            'product_id',
            'quantity',
            'unit_price',
            'created_at',
            'updated_at',
            'deleted_at',
            'store',
            'product',
        ]);

        $this->assertSoftDeleted('pharm_inventories', [
            'id' => $inventory->id,
        ]);
    }

    public function test_can_restore_an_inventory()
    {
        $inventory = factory(Inventory::class)->create([
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/pharmacy/inventories/{$inventory->id}/restore");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('restore', 'pharm-inventories');

        $random_inventory_id = base_convert(microtime(true), 10, 16);

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/pharmacy/inventories/{$random_inventory_id}/restore");

        $response->assertStatus(404);

        // ...

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/pharmacy/inventories/{$inventory->id}/restore");

        $response->assertStatus(404);

        // ...

        $user->pharm_stores()->sync([
            $inventory->store_id,
        ], true);
        $inventory->save();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/pharmacy/inventories/{$inventory->id}/restore");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'store_id',
            'product_id',
            'quantity',
            'unit_price',
            'created_at',
            'updated_at',
            'deleted_at',
            'store',
            'product',
        ]);

        $this->assertDatabaseHas('pharm_inventories', [
            'deleted_at' => null,
        ]);
    }

    public function test_can_delete_specified_inventory()
    {
        $inventory = factory(Inventory::class)->create([
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/pharmacy/inventories/{$inventory->id}");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('force-delete', 'pharm-inventories');

        $random_inventory_id = base_convert(microtime(true), 10, 16);

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/pharmacy/inventories/{$random_inventory_id}");

        $response->assertStatus(404);

        // ...

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/pharmacy/inventories/{$inventory->id}");

        $response->assertStatus(404);

        // ...

        $user->pharm_stores()->sync([
            $inventory->store_id,
        ], true);
        $inventory->save();

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/pharmacy/inventories/{$inventory->id}");

        $response->assertStatus(204);

        $this->assertEquals('', $response->getContent());

        $this->assertDatabaseMissing('pharm_inventories', [
            'id' => $inventory->id,
        ]);
    }
}
