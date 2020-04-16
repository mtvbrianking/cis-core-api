<?php

namespace Tests\Unit;

use App\Models\Station;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\StationUserController
 */
class StationUserControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_get_users_available_to_a_station()
    {
        $non_granted_user = factory(User::class)->create();

        $station = factory(Station::class)->create([
            'facility_id' => $non_granted_user->facility_id,
        ]);

        $response = $this->actingAs($non_granted_user, 'api')->json('GET', "api/v1/stations/{$station->id}/users/available");

        $response->assertStatus(403);

        // ...

        $granted_user = $this->getAuthorizedUser('sync-station-users', 'stations');

        $station->facility()->associate($granted_user->facility);
        $station->save();

        $station->users()->sync($granted_user->id, true);
        $station->save();

        $response = $this->actingAs($granted_user, 'api')->json('GET', "api/v1/stations/{$station->id}/users/available");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'facility_id',
            'code',
            'name',
            'description',
            'users' => [
                '*' => [
                    'id',
                    'facility_id',
                    'role_id',
                    'alias',
                    'name',
                    'email',
                    'email_verified_at',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                    'granted',
                ],
            ],
        ]);

        $response->assertJson(
            Arr::sortRecursive([
                'id' => $station->id,
                'users' => [
                    [
                        'id' => $granted_user->id,
                        'granted' => true,
                    ],
                    [
                        'id' => $non_granted_user->id,
                        'granted' => false,
                    ],
                ],
            ])
        );
    }

    public function test_cant_sync_unrelated_station_users()
    {
        $user = $this->getAuthorizedUser('sync-station-users', 'stations');

        $station = factory(Station::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $non_related_user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/stations/{$station->id}/users/available", [
            'users' => [
                $non_related_user->id,
                $user->id,
            ],
        ]);

        $response->assertStatus(422);

        $response->assertJsonStructure([
            'message',
            'errors' => [
                'users',
            ],
        ]);

        $response->assertJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                'users' => [
                    "Unknown users: {$non_related_user->id}",
                ],
            ],
        ]);
    }

    public function test_can_sync_station_users()
    {
        $granted_user = $this->getAuthorizedUser('sync-station-users', 'stations');

        $station = factory(Station::class)->create([
            'facility_id' => $granted_user->facility_id,
        ]);

        $station->users()->sync($granted_user->id);

        $non_granted_user = factory(User::class)->create([
            'facility_id' => $station->facility_id,
        ]);

        $response = $this->actingAs($granted_user, 'api')->json('PUT', "api/v1/stations/{$station->id}/users/available", [
            'users' => [
                $non_granted_user->id,
            ],
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'id',
            'facility_id',
            'code',
            'name',
            'description',
            'users' => [
                '*' => [
                    'id',
                    'name',
                    'granted',
                ],
            ],
        ]);

        $response->assertJson(
            Arr::sortRecursive([
                'id' => $station->id,
                'users' => [
                    [
                        'id' => $granted_user->id,
                        'granted' => false,
                    ],
                    [
                        'id' => $non_granted_user->id,
                        'granted' => true,
                    ],
                ],
            ])
        );
    }
}
