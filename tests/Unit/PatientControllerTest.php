<?php

namespace Tests\Unit;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\PatientController
 */
class PatientControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cant_get_patients_if_unauthorized()
    {
        $user = factory(User::class)->create();

        $patient = factory(Patient::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/patients');

        $response->assertStatus(403);
    }

    public function test_can_get_patients()
    {
        $user = $this->getAuthorizedUser('view-any', 'patient');

        $patient = factory(Patient::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', 'api/v1/patients');

        $response->assertStatus(206);

        $response->assertJsonStructure([
            'current_page',
            'data' => [
                '*' => [
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
                    'id' => $patient->id,
                ],
            ],
        ]);
    }

    public function test_can_get_patient_details()
    {
        $user = $this->getAuthorizedUser('view', 'patients');

        $patient = factory(Patient::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $response = $this->actingAs($user, 'api')->json('GET', "api/v1/patients/{$patient->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
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
            'facility',
        ]);
    }

    public function test_can_regsiter_a_patient()
    {
        $user = $this->getAuthorizedUser('create', 'patients');

        $attrs = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'date_of_birth' => '1970-01-01',
            'gender' => 'male',
        ];

        $response = $this->actingAs($user, 'api')->json('POST', 'api/v1/patients', $attrs);

        $response->assertStatus(201);

        $response->assertJsonStructure([
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
            'facility',
        ]);

        $attrs['facility_id'] = $user->facility_id;

        $response->assertJson($attrs);
    }

    public function test_can_update_patient_info()
    {
        $this->withoutExceptionHandling();

        $user = $this->getAuthorizedUser('create', 'patients');

        $patient = factory(Patient::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $attrs = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'date_of_birth' => '1970-01-01',
            'gender' => 'male',
        ];

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/patients/{$patient->id}", $attrs);

        $response->assertStatus(200);

        $response->assertJsonStructure([
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
            'facility',
        ]);

        $attrs['facility_id'] = $user->facility_id;

        $response->assertJson($attrs);
    }

    public function test_can_revoke_a_patient()
    {
        $user = $this->getAuthorizedUser('soft-delete', 'patients');

        $patient = factory(Patient::class)->create([
            'facility_id' => $user->facility_id,
        ]);

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/patients/{$patient->id}/revoke");

        $response->assertStatus(200);

        $response->assertJsonStructure([
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
            'facility',
        ]);

        $this->assertSoftDeleted('patients', [
            'id' => $patient->id,
        ]);
    }

    public function test_can_restore_a_revoked_patient()
    {
        $user = $this->getAuthorizedUser('restore', 'patients');

        $patient = factory(Patient::class)->create([
            'facility_id' => $user->facility_id,
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        $response = $this->actingAs($user, 'api')->json('PUT', "api/v1/patients/{$patient->id}/restore");

        $response->assertStatus(200);

        $response->assertJsonStructure([
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
            'facility',
        ]);

        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'deleted_at' => null,
        ]);
    }
}
