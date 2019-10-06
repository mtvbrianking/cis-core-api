<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    /**
     * Get all facilities.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $facilities = Facility::get();

        return response(['facilities' => $facilities]);
    }

    /**
     * Store a newly created facility in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:100',
            'description' => 'sometimes|max:100',
            'address' => 'sometimes|max:50',
            'email' => 'required|email|max:50',
            'website' => 'required|url|max:50',
            'phone' => 'required|tel|max:25',
        ]);

        $user = Auth::guard('api')->user();

        $facility = new Facility();
        $facility->name = $request->name;
        $facility->description = $request->description;
        $facility->address = $request->address;
        $facility->email = $request->email;
        $facility->website = $request->website;
        $facility->phone = $request->phone;
        $facility->creator->associate($user);
        $facility->save();

        $facility->refresh();

        return response($facility, 201);
    }

    /**
     * Get the specified facility.
     *
     * @param string $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $facility = Facility::findOrFail($id);

        return response($facility);
    }

    /**
     * Update the specified facility in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|max:100',
            'description' => 'sometimes|max:100',
            'address' => 'sometimes|max:50',
            'email' => 'required|email|max:50',
            'website' => 'required|url|max:50',
            'phone' => 'required|tel|max:25',
        ]);

        $user = Auth::guard('api')->user();

        $facility = Facility::findOrFail($id);
        $facility->name = $request->name;
        $facility->description = $request->description;
        $facility->address = $request->address;
        $facility->email = $request->email;
        $facility->website = $request->website;
        $facility->phone = $request->phone;
        $facility->creator->associate($user);
        $facility->save();

        $facility->refresh();

        return response($facility);
    }

    /**
     * Temporarily delete (ban) the specific facility.
     *
     * @param string $id
     *
     * @return \Illuminate\Http\Response
     */
    public function revoke($id)
    {
        $facility = Facility::findOrFail($id);

        $facility->delete();

        $facility->refresh();

        return response($facility);
    }

    /**
     * Restore the specific banned facility.
     *
     * @param string $id
     *
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        $facility = Facility::onlyTrashed()->findOrFail($id);

        $facility->restore();

        $facility->refresh();

        return response($facility);
    }

    /**
     * Permanently delete the specific facility.
     *
     * @param string $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $facility = Facility::onlyTrashed()->findOrFail($id);

        $facility->forceDelete();

        return response(null, 204);
    }
}
