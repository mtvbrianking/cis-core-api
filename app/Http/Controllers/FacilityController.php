<?php

namespace App\Http\Controllers;

use App\Rules\Tel;
use App\Models\Module;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FacilityController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

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
            'description' => 'nullable|max:100',
            'address' => 'required|max:50',
            'email' => 'required|email|max:50',
            'website' => 'nullable|url|max:50',
            'phone' => ['nullable', new Tel, 'max:25'],
        ]);

        $user = Auth::guard('api')->user();

        $facility = new Facility();
        $facility->name = $request->name;
        $facility->description = $request->description;
        $facility->address = $request->address;
        $facility->email = $request->email;
        $facility->website = $request->website;
        $facility->phone = $request->phone;
        $facility->creator()->associate($user);
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
            'name' => 'sometimes|max:100',
            'description' => 'nullable|max:100',
            'address' => 'sometimes|max:50',
            'email' => 'sometimes|email|max:50',
            'website' => 'nullable|url|max:50',
            'phone' => ['nullable', new Tel, 'max:25'],
        ]);

        $user = Auth::guard('api')->user();

        $facility = Facility::findOrFail($id);
        $facility->name = $request->input('name', $facility->name);
        $facility->description = $request->description;
        $facility->address = $request->input('address', $facility->address);
        $facility->email = $request->input('email', $facility->email);
        $facility->website = $request->website;
        $facility->phone = $request->phone;
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

    /**
     * Update facility module access.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @return \Illuminate\Http\Response
     */
    public function sync_modules(Request $request, $id)
    {
        $facility = Facility::findOrFail($id);

        $this->validate($request, [
            'modules' => 'required|array',
            'modules.*' => 'required|string',
        ]);

        $available_mods = Module::get()->map(function ($module) {
            return $module->name;
        })->toArray();

        $unknown_mods = array_values(array_diff($request->modules, $available_mods));

        if ($unknown_mods) {
            $validator = Validator::make([], []);
            $validator->errors()->add('modules', 'Unknown modules: '.implode(', ', $unknown_mods));

            throw new ValidationException($validator);
        }

        // Sync modules...
        $facility->modules()->sync($request->modules, true);
        $facility->save();

        $facility = Facility::with('modules')->find($id);

        return response($facility);
    }
}
