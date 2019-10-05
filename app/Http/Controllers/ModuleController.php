<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ModuleController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get modules.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $modules = Module::get();

        return response(['modules' => $modules]);
    }

    /**
     * Register new module.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'sometimes|max:25',
        ]);

        $validator->after(function ($validator) use ($request) {
            $name = Str::slug(Str::plural($request->name));

            $module = Module::where('name', $name)->first();

            if ($module) {
                $validator->errors()->add('name', 'The name has already been taken.');
            }
        });

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $module = new Module();
        $module->name = $request->name;
        $module->description = $request->description;
        $module->save();

        $module->refresh();

        return response($module, 201);
    }

    /**
     * Get specific module.
     *
     * @param string $name
     *
     * @return \Illuminate\Http\Response
     */
    public function show($name)
    {
        $module = Module::findOrFail($name);

        return response($module);
    }

    /**
     * Update specific module.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $name
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $name)
    {
        $module = Module::findOrFail($name);

        $this->validate($request, [
            'description' => 'nullable|max:25',
        ]);

        $module->description = $request->description;
        $module->save();

        $module->refresh();

        return response($module);
    }

    /**
     * Temporarily delete (ban) the specific module.
     *
     * @param string $name
     *
     * @return \Illuminate\Http\Response
     */
    public function revoke($name)
    {
        $module = Module::findOrFail($name);

        $module->delete();

        $module->refresh();

        return response($module);
    }

    /**
     * Restore the specific banned module.
     *
     * @param string $name
     *
     * @return \Illuminate\Http\Response
     */
    public function restore($name)
    {
        $module = Module::onlyTrashed()->findOrFail($name);

        $module->restore();

        $module->refresh();

        return response($module);
    }

    /**
     * Permanently delete the specific module.
     *
     * @param string $name
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($name)
    {
        $module = Module::onlyTrashed()->findOrFail($name);

        $module->forceDelete();

        return response(null, 204);
    }
}
