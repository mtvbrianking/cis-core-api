<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
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
}
