<?php

namespace Tests\Unit;

use App\Models\Facility;
use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\FacilityController
 */
class FacilityControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_facilities()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/facilities');

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view-any', 'facilities');

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/facilities');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'current_page',
            'data' => [
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

    public function test_can_get_non_paginated_facilities()
    {
        $user = $this->getAuthorizedUser('view-any', 'facilities');

        $query = http_build_query([
            'paginate' => 0,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/facilities?{$query}");

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

    public function test_can_get_facilities_for_datatables()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/facilities/datatables');

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view-any', 'facilities');

        $query = http_build_query([
            'start' => 0,
            'length' => 10,
            'draw' => 1,
            'columns' => [
                [
                    'data' => 'facilities.id',
                    'name' => 'facilities.id',
                    'searchable' => 'true',
                    'orderable' => 'true',
                    'search' => [
                        'value' => null,
                        'regex' => 'false',
                    ],
                ],
                [
                    'data' => 'facilities.name',
                    'name' => 'facilities.name',
                    'searchable' => 'true',
                    'orderable' => 'true',
                    'search' => [
                        'value' => null,
                        'regex' => 'false',
                    ],
                ],
                [
                    'data' => 'facilities.email',
                    'name' => 'facilities.email',
                    'searchable' => 'true',
                    'orderable' => 'true',
                    'search' => [
                        'value' => null,
                        'regex' => 'false',
                    ],
                ],
                [
                    'data' => 'facilities.website',
                    'name' => 'facilities.website',
                    'searchable' => 'true',
                    'orderable' => 'true',
                    'search' => [
                        'value' => null,
                        'regex' => 'false',
                    ],
                ],
                [
                    'data' => 'facilities.deleted_at',
                    'name' => 'facilities.deleted_at',
                    'searchable' => 'true',
                    'orderable' => 'true',
                    'search' => [
                        'value' => null,
                        'regex' => 'false',
                    ],
                ],
            ],
            'order' => [
                [
                    'column' => '1',
                    'dir' => 'asc',
                ],
            ],
            'search' => [
                'value' => null,
                'regex' => 'false',
            ],
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/facilities/datatables?{$query}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'website',
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

    public function test_can_delete_non_orphaned_facility()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/facilities/{$user->facility_id}");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('force-delete', 'facilities');

        $facility = factory(Facility::class)->create([
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        // Dependant
        factory(User::class)->create([
            'facility_id' => $facility->id,
        ]);

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/facilities/{$facility->id}");

        $response->assertStatus(400);

        $response->assertJsonStructure([
            'message',
        ]);

        $this->assertDatabaseHas('facilities', [
            'id' => $facility->id,
        ]);
    }

    public function test_can_get_facility_roles()
    {
        $facility = factory(Facility::class)->create();

        factory(Role::class)->create([
            'facility_id' => $facility->id,
        ]);

        // ...

        $user = factory(User::class)->create([
            'facility_id' => $facility->id,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/facilities/{$facility->id}/roles");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view-any', 'roles');

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/facilities/{$facility->id}/roles");

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
            'roles' => [
                '*' => [
                    'id',
                    'facility_id',
                    'name',
                    'description',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
            ],
        ]);

        $response->assertJson([
            'id' => $facility->id,
        ]);
    }

    public function test_can_get_facility_users()
    {
        $facility = factory(Facility::class)->create();

        // ...

        $user = factory(User::class)->create([
            'facility_id' => $facility->id,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/facilities/{$facility->id}/users");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view-any', 'users');

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/facilities/{$facility->id}/users");

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
            'users' => [
                '*' => [
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
                ],
            ],
        ]);

        $response->assertJson([
            'id' => $facility->id,
        ]);
    }

    public function test_can_get_facility_modules()
    {
        $facility = factory(Facility::class)->create();

        // ...

        $user = factory(User::class)->create([
            'facility_id' => $facility->id,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/facilities/{$facility->id}/modules");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view-any', 'modules');

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/facilities/{$user->facility->id}/modules");

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
            'modules' => [
                '*' => [
                    'name',
                    'category',
                    'description',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
            ],
        ]);

        $response->assertJson([
            'id' => $user->facility->id,
        ]);
    }

    public function test_can_get_modules_available_to_a_facility()
    {
        $facility = factory(Facility::class)->create();

        // ...

        $user = factory(User::class)->create([
            'facility_id' => $facility->id,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/facilities/{$facility->id}/modules/available");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('assign-modules', 'modules');

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/facilities/{$user->facility->id}/modules/available");

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
            'modules' => [
                '*' => [
                    'name',
                    'category',
                    'granted',
                ],
            ],
        ]);

        $response->assertJson([
            'id' => $user->facility->id,
            'modules' => [
                [
                    'name' => 'modules',
                    'granted' => true,
                ],
            ],
        ]);
    }

    public function test_can_sync_facility_modules()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/facilities/{$user->facility_id}/modules/available");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('assign-modules', 'modules');

        $facility = factory(Facility::class)->create();

        $module = factory(Module::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/facilities/{$facility->id}/modules/available", [
            'modules' => [
                $module->name,
            ],
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
            'modules' => [
                '*' => [
                    'name',
                    'category',
                    'granted',
                ],
            ],
        ]);

        $this->assertDatabaseHas('facility_module', [
            'facility_id' => $facility->id,
            'module_name' => $module->name,
        ]);
    }

    public function test_cant_sync_unknown_facility_modules()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/facilities/{$user->facility_id}/modules/available");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('assign-modules', 'modules');

        $facility = factory(Facility::class)->create();

        $module = factory(Module::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/facilities/{$facility->id}/modules/available", [
            'modules' => [
                $module->name,
                'random-mod',
            ],
        ]);

        $response->assertStatus(422);

        $response->assertJsonStructure([
            'message',
            'errors' => [
                'modules',
            ],
        ]);

        $response->assertJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                'modules' => [
                    'Unknown modules: random-mod',
                ],
            ],
        ]);
    }
}
