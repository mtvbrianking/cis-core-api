<?php

namespace Tests\Unit;

use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\ModuleController
 */
class ModuleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_modules()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/modules');

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view-any', 'modules');

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/modules');

        $response->assertStatus(200);

        $response->assertJsonStructure([
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
    }

    public function test_can_get_specified_module()
    {
        $name = 'user';

        factory(Module::class)->create([
            'name' => $name,
            'description' => 'Users module',
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
            'description',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $response->assertJson([
            'name' => $slug_plural_name,
            'description' => 'Users module',
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
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', 'api/v1/modules/users', []);

        $response->assertStatus(403);

        // ...

        factory(Module::class)->create([
            'name' => 'users',
            'description' => 'Users module',
        ]);

        $user = $this->getAuthorizedUser('update', 'modules');

        $response = $this->actingAs($user, 'api')->json('PUT', 'api/v1/modules/users', [
            'description' => 'New users module desc',
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'name',
            'description',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $response->assertJson([
            'description' => 'New users module desc',
        ]);
    }

    public function test_can_revoke_specified_module()
    {
        factory(Module::class)->create([
            'name' => 'users',
            'description' => 'Users module',
        ]);

        // ...

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', 'api/v1/modules/users/revoke');

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('soft-delete', 'modules');

        $response = $this->actingAs($user, 'api')->json('PUT', 'api/v1/modules/users/revoke');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'name',
            'description',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $this->assertSoftDeleted('modules', [
            'name' => 'users',
        ]);
    }

    public function test_cant_restore_non_revoked_module()
    {
        factory(Module::class)->create([
            'name' => 'users',
            'description' => 'Users module',
        ]);

        // ...

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', 'api/v1/modules/users/restore');

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('restore', 'modules');

        $response = $this->actingAs($user, 'api')->json('PUT', 'api/v1/modules/users/restore');

        $response->assertStatus(404);

        $response->assertJsonStructure([
            'message',
        ]);
    }

    public function test_can_restore_revoked_module()
    {
        factory(Module::class)->create([
            'name' => 'users',
            'description' => 'Users module',
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        // ...

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', 'api/v1/modules/users/restore');

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('restore', 'modules');

        $response = $this->actingAs($user, 'api')->json('PUT', 'api/v1/modules/users/restore');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'name',
            'description',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $this->assertDatabaseHas('modules', [
            'name' => 'users',
            'deleted_at' => null,
        ]);
    }

    public function test_cant_delete_non_revoked_module()
    {
        factory(Module::class)->create([
            'name' => 'users',
            'description' => 'Users module',
        ]);

        // ...

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('DELETE', 'api/v1/modules/users');

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('force-delete', 'modules');

        $response = $this->actingAs($user, 'api')->json('DELETE', 'api/v1/modules/users');

        $response->assertStatus(404);

        $response->assertJsonStructure([
            'message',
        ]);
    }

    public function test_can_delete_revoked_module()
    {
        factory(Module::class)->create([
            'name' => 'users',
            'description' => 'Users module',
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        // ...

        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('DELETE', 'api/v1/modules/users');

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('force-delete', 'modules');

        $response = $this->actingAs($user, 'api')->json('DELETE', 'api/v1/modules/users');

        $response->assertStatus(204);

        $this->assertEquals('', $response->getContent());

        $this->assertDatabaseMissing('modules', [
            'name' => 'users',
        ]);
    }
}
