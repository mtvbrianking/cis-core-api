<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @see \App\Http\Controllers\Web\ClientController
 */
class ClientControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_visit_index()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get(route('clients.index'));

        $response->assertStatus(200);
    }

    public function test_can_see_client_details()
    {
        $user = factory(User::class)->create();

        $client = factory(get_class(Passport::client()))->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('clients.show', $client->id));

        $response->assertStatus(200);
    }

    public function test_can_register_personal_client()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->from(route('clients.create'))
            ->post(route('clients.store'), [
                'name' => 'Personal Access Client',
                'password_client' => false,
                'personal_access_client' => true,
            ]);

        $this->assertDatabaseHas(Passport::client()->getTable(), [
            'user_id' => $user->id,
            'name' => 'Personal Access Client',
            'password_client' => false,
            'personal_access_client' => true,
            'revoked' => false,
        ]);

        $client = Passport::client()->first();

        $response->assertRedirect(route('clients.show', $client->id));
    }

    public function test_can_register_password_client()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->from(route('clients.create'))
            ->post(route('clients.store'), [
                'name' => 'Password Client',
                'password_client' => true,
                'personal_access_client' => false,
            ]);

        $this->assertDatabaseHas(Passport::client()->getTable(), [
            'user_id' => $user->id,
            'name' => 'Password Client',
            'password_client' => true,
            'personal_access_client' => false,
            'revoked' => false,
        ]);

        $client = Passport::client()->first();

        $response->assertRedirect(route('clients.show', $client->id));
    }

    public function test_can_register_authorization_code_client()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->from(route('clients.create'))
            ->post(route('clients.store'), [
                'name' => 'Authorization Code Client',
                'password_client' => false,
                'personal_access_client' => false,
                'redirect' => 'http://example/callback',
            ]);

        $this->assertDatabaseHas(Passport::client()->getTable(), [
            'user_id' => $user->id,
            'name' => 'Authorization Code Client',
            'redirect' => 'http://example/callback',
            'password_client' => false,
            'personal_access_client' => false,
            'revoked' => false,
        ]);

        $client = Passport::client()->first();

        $response->assertRedirect(route('clients.show', $client->id));
    }

    public function test_cant_register_authorization_code_client_without_redirect_uri()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->from(route('clients.create'))
            ->post(route('clients.store'), [
                'name' => 'OAuth Client',
                'password_client' => false,
                'personal_access_client' => false,
                'redirect' => null,
            ]);

        $this->assertEquals(0, Passport::client()->count());

        $response->assertRedirect(route('clients.create'));
        $response->assertSessionHasErrors('redirect');

        $this->assertTrue(session()->hasOldInput('name'));
        $this->assertTrue(session()->hasOldInput('password_client'));
        $this->assertTrue(session()->hasOldInput('personal_access_client'));
        $this->assertFalse(session()->hasOldInput('redirect'));
    }

    public function test_can_see_edit_client()
    {
        $user = factory(User::class)->create();

        $client = factory(get_class(Passport::client()))->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->from(route('clients.index'))
            ->get(route('clients.edit', $client->id));

        $response->assertStatus(200);
    }

    public function test_can_update_client()
    {
        $user = factory(User::class)->create();

        $client = factory(get_class(Passport::client()))->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->from(route('clients.edit', $client->id))
            ->put(route('clients.update', $client->id), [
                'name' => 'New OAuth Client',
                'password_client' => false,
                'personal_access_client' => false,
                'redirect' => 'http://example/callback',
            ]);

        $this->assertDatabaseHas(Passport::client()->getTable(), [
            'user_id' => $user->id,
            'name' => 'New OAuth Client',
            'password_client' => false,
            'personal_access_client' => false,
            'redirect' => 'http://example/callback',
        ]);

        $response->assertRedirect(route('clients.show', $client->id));
    }

    public function test_can_revoke_client()
    {
        $user = factory(User::class)->create();

        $client = factory(get_class(Passport::client()))->create([
            'user_id' => $user->id,
            'revoked' => false,
        ]);

        $response = $this->actingAs($user)
            ->from(route('clients.index'))
            ->put(route('clients.revoke', $client->id), []);

        $this->assertDatabaseHas(Passport::client()->getTable(), [
            'id' => $client->id,
            'revoked' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'user_id',
            'name',
            'redirect',
            'personal_access_client',
            'password_client',
            'revoked',
            'created_at',
            'updated_at',
        ]);
    }

    public function test_can_restore_client()
    {
        $user = factory(User::class)->create();

        $client = factory(get_class(Passport::client()))->create([
            'user_id' => $user->id,
            'revoked' => true,
        ]);

        $response = $this->actingAs($user)
            ->from(route('clients.index'))
            ->put(route('clients.restore', $client->id), []);

        $this->assertDatabaseHas(Passport::client()->getTable(), [
            'id' => $client->id,
            'revoked' => false,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'user_id',
            'name',
            'redirect',
            'personal_access_client',
            'password_client',
            'revoked',
            'created_at',
            'updated_at',
        ]);
    }

    public function test_can_delete_client()
    {
        $user = factory(User::class)->create();

        $client = factory(get_class(Passport::client()))->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->from(route('clients.index'))
            ->delete(route('clients.destroy', $client->id), []);

        $this->assertDatabaseMissing(Passport::client()->getTable(), [
            'id' => $client->id,
        ]);

        $response->assertStatus(204);
    }
}
