<?php

namespace Tests\Unit\Pharmacy;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Pharmacy\StoreController
 */
class StoreControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_stores()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/pharmacy/stores');

        $response->assertStatus(403);

        // ...

        $user = $this->getAuthorizedUser('view-any', 'pharm-stores');

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/pharmacy/stores');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'current_page',
            'data' => [
                '*' => [
                    'id',
                    'facility_id',
                    'name',
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

    public function test_can_get_non_paginated_stores()
    {
        $user = $this->getAuthorizedUser('view-any', 'pharm-stores');

        $query = http_build_query([
            'paginate' => 0,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/pharmacy/stores?{$query}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'stores' => [
                '*' => [
                    'id',
                    'facility_id',
                    'name',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
            ],
        ]);
    }
}
