<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Module;
use App\Models\Facility;
use App\Models\Permission;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @see \App\Http\Controllers\RoleController
 */
class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_roles()
    {
        $user = factory(User::class)->create();

        factory(Role::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/roles');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'roles' => [
                '*' => [
                    'id',
                    'facility_id',
                    'user_id',
                    'name',
                    'description',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
            ],
        ]);
    }

    public function test_can_get_specified_role()
    {
        $user = factory(User::class)->create();

        $roleName = 'Role name';

        $attrs = [
            'name' => $roleName,
            'description' => 'Role description.',
            'facility_id' => $user->facility_id,
        ];

        $role = factory(Role::class)->create($attrs);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/roles/{$role->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'facility_id',
            'user_id',
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
            'user_id',
            'name',
            'description',
            'created_at',
            'updated_at',
            'deleted_at',
            'creator',
            'facility',
        ]);

        $attrs['name'] = Str::title($roleName);
        $attrs['facility_id'] = $user->facility_id;

        $response->assertJson($attrs);
    }

    public function test_can_update_specified_role()
    {
        $user = factory(User::class)->create();

        $role = factory(Role::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $roleName = 'Role name';

        $attrs = [
            'name' => $roleName,
            'description' => 'Role description.',
        ];

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/roles/{$role->id}", $attrs);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'facility_id',
            'user_id',
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

        $role = factory(Role::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/roles/{$role->id}/revoke");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'name',
            'description',
            'facility_id',
            'user_id',
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

        $role = factory(Role::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/roles/{$role->id}/restore");

        $response->assertStatus(404);

        $response->assertJsonStructure([
            'message',
        ]);
    }

    public function test_can_restore_revoked_role()
    {
        $user = factory(User::class)->create();

        $role = factory(Role::class)->create([
            'facility_id' => $user->facility_id,
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/roles/{$role->id}/restore");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'facility_id',
            'user_id',
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

        $role = factory(Role::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/v1/roles/{$role->id}");

        $response->assertStatus(404);

        $response->assertJsonStructure([
            'message',
        ]);
    }

    public function test_can_delete_revoked_role()
    {
        $user = factory(User::class)->create();

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

    public function test_can_get_permissions_for_a_specified_role()
    {
        $user = factory(User::class)->create();

        $role = factory(Role::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/roles/{$role->id}/permissions");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'permissions' => [
                '*' => [
                    'id',
                    'user_id',
                    'module_name',
                    'description',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    public function test_can_assign_permissions_to_a_role()
    {
        $facility = factory(Facility::class)->create();

        $user = factory(User::class)->create([
            'facility_id' => $facility->id,
        ]);

        $module = factory(Module::class)->create();

        $facility->modules()->attach($module);
        $facility->save();

        $permission = factory(Permission::class)->create();

        $permission->module()->associate($module);
        $permission->save();

        $role = factory(Role::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/roles/{$role->id}/permissions", [
            'permissions' => [
                $permission->id,
            ],
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'user_id',
            'facility_id',
            'name',
            'description',
            'created_at',
            'updated_at',
            'deleted_at',
            'permissions' => [
                '*' => [
                    'id',
                    'user_id',
                    'module_name',
                    'description',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);

        $this->assertDatabaseHas('role_permission', [
            'permission_id' => $permission->id,
            'role_id' => $role->id,
        ]);
    }
}
