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
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get('/clients');

        $response->assertStatus(200);
    }

    public function test_can_see_client_details()
    {
        $user = factory(User::class)->create();

        $client = factory(get_class(Passport::client()))->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get("/clients/{$client->id}");

        $response->assertStatus(200);
    }
}
