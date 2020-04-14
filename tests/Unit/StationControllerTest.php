<?php

namespace Tests\Unit;

use App\Models\Station;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\StationController
 */
class StationControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cant_get_stations_if_unauthorized()
    {
        $user = factory(User::class)->create();

        $station = factory(Station::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/stations');

        $response->assertStatus(403);
    }

    public function test_can_get_stations()
    {
        $user = $this->getAuthorizedUser('view-any', 'stations');

        $station = factory(Station::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/stations');

        $response->assertStatus(206);

        $response->assertJsonStructure([
            'current_page',
            'data' => [
                '*' => [
                    'id',
                    'facility_id',
                    'code',
                    'name',
                    'description',
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

        $response->assertJson([
            'data' => [
                [
                    'id' => $station->id,
                ],
            ],
        ]);
    }

    public function test_can_get_station_details()
    {
        $user = $this->getAuthorizedUser('view', 'stations');

        $station = factory(Station::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/stations/{$station->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'facility_id',
            'code',
            'name',
            'description',
            'facility',
        ]);
    }

    public function test_can_regsiter_a_station()
    {
        $user = $this->getAuthorizedUser('create', 'stations');

        $attrs = [
            'code' => 'GP',
            'name' => 'General Practitioner',
            'description' => 'General Practitioner Consultation',
        ];

        $response = $this->actingAs($user, 'api')->json('POST', 'api/v1/stations', $attrs);

        $response->assertStatus(201);

        $response->assertJsonStructure([
            'id',
            'facility_id',
            'code',
            'name',
            'description',
            'facility',
        ]);

        $attrs['facility_id'] = $user->facility_id;

        $response->assertJson($attrs);
    }

    public function test_can_update_station_info()
    {
        $user = $this->getAuthorizedUser('create', 'stations');

        $station = factory(Station::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $attrs = [
            'code' => 'GP',
            'name' => 'General Practitioner',
            'description' => 'General Practitioner Consultation',
        ];

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/stations/{$station->id}", $attrs);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'facility_id',
            'code',
            'name',
            'description',
            'facility',
        ]);

        $attrs['facility_id'] = $user->facility_id;

        $response->assertJson($attrs);
    }

    public function test_can_revoke_a_station()
    {
        $user = $this->getAuthorizedUser('soft-delete', 'stations');

        $station = factory(Station::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/stations/{$station->id}/revoke");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'facility_id',
            'code',
            'name',
            'description',
            'facility',
        ]);

        $this->assertSoftDeleted('stations', [
            'id' => $station->id,
        ]);
    }

    public function test_can_restore_a_revoked_station()
    {
        $user = $this->getAuthorizedUser('restore', 'stations');

        $station = factory(Station::class)->create([
            'facility_id' => $user->facility_id,
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/stations/{$station->id}/restore");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'facility_id',
            'code',
            'name',
            'description',
            'facility',
        ]);

        $this->assertDatabaseHas('stations', [
            'id' => $station->id,
            'deleted_at' => null,
        ]);
    }
}
