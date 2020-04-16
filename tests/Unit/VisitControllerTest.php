<?php

namespace Tests\Unit;

use App\Models\Patient;
use App\Models\Station;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\VisitController
 */
class VisitControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_make_a_visit_with_an_invalid_station()
    {
        $user = $this->getAuthorizedUser('create', 'visits');

        $patient = factory(Patient::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $station = factory(Station::class)->create([
            // 'facility_id' => $user->facility_id,
        ]);

        $now = date('Y-m-d H:i:s');

        $response = $this->actingAs($user, 'api')->json('POST', 'api/v1/visits', [
            'patient_id' => $patient->id,
            'stations' => [
                [
                    'id' => $station->id,
                    'user_id' => null,
                    'instructions' => 'Lorem ipsum dolor sit amet.',
                    'status' => 'scheduled',
                    'starts_at' => $now,
                ],
            ],
        ]);

        $response->assertStatus(422);

        $response->assertJsonStructure([
            'message',
            'errors' => [
                'stations.0.id',
            ],
        ]);
    }

    public function test_can_make_a_visit_with_an_invalid_staff()
    {
        $user = $this->getAuthorizedUser('create', 'visits');

        $patient = factory(Patient::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $station = factory(Station::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $staff = factory(User::class)->create();

        $now = date('Y-m-d H:i:s');

        $response = $this->actingAs($user, 'api')->json('POST', 'api/v1/visits', [
            'patient_id' => $patient->id,
            'stations' => [
                [
                    'id' => $station->id,
                    'user_id' => $staff->id,
                    'instructions' => 'Lorem ipsum dolor sit amet.',
                    'status' => 'scheduled',
                    'starts_at' => $now,
                ],
            ],
        ]);

        $response->assertStatus(422);

        $response->assertJsonStructure([
            'message',
            'errors' => [
                'stations.0.user_id',
            ],
        ]);
    }

    public function test_can_make_a_visit()
    {
        // $this->withoutExceptionHandling();

        $user = $this->getAuthorizedUser('create', 'visits');

        $patient = factory(Patient::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $station = factory(Station::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $now = date('Y-m-d H:i:s');

        $response = $this->actingAs($user, 'api')->json('POST', 'api/v1/visits', [
            'patient_id' => $patient->id,
            'stations' => [
                [
                    'id' => $station->id,
                    'user_id' => $user->id,
                    'instructions' => 'Lorem ipsum dolor sit amet.',
                    'status' => 'scheduled',
                    'starts_at' => $now,
                ],
            ],
        ]);

        $response->assertStatus(201);

        $response->assertJsonStructure([
            'id',
            'patient_id',
            'user_id',
            'created_at',
            'updated_at',
            'user' => [
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
            ],
            'patient' => [
                'id',
                'facility_id',
                'first_name',
                'last_name',
                'date_of_birth',
                'gender',
                'phone',
                'email',
                'nin',
                'weight',
                'height',
                'blood_type',
                'existing_conditions',
                'allergies',
                'notes',
                'next_of_kin',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'stations' => [
                '*' => [
                    'id',
                    'facility_id',
                    'code',
                    'name',
                    'description',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                    'pivot' => [
                        'visit_id',
                        'station_id',
                        'user_id',
                        'status',
                        'instructions',
                        'starts_at',
                        'accepted_at',
                        'concluded_at',
                        'canceled_at',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ],
        ]);

        $response->assertJson([
            'user_id' => $user->id,
            'patient_id' => $patient->id,
            'stations' => [
                [
                    'id' => $station->id,
                    'pivot' => [
                        // 'visit_id' => $visit->id,
                        'station_id' => $station->id,
                        'user_id' => $user->id,
                        'instructions' => 'Lorem ipsum dolor sit amet.',
                        'status' => 'scheduled',
                        'starts_at' => $now,
                    ],
                ],
            ],
        ]);
    }
}
