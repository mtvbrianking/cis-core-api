<?php

namespace Tests\Unit;

use App\Models\Module;
use App\Models\Permission;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

/**
 * @see \App\Http\Controllers\PermissionController
 */
class PermissionControllerTest extends TestCase
{

    use RefreshDatabase;

    public function test_can_get_permissions()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/permissions');

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
            ]
        ]);
    }

    public function test_can_get_specified_permission()
    {
        $user = factory(User::class)->create();

        $name = 'sample permission';

        $permission = factory(Permission::class)->create([
            'name' => $name,
            'description' => 'Users permission.',
        ]);


        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/permissions/{$permission->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'module_name',
            'name',
            'description',
            'created_at',
            'updated_at',
        ]);

        $response->assertJson([
            'name'          => Str::slug($name),
            'description'   => 'Users permission.',
        ]);
    }

    public function test_can_create_a_permission()
    {
        $user = factory(User::class)->create();

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
        $user = factory(User::class)->create();

        $module = factory(Module::class)->create([
            'name'          => 'User module',
            'description'   => 'the users module',
        ]);

        $permission = factory(Permission::class)->create([
            'name' => 'users permission',
            'module_name' => $module->name,
            'description' => 'The Users permission.',
        ]);

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
        ]);

        $response->assertJson([
            'description' => 'New users permission desc',
        ]);
    }

    public function test_can_delete_a_permission()
    {
        $user = factory(User::class)->create();

        $permission = factory(Permission::class)->create([
            'name' => 'users permission',
            'description' => 'The Users permission',
        ]);

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/permissions/{$permission->id}");

        $response->assertStatus(204);

        $this->assertEquals('', $response->getContent());

        $this->assertDatabaseMissing('permissions', [
            'name' => 'users permission',
            'description' => 'The Users permission',
        ]);
    }
}
