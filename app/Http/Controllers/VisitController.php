<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VisitController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Register a new visit.
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
        $this->authorize('create', [Visit::class]);

        $consumer = Auth::guard('api')->user();

        $this->validate($request, [
            'patient_id' => [
                'required',
                'uuid',
                Rule::exists('patients', 'id')->where(function ($query) use ($consumer) {
                    $query->where('facility_id', $consumer->facility_id);
                }),
            ],
            'stations' => 'required|array',
            'stations.*.id' => 'required|uuid',
            'stations.*.user_id' => 'nullable|uuid',
            'stations.*.instructions' => 'required|string',
            'stations.*.status' => 'sometimes|in:scheduled,available',
            'stations.*.starts_at' => 'sometimes|date_format:Y-m-d H:i:s',
        ]);

        // Validate stations, staff.

        $validStations = Station::onlyRelated($consumer)->whereIn('id', array_column($request->stations, 'id'))->get();

        $validUsers = User::onlyRelated($consumer)->whereIn('id', array_column($request->stations, 'user_id'))->get();

        $errors = [];

        foreach ($request->stations as $idx => $reqStation) {
            $station = $validStations->where('id', $reqStation['id'])->first();

            if (! $station) {
                $errors["stations.{$idx}.id"][] = 'Unknown station.';
            }

            if (! $reqStation['user_id']) {
                continue;
            }

            $user = $validUsers->where('id', $reqStation['user_id'])->first();

            if (! $user) {
                $errors["stations.{$idx}.user_id"][] = 'Unknown user.';
            }
        }

        if ($errors) {
            $validator = Validator::make([], []);
            $validator->errors()->merge($errors);

            throw new ValidationException($validator);
        }

        // ...

        $visit = new Visit();
        $visit->patient_id = $request->patient_id;
        $visit->user_id = $consumer->id;
        $visit->save();

        $stationVisits = array_reduce($request->stations, function ($stationVisits, $station) {
            $stationVisits[$station['id']] = [
                'user_id' => $station['user_id'],
                'instructions' => $station['instructions'],
                'status' => array_get($station, 'status', 'scheduled'),
                'starts_at' => array_get($station, 'starts_at', date('Y-m-d H:i:s')),
            ];

            return $stationVisits;
        }, []);

        $visit->stations()->attach($stationVisits);

        $visit = Visit::with(['user', 'patient', 'stations'])->find($visit->id);

        return response($visit, 201);
    }
}
