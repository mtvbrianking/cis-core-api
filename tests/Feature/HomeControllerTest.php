<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Web\HomeController
 */
class HomeControllerTest extends TestCase
{
    use DatabaseTransactions;

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

    public function test_can_see_application_routes()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get('/routes');

        $response->assertStatus(200);
    }
}
