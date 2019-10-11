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
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('viewAny', [Module::class]);

        $modules = Module::withTrashed()->get();

        return response(['modules' => $modules]);
    }

    /**
     * Register new module.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', [Module::class]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:20',
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
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function show($name)
    {
        $this->authorize('view', [Module::class]);

        $module = Module::withTrashed()->findOrFail($name);

        return response($module);
    }

    /**
     * Update specific module.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $name
     *
     * @throws ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $name)
    {
        $this->authorize('update', [Module::class]);

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
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     *
     * @return \Illuminate\Http\Response
     */
    public function revoke($name)
    {
        $this->authorize('soft-delete', [Module::class]);

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
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function restore($name)
    {
        $this->authorize('restore', [Module::class]);

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
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($name)
    {
        $this->authorize('force-delete', [Module::class]);

        $module = Module::onlyTrashed()->findOrFail($name);

        $module->forceDelete();

        return response(null, 204);
    }
}
