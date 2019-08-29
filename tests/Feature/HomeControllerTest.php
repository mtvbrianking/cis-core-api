<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @see \App\Http\Controllers\Web\HomeController
 */
class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_cant_visit_home_unauthenticated()
    {
        $this->get('/')->assertStatus(302)->assertRedirect(route('login'));
    }

    public function test_can_visit_home()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
    }
}
