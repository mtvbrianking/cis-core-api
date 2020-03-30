<?php

namespace Tests\Unit\Pharmacy;

use App\Models\Pharmacy\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Pharmacy\StoreController
 */
class StoreControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_a_store()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('POST', 'api/v1/pharmacy/stores', []);

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('create', 'pharm-stores');

        $storeName = 'OPD Pharmacy';

        $response = $this->actingAs($user, 'api')->json('POST', 'api/v1/pharmacy/stores', [
            'name' => $storeName,
        ]);

        $response->assertStatus(201);

        $response->assertJsonStructure([
            'id',
            'facility_id',
            'name',
            'created_at',
            'updated_at',
            'deleted_at',
            'facility',
        ]);

        $attrs['name'] = Str::title($storeName);
        $attrs['facility_id'] = $user->facility_id;

        $response->assertJson($attrs);
    }

    public function test_can_get_stores()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/pharmacy/stores');

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view-any', 'pharm-stores');

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/pharmacy/stores');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'current_page',
            'data' => [
                '*' => [
                    'id',
                    'facility_id',
                    'name',
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

    public function test_can_get_non_paginated_stores()
    {
        $user = $this->getAuthorizedUser('view-any', 'pharm-stores');

        $query = http_build_query([
            'paginate' => 0,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/pharmacy/stores?{$query}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'stores' => [
                '*' => [
                    'id',
                    'facility_id',
                    'name',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
            ],
        ]);
    }

    public function test_can_get_any_store_info()
    {
        $storeName = 'OPD Pharmacy';

        $attrs = [
            'name' => $storeName,
        ];

        $store = factory(Store::class)->create($attrs);

        // ...

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/pharmacy/stores/{$store->id}");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view', 'pharm-stores');

        $store->facility()->associate($user->facility);
        $store->save();

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/pharmacy/stores/{$store->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'facility_id',
            'name',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $attrs['name'] = Str::title($storeName);

        $response->assertJson($attrs);
    }

    public function test_can_user_can_get_their_store_info()
    {
        $user = factory(User::class)->create();

        $storeName = 'OPD Pharmacy';

        $attrs = [
            'name' => $storeName,
            'facility_id' => $user->facility_id,
        ];

        $store = factory(Store::class)->create($attrs);

        $store->users()->sync($user->id, true);
        $store->save();

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/pharmacy/stores/{$store->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'facility_id',
            'name',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $attrs['name'] = Str::title($storeName);

        $response->assertJson($attrs);
    }

    public function test_can_update_specified_store()
    {
        $store = factory(Store::class)->create();

        $storeName = 'OPD Pharmacy';

        $attrs = [
            'name' => $storeName,
        ];

        // ...

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/pharmacy/stores/{$store->id}"); //, [];

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('update', 'pharm-stores');

        $store->facility()->associate($user->facility);
        $store->save();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/pharmacy/stores/{$store->id}", $attrs);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'facility_id',
            'name',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $attrs['name'] = Str::title($storeName);

        $response->assertJson($attrs);
    }

    public function test_can_revoke_specified_store()
    {
        $user = factory(User::class)->create();

        // ...

        $store = factory(Store::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/pharmacy/stores/{$store->id}/revoke");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('soft-delete', 'pharm-stores');

        $store->facility()->associate($user->facility);
        $store->save();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/pharmacy/stores/{$store->id}/revoke");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'name',
            'facility_id',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $this->assertSoftDeleted('pharm_stores', [
            'facility_id' => $user->facility_id,
        ]);
    }

    public function test_cant_restore_non_revoked_store()
    {
        $user = factory(User::class)->create();

        // ...

        $store = factory(Store::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/pharmacy/stores/{$store->id}/restore");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('restore', 'pharm-stores');

        $store->facility()->associate($user->facility);
        $store->save();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/pharmacy/stores/{$store->id}/restore");

        $response->assertStatus(404);

        $response->assertJsonStructure([
            'message',
        ]);
    }

    public function test_can_restore_revoked_store()
    {
        $user = factory(User::class)->create();

        // ...

        $store = factory(Store::class)->create([
            'facility_id' => $user->facility_id,
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/pharmacy/stores/{$store->id}/restore");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('restore', 'pharm-stores');

        $store->facility()->associate($user->facility);
        $store->save();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/pharmacy/stores/{$store->id}/restore");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'facility_id',
            'name',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $this->assertDatabaseHas('pharm_stores', [
            'deleted_at' => null,
        ]);
    }

    public function test_cant_delete_non_revoked_store()
    {
        $user = factory(User::class)->create();

        // ...

        $store = factory(Store::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/pharmacy/stores/{$store->id}");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('force-delete', 'pharm-stores');

        $store->facility()->associate($user->facility);
        $store->save();

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/pharmacy/stores/{$store->id}");

        $response->assertStatus(404);

        $response->assertJsonStructure([
            'message',
        ]);
    }

    public function test_can_delete_revoked_store()
    {
        $user = factory(User::class)->create();

        $store = factory(Store::class)->create([
            'facility_id' => $user->facility_id,
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/pharmacy/stores/{$store->id}");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('force-delete', 'pharm-stores');

        $store->deleted_at = date('Y-m-d H:i:s');
        $store->facility()->associate($user->facility);
        $store->save();

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/pharmacy/stores/{$store->id}");

        $response->assertStatus(204);

        $this->assertEquals('', $response->getContent());

        $this->assertDatabaseMissing('pharm_stores', [
            'id' => $store->id,
        ]);
    }

    public function test_cant_delete_non_orphaned_store()
    {
        $user = $this->getAuthorizedUser('force-delete', 'pharm-stores');

        $store = factory(Store::class)->create([
            'facility_id' => $user->facility_id,
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        // Dependant
        $store->users()->sync($user->id, true);
        $store->save();

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/pharmacy/stores/{$store->id}");

        $response->assertStatus(500);

        $response->assertJsonStructure([
            'message',
        ]);

        $this->assertDatabaseHas('pharm_stores', [
            'id' => $store->id,
        ]);
    }
}
