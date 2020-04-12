<?php

namespace Tests\Unit\Pharmacy;

use App\Models\Pharmacy\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Pharmacy\StoreUserController
 */
class StoreUserControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_get_stores_available_to_a_user()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/users/{$user->id}/pharmacy-stores/available");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('sync-store-users', 'pharm-stores');

        $granted_pharm = factory(Store::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $non_granted_pharm = factory(Store::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $user->pharm_stores()->sync($granted_pharm->id, true);
        $user->save();

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/users/{$user->id}/pharmacy-stores/available");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'facility_id',
            'role_id',
            'alias',
            'name',
            'email',
            'email_verified_at',
            'created_at',
            'updated_at',
            'deleted_at',
            'stores' => [
                '*' => [
                    'id',
                    'name',
                    'granted',
                ],
            ],
        ]);

        $response->assertJson([
            'id' => $user->id,
            'stores' => [
                [
                    'id' => $granted_pharm->id,
                    'granted' => true,
                ],
                [
                    'id' => $non_granted_pharm->id,
                    'granted' => false,
                ],
            ],
        ]);
    }

    public function test_can_sync_store_users()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/users/{$user->id}/pharmacy-stores/available");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('sync-store-users', 'pharm-stores');

        $granted_pharm = factory(Store::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $non_granted_pharm = factory(Store::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $user->pharm_stores()->sync($granted_pharm->id, true);
        $user->save();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/users/{$user->id}/pharmacy-stores/available", [
            'stores' => [
                $non_granted_pharm->id,
            ],
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'facility_id',
            'role_id',
            'alias',
            'name',
            'email',
            'email_verified_at',
            'created_at',
            'updated_at',
            'deleted_at',
            'stores' => [
                '*' => [
                    'id',
                    'name',
                    'granted',
                ],
            ],
        ]);

        $response->assertJson([
            'id' => $user->id,
            'stores' => [
                [
                    'id' => $granted_pharm->id,
                    'granted' => false,
                ],
                [
                    'id' => $non_granted_pharm->id,
                    'granted' => true,
                ],
            ],
        ]);
    }

    public function test_cant_sync_unknown_store_users()
    {
        $user = $this->getAuthorizedUser('sync-store-users', 'pharm-stores');

        $granted_pharm = factory(Store::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $non_granted_pharm = factory(Store::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $user->pharm_stores()->sync($granted_pharm->id, true);
        $user->save();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/users/{$user->id}/pharmacy-stores/available", [
            'stores' => [
                $non_granted_pharm->id,
                '0ac99f1b-482c-4af1-be28-ddace07eff20',
            ],
        ]);

        $response->assertStatus(422);

        $response->assertJsonStructure([
            'message',
            'errors' => [
                'stores',
            ],
        ]);

        $response->assertJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                'stores' => [
                    'Unknown stores: 0ac99f1b-482c-4af1-be28-ddace07eff20',
                ],
            ],
        ]);
    }
}
