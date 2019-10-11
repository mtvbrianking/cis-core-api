<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @see \App\Http\Controllers\UserController
 */
class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_users()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/users');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'users' => [
                '*' => [
                    'id',
                    'facility_id',
                    'role_id',
                    'user_id',
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
    }

    public function test_can_get_specified_user()
    {
        $attrs = [
            'alias' => 'jdoe',
            'name' => 'John Doe',
            'email' => 'jdoe@example.com',
        ];

        $user = factory(User::class)->create($attrs);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/users/{$user->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'facility_id',
            'role_id',
            'user_id',
            'alias',
            'name',
            'email',
            'email_verified_at',
            'created_at',
            'updated_at',
            'deleted_at',
            'facility' => [
                'id',
                'user_id',
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
            'role' => [
                'id',
                'facility_id',
                'user_id',
                'name',
                'description',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
        ]);

        $response->assertJson($attrs);
    }

    public function test_can_create_a_user()
    {
        $user = factory(User::class)->create();

        $role = factory(Role::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $attrs = [
            'alias' => 'jdoe',
            'name' => 'John Doe',
            'email' => 'jdoe@example.com',
            'role_id' => $role->id,
        ];

        $response = $this->actingAs($user, 'api')->json('POST', 'api/v1/users', $attrs);

        $response->assertStatus(201);

        $response->assertJsonStructure([
            'id',
            'facility_id',
            'role_id',
            'user_id',
            'alias',
            'name',
            'email',
            'email_verified_at',
            'created_at',
            'updated_at',
            'deleted_at',
            'facility' => [
                'id',
                'user_id',
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
            'role' => [
                'id',
                'facility_id',
                'user_id',
                'name',
                'description',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'creator' => [
                'id',
                'facility_id',
                'role_id',
                'alias',
                'name',
                'email',
                'email_verified_at',
                'user_id',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
        ]);

        $response->assertJson($attrs);
    }

    public function test_can_update_specified_user()
    {
        $user = factory(User::class)->create();

        $role = factory(Role::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $attrs = [
            'alias' => 'jdoe',
            'name' => 'John Doe',
            'email' => 'jdoe@example.com',
            'role_id' => $role->id,
        ];

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/users/{$user->id}", $attrs);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'facility_id',
            'role_id',
            'user_id',
            'alias',
            'name',
            'email',
            'email_verified_at',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $response->assertJson($attrs);
    }

    public function test_can_revoke_specified_user()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/users/{$user->id}/revoke");

        // what happens when a user is soft deleted?
        // api - revoke associated access tokens
        // web - require user to login again

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'facility_id',
            'role_id',
            'user_id',
            'alias',
            'name',
            'email',
            'email_verified_at',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }

    public function test_cant_restore_non_revoked_role()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/users/{$user->id}/restore");

        $response->assertStatus(404);

        $response->assertJsonStructure([
            'message',
        ]);
    }

    public function test_can_restore_revoked_role()
    {
        $user = factory(User::class)->create([
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        // do passport enforce softdelete?
        // This should return 401 since the user is soft deleted
        // Can a revoke user login?

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/users/{$user->id}/restore");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'facility_id',
            'role_id',
            'user_id',
            'alias',
            'name',
            'email',
            'email_verified_at',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'deleted_at' => null,
        ]);
    }

    public function test_cant_delete_non_revoked_user()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/users/{$user->id}");

        $response->assertStatus(404);

        $response->assertJsonStructure([
            'message',
        ]);
    }

    public function test_can_delete_revoked_user()
    {
        $user = factory(User::class)->create([
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/users/{$user->id}");

        $response->assertStatus(204);

        $this->assertEquals('', $response->getContent());

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }
}
