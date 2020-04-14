<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get patients.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('viewAny', [Patient::class]);

        $user = Auth::guard('api')->user();

        $query = Patient::onlyRelated($user)->withTrashed();

        return response($query->paginate(), 206);
    }

    /**
     * Get patient details.
     *
     * @param string $patientId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @return \Illuminate\Http\Response
     */
    public function show($patientId)
    {
        $this->authorize('view', [Patient::class]);

        $user = Auth::guard('api')->user();

        $patient = Patient::onlyRelated($user)->withTrashed()->with('facility')->findOrFail($patientId);

        return response($patient);
    }

    /**
     * Register a new patient.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $this->authorize('create', [Patient::class]);

        $this->validate($request, [
            'first_name' => 'required|max:25',
            'last_name' => 'required|max:25',
            'date_of_birth' => 'required|date_format:Y-m-d',
            'gender' => 'required|in:male,female',
            'phone' => 'sometimes|max:20',
            'email' => 'sometimes|max:100',
            'nin' => 'sometimes|size:14',
            'weight' => 'sometimes|numeric',
            'height' => 'sometimes|numeric',
            'blood_type' => 'sometimes|in:O+,A+,B+,AB+,O-,A-,B-,AB-',
            'existing_conditions' => 'sometimes|max:255',
            'allergies' => 'sometimes|max:255',
            'notes' => 'sometimes',
            'next_of_kin' => 'sometimes|max:255',
        ]);

        $user = Auth::guard('api')->user();

        $patient = new Patient();
        $patient->first_name = $request->first_name;
        $patient->last_name = $request->last_name;
        $patient->date_of_birth = $request->date_of_birth;
        $patient->gender = $request->gender;
        $patient->phone = $request->phone;
        $patient->email = $request->email;
        $patient->nin = $request->nin;
        $patient->weight = $request->weight;
        $patient->height = $request->height;
        $patient->blood_type = $request->blood_type;
        $patient->existing_conditions = $request->existing_conditions;
        $patient->allergies = $request->allergies;
        $patient->notes = $request->notes;
        $patient->next_of_kin = $request->next_of_kin;
        $patient->facility()->associate($user->facility);
        $patient->save();

        $patient->refresh();

        return response($patient, 201);
    }

    /**
     * Update patient info.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $patientId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $patientId)
    {
        $this->authorize('create', [Patient::class]);

        $user = Auth::guard('api')->user();

        $patient = Patient::onlyRelated($user)->with('facility')->findOrFail($patientId);

        $this->validate($request, [
            'first_name' => 'required|max:25',
            'last_name' => 'required|max:25',
            'date_of_birth' => 'required|date_format:Y-m-d',
            'gender' => 'required|in:male,female',
            'phone' => 'sometimes|max:20',
            'email' => 'sometimes|max:100',
            'nin' => 'sometimes|size:14',
            'weight' => 'sometimes|numeric',
            'height' => 'sometimes|numeric',
            'blood_type' => 'sometimes|in:O+,A+,B+,AB+,O-,A-,B-,AB-',
            'existing_conditions' => 'sometimes|max:255',
            'allergies' => 'sometimes|max:255',
            'notes' => 'sometimes',
            'next_of_kin' => 'sometimes|max:255',
        ]);

        $patient->first_name = $request->first_name;
        $patient->last_name = $request->last_name;
        $patient->date_of_birth = $request->date_of_birth;
        $patient->gender = $request->gender;
        $patient->phone = $request->phone;
        $patient->email = $request->email;
        $patient->nin = $request->nin;
        $patient->weight = $request->weight;
        $patient->height = $request->height;
        $patient->blood_type = $request->blood_type;
        $patient->existing_conditions = $request->existing_conditions;
        $patient->allergies = $request->allergies;
        $patient->notes = $request->notes;
        $patient->next_of_kin = $request->next_of_kin;
        $patient->save();

        return response($patient);
    }

    /**
     * Temporarily delete (ban) the specific patient.
     *
     * @param string $patientId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @return \Illuminate\Http\Response
     */
    public function revoke($patientId)
    {
        $this->authorize('softDelete', [Patient::class]);

        $user = Auth::guard('api')->user();

        $patient = Patient::onlyRelated($user)->with('facility')->findOrFail($patientId);

        $patient->delete();

        $patient->refresh();

        return response($patient);
    }

    /**
     * Restore the specific banned patient.
     *
     * @param string $patientId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @return \Illuminate\Http\Response
     */
    public function restore($patientId)
    {
        $this->authorize('restore', [Patient::class]);

        $user = Auth::guard('api')->user();

        $patient = Patient::onlyRelated($user)->onlyTrashed()->with('facility')->findOrFail($patientId);

        $patient->restore();

        $patient->refresh();

        return response($patient);
    }
}
