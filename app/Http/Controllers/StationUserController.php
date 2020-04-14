<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StationUserController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Query users available to a station.
     *
     * Indicating whether or not a user is granted to that station.
     *
     * @param \App\Models\Station $station
     *
     * @return \Illuminate\Support\Collection
     */
    protected function queryStationUsers(Station $station): Collection
    {
        $query = User::query();

        $query->leftJoin('station_user', function ($join) use ($station) {
            $join->on('users.id', '=', 'station_user.user_id');
            $join->where('station_user.station_id', '=', $station->id);
        });

        $query->select([
            'users.*',
            'station_user.station_id',
        ]);

        $users = $query->get();

        return $users->map(function ($user) {
            $user->granted = ! is_null($user->station_id);
            unset($user->station_id);

            return $user;
        });
    }

    /**
     * Users assigned to this station.
     *
     * @param string $stationId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function users($stationId)
    {
        $this->authorize('syncStationUsers', [Station::class]);

        $user = Auth::guard('api')->user();

        $station = Station::onlyRelated($user)->findOrFail($stationId);

        $station->users = $this->queryStationUsers($station);

        return response($station);
    }

    /**
     * Sync station users.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $stationId
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function syncUsers(Request $request, $stationId)
    {
        $this->authorize('syncStationUsers', [Station::class]);

        $user = Auth::guard('api')->user();

        $station = Station::onlyRelated($user)->findOrFail($stationId);

        $this->validate($request, [
            'users' => 'required|array',
            'users.*' => 'required|uuid',
        ]);

        // Available users

        $available_users = User::select('id')->where('facility_id', $station->facility_id)->pluck('id')->toArray();

        $unknown_users = array_values(array_diff($request->users, $available_users));

        if ($unknown_users) {
            $validator = Validator::make([], []);
            $validator->errors()->add('users', 'Unknown users: '.implode(', ', $unknown_users));

            throw new ValidationException($validator);
        }

        // Sync users...

        $station->users()->sync($request->users, true);
        $station->save();

        $station->users = $this->queryStationUsers($station);

        return response($station);
    }
}
