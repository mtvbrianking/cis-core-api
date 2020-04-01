<?php

namespace Tests\Unit;

use App\Models\Module;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\PermissionController
 */
class PermissionControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_get_permissions()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/permissions');

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view-any', 'permissions');

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/permissions');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'current_page',
            'data' => [
                '*' => [
                    'id',
                    'module_name',
                    'name',
                    'description',
                    'created_at',
                    'updated_at',
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

    public function test_can_get_non_paginated_permissions()
    {
        $user = $this->getAuthorizedUser('view-any', 'permissions');

        $query = http_build_query([
            'paginate' => 0,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/permissions?{$query}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'permissions' => [
                '*' => [
                    'id',
                    'module_name',
                    'name',
                    'description',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    public function test_can_get_permissions_for_datatables()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/permissions/datatables');

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view-any', 'permissions');

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
                    'data' => 'module_name',
                    'name' => 'module_name',
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

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/permissions/datatables?{$query}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'module_name',
                ],
            ],
        ]);
    }

    public function test_can_get_specified_permission()
    {
        $name = 'sample permission';

        $permission = factory(Permission::class)->create([
            'name' => $name,
            'description' => 'Users permission.',
        ]);

        // ...

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/permissions/{$permission->id}");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view', 'permissions');

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/permissions/{$permission->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'module_name',
            'name',
            'description',
            'created_at',
            'updated_at',
            'module' => [
                'name',
                'category',
                'description',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
        ]);

        $response->assertJson([
            'name'          => Str::slug($name),
            'description'   => 'Users permission.',
        ]);
    }

    public function test_can_create_a_permission()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('POST', 'api/v1/permissions');

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('create', 'permissions');

        $module = factory(Module::class)->create([
            'name' => 'User module',
            'description' => 'the users module',
        ]);

        $name = 'Users permission.';

        $response = $this->actingAs($user, 'api')->json('POST', 'api/v1/permissions', [
            'name' => $name,
            'module_name' => $module->name,
            'description' => 'Users permission',
        ]);

        $response->assertStatus(201);

        $response->assertJsonStructure([
            'name',
            'module_name',
            'description',
            'created_at',
            'updated_at',
            'module' => [
                'name',
                'category',
                'description',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
        ]);

        $response->assertJson([
            'name' => Str::slug($name),
            'module_name' => $module->name,
            'description' => 'Users permission',
        ]);
    }

    public function test_cant_create_a_duplicate_permission_name_on_a_module()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('POST', 'api/v1/permissions', []);

        $response->assertStatus(403);

        // ...

        $name = 'Users permission.';

        $module = factory(Module::class)->create([
            'name' => 'User module',
            'description' => 'the users module',
        ]);

        factory(Permission::class)->create([
            'name'          => $name,
            'module_name'   => $module->name,
            'description'   => 'Original permission on user-module',
        ]);

        $slug_name = Str::slug($name);

        $user = $this->getAuthorizedUser('create', 'permissions');

        $response = $this->actingAs($user, 'api')->json('POST', 'api/v1/permissions', [
            'name' => $slug_name,
            'module_name' => $module->name,
            'description' => 'Duplicate permission on user-module',
        ]);

        $response->assertStatus(422);

        $response->assertJsonStructure([
            'message',
            'errors' => [
                'name',
            ],
        ]);

        $response->assertJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                'name' => [
                    'The name has already been taken.',
                ],
            ],
        ]);
    }

    public function test_can_update_specified_permission()
    {
        $module = factory(Module::class)->create([
            'name'          => 'User module',
            'description'   => 'the users module',
        ]);

        $permission = factory(Permission::class)->create([
            'name' => 'users permission',
            'module_name' => $module->name,
            'description' => 'The Users permission.',
        ]);

        // ...

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/permissions/{$permission->id}");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('update', 'permissions');

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/permissions/{$permission->id}", [
            'name' => 'users permission',
            'module_name' => $module->name,
            'description' => 'New users permission desc',
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'name',
            'module_name',
            'description',
            'created_at',
            'updated_at',
            'module' => [
                'name',
                'category',
                'description',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
        ]);

        $response->assertJson([
            'description' => 'New users permission desc',
        ]);
    }

    public function test_update_validates_unique_module_permission()
    {
        $module = factory(Module::class)->create([
            'name' => 'users',
        ]);

        $viewAnyPerm = factory(Permission::class)->create([
            'name' => 'view-any',
            'module_name' => $module->name,
        ]);

        $viewPerm = factory(Permission::class)->create([
            'name' => 'view',
            'module_name' => $module->name,
        ]);

        // ...

        $user = $this->getAuthorizedUser('update', 'permissions');

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/permissions/{$viewPerm->id}", [
            'name' => $viewAnyPerm->name,
            'module_name' => $module->name,
        ]);

        $response->assertStatus(422);

        $response->assertJsonStructure([
            'message',
            'errors' => [
                'name',
            ],
        ]);

        $response->assertJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                'name' => [
                    'The name has already been taken.',
                ],
            ],
        ]);
    }

    public function test_can_delete_a_permission()
    {
        $permission = factory(Permission::class)->create([
            'name' => 'users permission',
            'description' => 'The Users permission',
        ]);

        // ...

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/permissions/{$permission->id}");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('delete', 'permissions');

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/permissions/{$permission->id}");

        $response->assertStatus(204);

        $this->assertEquals('', $response->getContent());

        $this->assertDatabaseMissing('permissions', [
            'name' => 'users permission',
            'description' => 'The Users permission',
        ]);
    }

    public function test_can_get_permission_roles()
    {
        $permission = factory(Permission::class)->create();

        $user = factory(User::class)->create([
            // 'role_id' => $role->id,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/permissions/{$permission->id}/roles");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view-any', 'roles');

        // $user->role->permissions()->attach($permission);

        $permission->roles()->attach($user->role_id);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/permissions/{$permission->id}/roles");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'module_name',
            'name',
            'description',
            'created_at',
            'updated_at',
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
            'id' => $permission->id,
            'roles' => [
                [
                    'id' => $user->role_id,
                ],
            ],
        ]);
    }
}
