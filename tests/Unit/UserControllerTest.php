<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\UserController
 */
class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_users()
    {
        $consumer = factory(User::class)->create();

        $response = $this->actingAs($consumer, 'api')->json('GET', 'api/v1/users');

        $response->assertStatus(403);

        // ...

        $consumer = $this->getAuthorizedUser('view-any', 'users');

        $response = $this->actingAs($consumer, 'api')->json('GET', 'api/v1/users');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'current_page',
            'data' => [
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
        $consumer = $this->getAuthorizedUser('view-any', 'users');

        $query = http_build_query([
            'paginate' => 0,
        ]);

        $response = $this->actingAs($consumer, 'api')->json('GET', "api/v1/users?{$query}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
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
    }

    public function test_can_get_users_for_datatables()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/users/datatables');

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view-any', 'users');

        $query = http_build_query([
            'start' => 0,
            'length' => 10,
            'draw' => 1,
            'columns' => [
                [
                    'data' => 'id',
                    'name' => 'id',
                    'searchable' => 'true',
                    'orderable' => 'true',
                    'search' => [
                        'value' => null,
                        'regex' => 'false',
                    ],
                ],
                [
                    'data' => 'alias',
                    'name' => 'alias',
                    'searchable' => 'true',
                    'orderable' => 'true',
                    'search' => [
                        'value' => null,
                        'regex' => 'false',
                    ],
                ],
                [
                    'data' => 'name',
                    'name' => 'name',
                    'searchable' => 'true',
                    'orderable' => 'true',
                    'search' => [
                        'value' => null,
                        'regex' => 'false',
                    ],
                ],
                [
                    'data' => 'email',
                    'name' => 'email',
                    'searchable' => 'true',
                    'orderable' => 'true',
                    'search' => [
                        'value' => null,
                        'regex' => 'false',
                    ],
                ],
                [
                    'data' => 'deleted_at',
                    'name' => 'deleted_at',
                    'searchable' => 'true',
                    'orderable' => 'true',
                    'search' => [
                        'value' => null,
                        'regex' => 'false',
                    ],
                ],
                [
                    'data' => 'role.name',
                    'name' => 'role.name',
                    'searchable' => 'true',
                    'orderable' => 'true',
                    'search' => [
                        'value' => null,
                        'regex' => 'false',
                    ],
                ],
                [
                    'data' => 'facility.name',
                    'name' => 'facility.name',
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

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/users/datatables?{$query}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'alias',
                    'email',
                    'deleted_at',
                    'role' => [
                        'name',
                    ],
                    'facility' => [
                        'name',
                    ],
                ],
            ],
        ]);
    }

    public function test_can_user_can_get_their_info()
    {
        $attrs = [
            'alias' => 'jdoe',
            'name' => 'John Doe',
            'email' => 'jdoe@example.com',
        ];

        $consumer = factory(User::class)->create($attrs);

        $response = $this->actingAs($consumer, 'api')->json('GET', "api/v1/users/{$consumer->id}");

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
            'facility' => [
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
            'role' => [
                'id',
                'facility_id',
                'name',
                'description',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
        ]);

        $response->assertJson($attrs);
    }

    public function test_can_get_any_user_info()
    {
        $attrs = [
            'alias' => 'jdoe',
            'name' => 'John Doe',
            'email' => 'jdoe@example.com',
        ];

        $user = factory(User::class)->create($attrs);

        // ...

        $consumer = factory(User::class)->create();

        $response = $this->actingAs($consumer, 'api')->json('GET', "api/v1/users/{$user->id}");

        $response->assertStatus(403);

        // ...

        $consumer = $this->getAuthorizedUser('view', 'users');

        $user->facility()->associate($consumer->facility);
        $user->save();

        $response = $this->actingAs($consumer, 'api')->json('GET', "api/v1/users/{$user->id}");

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
            'facility' => [
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
            'role' => [
                'id',
                'facility_id',
                'name',
                'description',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
        ]);

        $response->assertJson($attrs);
    }

    public function test_cant_create_a_user_with_unknown_role()
    {
        $consumer = factory(User::class)->create();

        $response = $this->actingAs($consumer, 'api')->json('POST', 'api/v1/users', []);

        $response->assertStatus(403);

        // ...

        $consumer = $this->getAuthorizedUser('create', 'users');

        $unrelatedRole = factory(Role::class)->create();

        $attrs = [
            'alias' => 'jdoe',
            'name' => 'John Doe',
            'email' => 'jdoe@example.com',
            'role_id' => $unrelatedRole->id,
        ];

        $response = $this->actingAs($consumer, 'api')->json('POST', 'api/v1/users', $attrs);

        $response->assertStatus(422);

        $response->assertJsonStructure([
            'message',
            'errors' => [
                'role_id',
            ],
        ]);
    }

    public function test_can_create_a_user()
    {
        $consumer = $this->getAuthorizedUser('create', 'users');

        $role = factory(Role::class)->create([
            'facility_id' => $consumer->facility_id,
        ]);

        $attrs = [
            'alias' => 'jdoe',
            'name' => 'John Doe',
            'email' => 'jdoe@example.com',
            'role_id' => $role->id,
        ];

        $response = $this->actingAs($consumer, 'api')->json('POST', 'api/v1/users', $attrs);

        $response->assertStatus(201);

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
            'facility' => [
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
            'role' => [
                'id',
                'facility_id',
                'name',
                'description',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
        ]);

        $response->assertJson($attrs);
    }

    public function test_can_update_user_their_details()
    {
        $consumer = factory(User::class)->create();

        $attrs = [
            'alias' => 'jdoe',
            'name' => 'John Doe',
            'email' => 'jdoe@example.com',
        ];

        $response = $this->actingAs($consumer, 'api')->json('PUT', "api/v1/users/{$consumer->id}", $attrs);

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
        ]);

        $response->assertJson($attrs);
    }

    public function test_cant_update_a_user_with_unknown_role()
    {
        $consumer = $this->getAuthorizedUser('update', 'users');

        $user = factory(User::class)->create([
            'facility_id' => $consumer->facility_id,
        ]);

        $unrelatedRole = factory(Role::class)->create();

        $attrs = [
            'alias' => 'jdoe',
            'name' => 'John Doe',
            'email' => 'jdoe@example.com',
            'role_id' => $unrelatedRole->id,
        ];

        $response = $this->actingAs($consumer, 'api')->json('PUT', "api/v1/users/{$user->id}", $attrs);

        $response->assertStatus(422);

        $response->assertJsonStructure([
            'message',
            'errors' => [
                'role_id',
            ],
        ]);
    }

    public function test_can_update_any_user_details()
    {
        $consumer = $this->getAuthorizedUser('update', 'users');

        $user = factory(User::class)->create([
            'facility_id' => $consumer->facility_id,
        ]);

        $role = factory(Role::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $attrs = [
            'alias' => 'jdoe',
            'name' => 'John Doe',
            'email' => 'jdoe@example.com',
            'role_id' => $role->id,
        ];

        $response = $this->actingAs($consumer, 'api')->json('PUT', "api/v1/users/{$user->id}", $attrs);

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
        ]);

        $response->assertJson($attrs);
    }

    public function test_can_revoke_specified_user()
    {
        $consumer = $this->getAuthorizedUser('soft-delete', 'users');

        $user = factory(User::class)->create([
            'facility_id' => $consumer->facility_id,
        ]);

        $response = $this->actingAs($consumer, 'api')->json('PUT', "api/v1/users/{$user->id}/revoke");

        // what happens when a user is soft deleted?
        // api - revoke associated access tokens
        // web - require user to login again

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
        ]);

        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }

    public function test_cant_restore_non_revoked_user()
    {
        $consumer = $this->getAuthorizedUser('restore', 'users');

        $user = factory(User::class)->create([
            'facility_id' => $consumer->facility_id,
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($consumer, 'api')->json('PUT', "api/v1/users/{$user->id}/restore");

        $response->assertStatus(404);

        $response->assertJsonStructure([
            'message',
        ]);
    }

    public function test_can_restore_revoked_user()
    {
        $consumer = $this->getAuthorizedUser('restore', 'users');

        $user = factory(User::class)->create([
            'facility_id' => $consumer->facility_id,
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        // do passport enforce softdelete?
        // This should return 401 since the user is soft deleted
        // Can a revoke user login?

        $response = $this->actingAs($consumer, 'api')->json('PUT', "api/v1/users/{$user->id}/restore");

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
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'deleted_at' => null,
        ]);
    }

    public function test_cant_delete_non_revoked_user()
    {
        $consumer = $this->getAuthorizedUser('force-delete', 'users');

        $user = factory(User::class)->create([
            'facility_id' => $consumer->facility_id,
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($consumer, 'api')->json('DELETE', "api/v1/users/{$user->id}");

        $response->assertStatus(404);

        $response->assertJsonStructure([
            'message',
        ]);
    }

    public function test_can_delete_revoked_user()
    {
        $consumer = $this->getAuthorizedUser('force-delete', 'users');

        $user = factory(User::class)->create([
            'facility_id' => $consumer->facility_id,
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        $response = $this->actingAs($consumer, 'api')->json('DELETE', "api/v1/users/{$user->id}");

        $response->assertStatus(204);

        $this->assertEquals('', $response->getContent());

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_a_user_can_confirm_their_password()
    {
        $user = factory(User::class)->create([
            'password' => Hash::make('correct-password'),
        ]);

        // ...

        $response = $this->actingAs($user, 'api')->json('POST', 'api/v1/users/password', [
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);

        $response->assertJsonStructure([
            'message',
            'errors' => [
                'password',
            ],
        ]);

        // ...

        $response = $this->actingAs($user, 'api')->json('POST', 'api/v1/users/password', [
            'password' => 'correct-password',
        ]);

        $response->assertStatus(204);

        $this->assertEquals('', $response->getContent());
    }

    public function test_a_user_can_change_their_password()
    {
        $user = factory(User::class)->create([
            'password' => Hash::make('current-password'),
        ]);

        // ...

        $response = $this->actingAs($user, 'api')->json('PUT', 'api/v1/users/password', [
            'password' => 'wrong-current-password',
            'new_password' => 'new-password',
            'new_password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(422);

        $response->assertJsonStructure([
            'message',
            'errors' => [
                'password',
            ],
        ]);

        // ...

        $response = $this->actingAs($user, 'api')->json('PUT', 'api/v1/users/password', [
            'password' => 'current-password',
            'new_password' => 'new-password',
            'new_password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(204);

        $this->assertEquals('', $response->getContent());

        $user->refresh();

        $this->assertTrue(password_verify('new-password', $user->password));
    }

    /**
     * Create client credentials grant client app.
     *
     * @return void
     */
    protected function createClient()
    {
        $client = new \App\Models\Client();
        $client->id = Uuid::uuid4()->toString();
        $client->user_id = null;
        $client->name = 'test-client-grant-client';
        $client->secret = Str::random('40');
        $client->redirect = '';
        $client->personal_access_client = false;
        $client->password_client = false;
        $client->revoked = false;
        $client->save();

        return $client;
    }

    /**
     * @see https://laravel.com/docs/6.x/passport#testing
     */
    public function test_an_app_can_validate_a_user_by_email()
    {
        Passport::actingAsClient($this->createClient(), ['validate-email']);

        // ...

        $user = factory(User::class)->create([
            'email' => 'correct@example.com',
        ]);

        // ...

        $response = $this->json('POST', 'api/v1/users/email', [
            'email' => 'wrong@example.com',
        ]);

        $response->assertStatus(422);

        $response->assertJsonStructure([
            'message',
            'errors' => [
                'email',
            ],
        ]);

        // ...

        $response = $this->json('POST', 'api/v1/users/email', [
            'email' => 'correct@example.com',
        ]);

        $response->assertStatus(204);

        $this->assertEquals('', $response->getContent());
    }

    public function test_an_app_can_confirm_user_email_verification()
    {
        Passport::actingAsClient($this->createClient(), ['confirm-email']);

        // ...

        $user = factory(User::class)->create([
            'email' => 'correct@example.com',
        ]);

        // ...

        $response = $this->json('PUT', 'api/v1/users/email', [
            'email' => 'wrong@example.com',
        ]);

        $response->assertStatus(422);

        $response->assertJsonStructure([
            'message',
            'errors' => [
                'email',
            ],
        ]);

        // ...

        $response = $this->json('PUT', 'api/v1/users/email', [
            'email' => 'correct@example.com',
        ]);

        $response->assertStatus(204);

        $this->assertEquals('', $response->getContent());

        $user->refresh();

        $this->assertNotNull($user->email_verified_at);
    }

    public function test_an_app_can_reset_forgotten_user_password()
    {
        Passport::actingAsClient($this->createClient(), ['reset-password']);

        // ...

        $user = factory(User::class)->create([
            'email' => 'jdoe@example.com',
            'password' => Hash::make('forgotten_pswd'),
        ]);

        // ...

        $response = $this->json('PUT', 'api/v1/users/password/reset', [
            'email' => 'wrong@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(422);

        $response->assertJsonStructure([
            'message',
            'errors' => [
                'email',
            ],
        ]);

        // ...

        $response = $this->json('PUT', 'api/v1/users/password/reset', [
            'email' => 'jdoe@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(204);

        $this->assertEquals('', $response->getContent());

        $user->refresh();

        $this->assertTrue(password_verify('new-password', $user->password));
    }
}
