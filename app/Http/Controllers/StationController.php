<?php

namespace App\Http\Controllers;

use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StationController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get stations.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('viewAny', [Station::class]);

        $user = Auth::guard('api')->user();

        $query = Station::onlyRelated($user)->withTrashed();

        return response($query->paginate(), 206);
    }

    /**
     * Get station details.
     *
     * @param string $stationId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @return \Illuminate\Http\Response
     */
    public function show($stationId)
    {
        $this->authorize('view', [Station::class]);

        $user = Auth::guard('api')->user();

        $station = Station::onlyRelated($user)->withTrashed()->with('facility')->findOrFail($stationId);

        return response($station);
    }

    /**
     * Register a new station.
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
        $this->authorize('create', [Station::class]);

        $this->validate($request, [
            'code' => 'required|max:10',
            'name' => 'required|max:100',
            'description' => 'sometimes|max:255',
        ]);

        $user = Auth::guard('api')->user();

        $station = new Station();
        $station->code = $request->code;
        $station->name = $request->name;
        $station->description = $request->description;
        $station->facility()->associate($user->facility);
        $station->save();

        $station->refresh();

        return response($station, 201);
    }

    /**
     * Update station info.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $stationId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $stationId)
    {
        $this->authorize('create', [Station::class]);

        $user = Auth::guard('api')->user();

        $station = Station::onlyRelated($user)->with('facility')->findOrFail($stationId);

        $this->validate($request, [
            'code' => 'required|max:10',
            'name' => 'required|max:100',
            'description' => 'sometimes|max:255',
        ]);

        $station->code = $request->code;
        $station->name = $request->name;
        $station->description = $request->description;
        $station->save();

        return response($station);
    }

    /**
     * Temporarily delete (ban) the specific station.
     *
     * @param string $stationId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @return \Illuminate\Http\Response
     */
    public function revoke($stationId)
    {
        $this->authorize('softDelete', [Station::class]);

        $user = Auth::guard('api')->user();

        $station = Station::onlyRelated($user)->with('facility')->findOrFail($stationId);

        $station->delete();

        $station->refresh();

        return response($station);
    }

    /**
     * Restore the specific banned station.
     *
     * @param string $stationId
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @return \Illuminate\Http\Response
     */
    public function restore($stationId)
    {
        $this->authorize('restore', [Station::class]);

        $user = Auth::guard('api')->user();

        $station = Station::onlyRelated($user)->onlyTrashed()->with('facility')->findOrFail($stationId);

        $station->restore();

        $station->refresh();

        return response($station);
    }
}
