<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\PersonalAccessClient;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @see \App\Http\Controllers\Web\PersonalAccessClientController
 */
class PersonalAccessClientControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_visit_index()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->from(route('clients.index'))
            ->get(route('clients.personal.index'));

        $response->assertStatus(200);
    }

    public function test_can_not_see_clients_created_by_others()
    {
        $this->withExceptionHandling();

        $this->actingAs(factory(User::class)->create());

        $client = factory(Client::class)->create(['personal_access_client' => true]);

        $this->from(route('clients.index'))
            ->get(route('clients.personal.index'))
            ->assertDontSee($client->id)
            ->assertStatus(200);

        $this->get(route('clients.personal.show', $client->id))->assertStatus(404);
    }

    public function test_can_see_client_details()
    {
        $user = factory(User::class)->create();

        $client = factory(Client::class)->create([
            'user_id' => $user->id,
            'personal_access_client' => true,
        ]);

        $personal_client = factory(PersonalAccessClient::class)->create([
            'client_id' => $client->id,
        ]);

        $response = $this->actingAs($user)
            ->from(route('clients.personal.index'))
            ->get(route('clients.personal.show', $personal_client->id));

        $response->assertStatus(200);
    }

    public function test_can_register_client()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->from(route('clients.personal.create'))
            ->post(route('clients.personal.store'), [
                'name' => 'Personal Client',
            ]);

        $this->assertDatabaseHas((new Client)->getTable(), [
            'user_id' => $user->id,
            'name' => 'Personal Client',
            'redirect' => '',
            'password_client' => false,
            'personal_access_client' => true,
            'revoked' => false,
        ]);

        $client = Client::first();

        $this->assertDatabaseHas((new PersonalAccessClient)->getTable(), [
            'client_id' => $client->id,
        ]);

        $personal_client = PersonalAccessClient::first();

        $response->assertRedirect(route('clients.personal.show', $personal_client->id));
    }

    public function test_can_see_edit_client()
    {
        $user = factory(User::class)->create();

        $client = factory(Client::class)->create([
            'user_id' => $user->id,
            'personal_access_client' => true,
        ]);

        $personal_client = factory(PersonalAccessClient::class)->create([
            'client_id' => $client->id,
        ]);

        $response = $this->actingAs($user)
            ->from(route('clients.personal.index'))
            ->get(route('clients.personal.edit', $personal_client->id));

        $response->assertStatus(200);
    }

    public function test_can_update_client()
    {
        $user = factory(User::class)->create();

        $client = factory(Client::class)->create([
            'user_id' => $user->id,
            'personal_access_client' => true,
        ]);

        $personal_client = factory(PersonalAccessClient::class)->create([
            'client_id' => $client->id,
        ]);

        $response = $this->actingAs($user)
            ->from(route('clients.personal.edit', $personal_client->id))
            ->put(route('clients.personal.update', $personal_client->id), [
                'name' => 'Personal Client',
            ]);

        $this->assertDatabaseHas((new Client)->getTable(), [
            'user_id' => $user->id,
            'name' => 'Personal Client',
            'password_client' => false,
            'personal_access_client' => true,
            'redirect' => '',
        ]);

        $response->assertRedirect(route('clients.personal.show', $personal_client->id));
    }

    public function test_can_delete_client()
    {
        $user = factory(User::class)->create();

        $client = factory(Client::class)->create([
            'user_id' => $user->id,
            'personal_access_client' => true,
        ]);

        $personal_client = factory(PersonalAccessClient::class)->create([
            'client_id' => $client->id,
        ]);

        $response = $this->actingAs($user)
            ->from(route('clients.personal.index'))
            ->delete(route('clients.personal.destroy', $personal_client->id), []);

        $this->assertDatabaseMissing((new Client)->getTable(), [
            'id' => $client->id,
        ]);

        $this->assertDatabaseMissing((new PersonalAccessClient)->getTable(), [
            'client_id' => $client->id,
        ]);

        $response->assertStatus(204);
    }
}
