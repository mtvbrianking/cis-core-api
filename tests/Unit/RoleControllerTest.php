<?php

namespace Tests\Unit;

use App\Models\Facility;
use App\Models\Module;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\RoleController
 */
class RoleControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_get_roles()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/roles');

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view-any', 'roles');

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/roles');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'current_page',
            'data' => [
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

    public function test_can_get_non_paginated_roles()
    {
        $user = $this->getAuthorizedUser('view-any', 'roles');

        $query = http_build_query([
            'paginate' => 0,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/roles?{$query}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
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
    }

    public function test_can_get_roles_for_datatables()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/roles/datatables');

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view-any', 'roles');

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
                    'data' => 'description',
                    'name' => 'description',
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

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/roles/datatables?{$query}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'deleted_at',
                ],
            ],
        ]);
    }

    public function test_can_user_can_get_their_role_info()
    {
        $roleName = 'Role name';

        $attrs = [
            'name' => $roleName,
            'description' => 'Role description.',
        ];

        $role = factory(Role::class)->create($attrs);

        // ...

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/roles/{$role->id}");

        $response->assertStatus(403);

        // ...

        $role->facility()->associate($user->facility);
        $role->save();

        $user->role()->associate($role);
        $user->save();

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/roles/{$role->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'facility_id',
            'name',
            'description',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $attrs['name'] = Str::title($roleName);

        $response->assertJson($attrs);
    }

    public function test_can_get_any_role_info()
    {
        $roleName = 'Role name';

        $attrs = [
            'name' => $roleName,
            'description' => 'Role description.',
        ];

        $role = factory(Role::class)->create($attrs);

        // ...

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/roles/{$role->id}");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view', 'roles');

        $role->facility()->associate($user->facility);
        $role->save();

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/roles/{$role->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'facility_id',
            'name',
            'description',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $attrs['name'] = Str::title($roleName);

        $response->assertJson($attrs);
    }

    public function test_can_create_a_role()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('POST', 'api/v1/roles', []);

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('create', 'roles');

        $roleName = 'Role name';

        $attrs = [
            'name' => $roleName,
            'description' => 'Role description.',
        ];

        $response = $this->actingAs($user, 'api')->json('POST', 'api/v1/roles', $attrs);

        $response->assertStatus(201);

        $response->assertJsonStructure([
            'id',
            'facility_id',
            'name',
            'description',
            'created_at',
            'updated_at',
            'deleted_at',
            'facility',
        ]);

        $attrs['name'] = Str::title($roleName);
        $attrs['facility_id'] = $user->facility_id;

        $response->assertJson($attrs);
    }

    public function test_can_update_specified_role()
    {
        $role = factory(Role::class)->create([
            // 'facility_id' => $user->facility_id,
        ]);

        $roleName = 'Role name';

        $attrs = [
            'name' => $roleName,
            'description' => 'Role description.',
        ];

        // ...

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/roles/{$role->id}", []);

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('update', 'roles');

        $role->facility()->associate($user->facility);
        $role->save();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/roles/{$role->id}", $attrs);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'facility_id',
            'name',
            'description',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $attrs['name'] = Str::title($roleName);

        $response->assertJson($attrs);
    }

    public function test_can_revoke_specified_role()
    {
        $user = factory(User::class)->create();

        // ...

        $role = factory(Role::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/roles/{$role->id}/revoke");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('soft-delete', 'roles');

        $role->facility()->associate($user->facility);
        $role->save();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/roles/{$role->id}/revoke");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'name',
            'description',
            'facility_id',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $this->assertSoftDeleted('roles', [
            'facility_id' => $user->facility_id,
        ]);
    }

    public function test_cant_restore_non_revoked_role()
    {
        $user = factory(User::class)->create();

        // ...

        $role = factory(Role::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/roles/{$role->id}/restore");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('restore', 'roles');

        $role->facility()->associate($user->facility);
        $role->save();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/roles/{$role->id}/restore");

        $response->assertStatus(404);

        $response->assertJsonStructure([
            'message',
        ]);
    }

    public function test_can_restore_revoked_role()
    {
        $user = factory(User::class)->create();

        // ...

        $role = factory(Role::class)->create([
            'facility_id' => $user->facility_id,
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/roles/{$role->id}/restore");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('restore', 'roles');

        $role->facility()->associate($user->facility);
        $role->save();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/roles/{$role->id}/restore");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'facility_id',
            'name',
            'description',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $this->assertDatabaseHas('roles', [
            'deleted_at' => null,
        ]);
    }

    public function test_cant_delete_non_revoked_role()
    {
        $user = factory(User::class)->create();

        // ...

        // $role = factory(Role::class)->create([
        //     'facility_id' => $user->facility_id,
        // ]);

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/roles/{$user->role_id}");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('force-delete', 'roles');

        // $role->facility()->associate($user->facility);
        // $role->save();

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/roles/{$user->role_id}");

        $response->assertStatus(404);

        $response->assertJsonStructure([
            'message',
        ]);
    }

    public function test_can_delete_revoked_role()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/roles/{$user->role_id}");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('force-delete', 'roles');

        $role = factory(Role::class)->create([
            'facility_id' => $user->facility_id,
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/roles/{$role->id}");

        $response->assertStatus(204);

        $this->assertEquals('', $response->getContent());

        $this->assertDatabaseMissing('roles', [
            'id' => $role->id,
        ]);
    }

    public function test_cant_delete_non_orphaned_role()
    {
        $user = $this->getAuthorizedUser('force-delete', 'roles');

        $role = factory(Role::class)->create([
            'facility_id' => $user->facility_id,
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        // Dependant
        factory(User::class)->create([
            'facility_id' => $user->facility_id,
            'role_id' => $role->id,
        ]);

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/roles/{$role->id}");

        $response->assertStatus(400);

        $response->assertJsonStructure([
            'message',
        ]);

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
        ]);
    }

    public function test_can_get_permissions_for_a_specified_role()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/roles/{$user->role_id}/permissions");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view-any', 'permissions');

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/roles/{$user->role_id}/permissions");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'permissions' => [
                '*' => [
                    'id',
                    'module_name',
                    'description',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    public function test_can_get_granted_permissions_to_a_role()
    {
        // Can't access permissions granted to a role otherthan theirs if not authorized

        $user = factory(User::class)->create();

        $role = factory(Role::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/roles/{$role->id}/permissions/available");

        $response->assertStatus(403);

        // Can access permissions granted to any role if authorized

        $user = $this->getAuthorizedUser('assign-permissions', 'permissions');

        $role = factory(Role::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/roles/{$role->id}/permissions/available");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'permissions' => [
                '*' => [
                    'id',
                    'name',
                    'granted',
                    'module' => [
                        'name',
                        'category',
                    ],
                ],
            ],
        ]);
    }

    public function test_can_get_users_with_specified_role()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/roles/{$user->role_id}/users");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view-any', 'users');

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/roles/{$user->role_id}/users");

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

    public function test_can_assign_permissions_to_a_role()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/roles/{$user->role_id}/permissions/available", []);

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('assign-permissions', 'permissions');

        $module = factory(Module::class)->create([
            'name' => 'pharmacies',
        ]);

        $user->facility->modules()->attach($module);
        $user->facility->save();

        $permission = factory(Permission::class)->create([
            'module_name' => 'pharmacies',
            'name' => 'view-any',
        ]);

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/roles/{$user->role_id}/permissions/available", [
            'permissions' => [
                $permission->id,
            ],
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'facility_id',
            'name',
            'description',
            'created_at',
            'updated_at',
            'deleted_at',
            'permissions' => [
                '*' => [
                    'id',
                    'module_name',
                    'description',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);

        $this->assertDatabaseHas('role_permission', [
            'permission_id' => $permission->id,
            'role_id' => $user->role_id,
        ]);
    }

    public function test_cant_sync_unknown_permissions_permissions()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/roles/{$user->role_id}/permissions/available");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('assign-permissions', 'permissions');

        $role = factory(Role::class)->create();

        $randomPermissionId = time();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/roles/{$role->id}/permissions/available", [
            'permissions' => [
                $randomPermissionId,
            ],
        ]);

        $response->assertStatus(422);

        $response->assertJsonStructure([
            'message',
            'errors' => [
                'permissions',
            ],
        ]);

        $response->assertJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                'permissions' => [
                    "Unknown permissions: {$randomPermissionId}",
                ],
            ],
        ]);
    }
}
