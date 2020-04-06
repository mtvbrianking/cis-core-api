<?php

namespace Tests\Unit;

use App\Models\Facility;
use App\Models\Module;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\ModuleController
 */
class ModuleControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_get_modules()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/modules');

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view-any', 'modules');

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/modules');

        $response->assertStatus(206);

        $response->assertJsonStructure([
            'current_page',
            'data' => [
                '*' => [
                    'name',
                    'category',
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

    public function test_can_get_non_paginated_modules()
    {
        $user = $this->getAuthorizedUser('view-any', 'modules');

        $query = http_build_query([
            'paginate' => 0,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/modules?{$query}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
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
    }

    public function test_can_get_modules_for_datatables()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/modules/datatables');

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view-any', 'modules');

        $query = http_build_query([
            'start' => 0,
            'length' => 10,
            'draw' => 1,
            'columns' => [
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
                    'data' => 'category',
                    'name' => 'category',
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

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/modules/datatables?{$query}");

        $response->assertStatus(206);

        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data' => [
                '*' => [
                    'name',
                    'category',
                    'deleted_at',
                ],
            ],
        ]);
    }

    public function test_can_get_specified_module()
    {
        $name = 'user';

        factory(Module::class)->create([
            'name' => $name,
        ]);

        $slug_plural_name = Str::slug(Str::plural($name));

        // ...

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/modules/{$slug_plural_name}");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view', 'modules');

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/modules/{$slug_plural_name}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'name',
            'category',
            'description',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $response->assertJson([
            'name' => $slug_plural_name,
        ]);
    }

    public function test_can_create_a_module()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('POST', 'api/v1/modules', []);

        $response->assertStatus(403);

        // ...

        $name = 'user';

        $user = $this->getAuthorizedUser('create', 'modules');

        $response = $this->actingAs($user, 'api')->json('POST', 'api/v1/modules', [
            'name' => $name,
            'description' => 'Users module',
        ]);

        $response->assertStatus(201);

        $response->assertJsonStructure([
            'name',
            'category',
            'description',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $slug_plural_name = Str::slug(Str::plural($name));

        $response->assertJson([
            'name' => $slug_plural_name,
            'description' => 'Users module',
        ]);
    }

    public function test_cant_create_module_with_existing_name()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('POST', 'api/v1/modules', []);

        $response->assertStatus(403);

        // ...

        $name = 'user';

        $user = $this->getAuthorizedUser('create', 'modules');

        factory(Module::class)->create([
            'name' => $name,
            'description' => 'Original users module',
        ]);

        $slug_plural_name = Str::slug(Str::plural($name));

        $response = $this->actingAs($user, 'api')->json('POST', 'api/v1/modules', [
            'name' => $slug_plural_name,
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

    public function test_can_update_specified_module()
    {
        $module = factory(Module::class)->create([
            'description' => 'Org desc',
        ]);

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/modules/{$module->name}", []);

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('update', 'modules');

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/modules/{$module->name}", [
            'description' => 'New desc',
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'name',
            'category',
            'description',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $response->assertJson([
            'description' => 'New desc',
        ]);
    }

    public function test_can_revoke_specified_module()
    {
        $module = factory(Module::class)->create();

        // ...

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/modules/{$module->name}/revoke");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('soft-delete', 'modules');

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/modules/{$module->name}/revoke");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'name',
            'category',
            'description',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $this->assertSoftDeleted('modules', [
            'name' => $module->name,
        ]);
    }

    public function test_cant_restore_non_revoked_module()
    {
        $module = factory(Module::class)->create();

        // ...

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/modules/{$module->name}/restore");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('restore', 'modules');

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/modules/{$module->name}/restore");

        $response->assertStatus(404);

        $response->assertJsonStructure([
            'message',
        ]);
    }

    public function test_can_restore_revoked_module()
    {
        $module = factory(Module::class)->create([
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        // ...

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/modules/{$module->name}/restore");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('restore', 'modules');

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/modules/{$module->name}/restore");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'name',
            'category',
            'description',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $this->assertDatabaseHas('modules', [
            'name' => $module->name,
            'deleted_at' => null,
        ]);
    }

    public function test_cant_delete_non_revoked_module()
    {
        $module = factory(Module::class)->create();

        // ...

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/modules/{$module->name}");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('force-delete', 'modules');

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/modules/{$module->name}");

        $response->assertStatus(404);

        $response->assertJsonStructure([
            'message',
        ]);
    }

    public function test_can_delete_revoked_module()
    {
        $module = factory(Module::class)->create([
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        // ...

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/modules/{$module->name}");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('force-delete', 'modules');

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/modules/{$module->name}");

        $response->assertStatus(204);

        $this->assertEquals('', $response->getContent());

        $this->assertDatabaseMissing('modules', [
            'name' => $module->name,
        ]);
    }

    public function test_can_delete_non_orphaned_module()
    {
        $module = factory(Module::class)->create([
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        // Dependant
        factory(Permission::class)->create([
            'module_name' => $module->name,
        ]);

        $user = $this->getAuthorizedUser('force-delete', 'modules');

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/modules/{$module->name}");

        $response->assertStatus(400);

        $response->assertJsonStructure([
            'message',
        ]);

        $this->assertDatabaseHas('modules', [
            'name' => $module->name,
        ]);
    }

    public function test_can_get_module_permissions()
    {
        $module = factory(Module::class)->create();

        $permission = factory(Permission::class)->create([
            'module_name' => $module->name,
        ]);

        // ...

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/modules/{$module->name}/permissions");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view-any', 'permissions');

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/modules/{$module->name}/permissions");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'name',
            'category',
            'description',
            'created_at',
            'updated_at',
            'deleted_at',
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

        $response->assertJson([
            'name' => $module->name,
            'permissions' => [
                [
                    'name' => $permission->name,
                ],
            ],
        ]);
    }

    public function test_can_get_module_facilities()
    {
        $module = factory(Module::class)->create();

        $facility = factory(Facility::class)->create();

        $facility->modules()->attach($module);

        // ...

        $user = factory(User::class)->create([
            // 'facility_id' => $facility->id,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/modules/{$module->name}/facilities");

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view-any', 'facilities');

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/modules/{$module->name}/facilities");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'name',
            'category',
            'description',
            'created_at',
            'updated_at',
            'deleted_at',
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

        $response->assertJson([
            'name' => $module->name,
            'facilities' => [
                [
                    'id' => $facility->id,
                ],
            ],
        ]);
    }
}
