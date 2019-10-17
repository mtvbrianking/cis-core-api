<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Module;
use App\Models\Facility;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @see \App\Http\Controllers\FacilityController
 */
class FacilityControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_facilities()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/users');

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view-any', 'facilities');

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/facilities');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'facilities' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'address',
                    'email',
                    'website',
                    'phone',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
            ],
        ]);
    }

    public function test_can_get_any_facility_info()
    {
        $facility = factory(Facility::class)->create();

        // ...

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/facilities/{$facility->id}");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view', 'facilities');

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/facilities/{$facility->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'name',
            'description',
            'address',
            'email',
            'website',
            'phone',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $response->assertJson([
            'id' => $facility->id,
        ]);
    }

    public function test_can_get_user_facility_info()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/facilities/{$user->facility_id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'name',
            'description',
            'address',
            'email',
            'website',
            'phone',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $response->assertJson([
            'id' => $user->facility_id,
        ]);
    }

    public function test_can_create_a_facility()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('POST', 'api/v1/facilities', []);

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('create', 'facilities');

        $response = $this->actingAs($user, 'api')->json('POST', 'api/v1/facilities', [
            'name' => 'Mulago Hospital',
            'description' => 'Regional Referral Hospital',
            'address' => 'Mulago Hill',
            'email' => 'info@mulago.com',
            'website' => 'https://mulago.ug',
            'phone' => '+256754954852',
        ]);

        $response->assertStatus(201);

        $response->assertJsonStructure([
            'id',
            'name',
            'description',
            'address',
            'email',
            'website',
            'phone',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $response->assertJson([
            'name' => 'Mulago Hospital',
            'description' => 'Regional Referral Hospital',
            'address' => 'Mulago Hill',
            'email' => 'info@mulago.com',
            'website' => 'https://mulago.ug',
            'phone' => '+256754954852',
        ]);
    }

    public function test_can_update_specified_facility()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/facilities/{$user->facility_id}", []);

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('update', 'facilities');

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/facilities/{$user->facility_id}", [
            'name' => 'Mulago Hospital',
            'description' => 'Regional Referral Hospital',
            'address' => 'Mulago Hill',
            'email' => 'info@mulago.com',
            'website' => 'https://mulago.ug',
            'phone' => '+256754954852',
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'name',
            'description',
            'address',
            'email',
            'website',
            'phone',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $response->assertJson([
            'name' => 'Mulago Hospital',
            'description' => 'Regional Referral Hospital',
            'address' => 'Mulago Hill',
            'email' => 'info@mulago.com',
            'website' => 'https://mulago.ug',
            'phone' => '+256754954852',
        ]);
    }

    public function test_can_revoke_specified_facility()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/facilities/{$user->facility_id}/revoke");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('soft-delete', 'facilities');

        $facility = factory(Facility::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/facilities/{$facility->id}/revoke");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'name',
            'description',
            'address',
            'email',
            'website',
            'phone',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $this->assertSoftDeleted('facilities', [
            'id' => $facility->id,
        ]);
    }

    public function test_cant_restore_non_revoked_facility()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/facilities/{$user->facility_id}/restore");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('restore', 'facilities');

        $facility = factory(Facility::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/facilities/{$facility->id}/restore");

        $response->assertStatus(404);

        $response->assertJsonStructure([
            'message',
        ]);
    }

    public function test_can_restore_revoked_facility()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/facilities/{$user->facility_id}");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('restore', 'facilities');

        $facility = factory(Facility::class)->create([
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/facilities/{$facility->id}/restore");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'name',
            'description',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $this->assertDatabaseHas('facilities', [
            'id' => $facility->id,
            'deleted_at' => null,
        ]);
    }

    public function test_cant_delete_non_revoked_facility()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/facilities/{$user->facility_id}");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('force-delete', 'facilities');

        $facility = factory(Facility::class)->create();

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/facilities/{$facility->id}");

        $response->assertStatus(404);

        $response->assertJsonStructure([
            'message',
        ]);
    }

    public function test_can_delete_revoked_facility()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/facilities/{$user->facility_id}");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('force-delete', 'facilities');

        $facility = factory(Facility::class)->create([
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/facilities/{$facility->id}");

        $response->assertStatus(204);

        $this->assertEquals('', $response->getContent());

        $this->assertDatabaseMissing('facilities', [
            'id' => $facility->id,
        ]);
    }

    public function test_can_reassign_facility_module_access()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/facilities/{$user->facility_id}/modules");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('assign-modules', 'modules');

        $facility = factory(Facility::class)->create();

        $module = factory(Module::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/facilities/{$facility->id}/modules", [
            'modules' => [
                $module->name,
            ],
        ]);

        $response->assertJsonStructure([
            'id',
            'name',
            'description',
            'address',
            'email',
            'website',
            'phone',
            'created_at',
            'updated_at',
            'deleted_at',
            'modules' => [
                '*' => [
                    'name',
                    'description',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
            ],
        ]);

        $this->assertDatabaseHas('facility_module', [
            'facility_id' => $facility->id,
            'module_name' => $module->name,
        ]);
    }
}
