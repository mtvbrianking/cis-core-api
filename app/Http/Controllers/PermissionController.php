<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PermissionController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get permissions.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('viewAny', [Permission::class]);

        $permissions = Permission::get();

        return response(['permissions' => $permissions]);
    }

    /**
     * Register new permission.
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
        $this->authorize('create', [Permission::class]);

        $validator = Validator::make($request->all(), [
            'name'          => 'required',
            'description'   => 'sometimes|max:25',
            'module_name'   => 'required|exists:modules,name',
        ]);

        $validator->after(function ($validator) use ($request) {
            $name = Str::slug($request->name);

            $permission = Permission::where('name', $name)->where('module_name', $request->module_name)->first();

            if ($permission) {
                $validator->errors()->add('name', 'The name has already been taken.');
            }
        });

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $permission = new Permission();

        $permission->module_name = $request['module_name'];
        $permission->name = $request['name'];
        $permission->description = $request['description'];

        $permission->save();

        $permission->refresh();

        return response($permission, 201);
    }

    /**
     * Get specific permission.
     *
     * @param string $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->authorize('view', [Permission::class]);

        $permission = Permission::findOrFail($id);

        return response($permission);
    }

    /**
     * Update specific permission.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @throws ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->authorize('update', [Permission::class]);

        $validator = Validator::make($request->all(), [
            'name'          => 'required',
            'module_name'   => 'required|exists:modules,name',
            'description'   => 'sometimes|max:25',
        ]);

        $validator->after(function ($validator) use ($request, $id) {
            $name = Str::slug($request->name);

            $permission = Permission::where('name', $name)
                ->where('module_name', $request->module_name)
                ->where('id', '<>', $id)
                ->first();

            if ($permission) {
                $validator->errors()->add('name', 'The name has already been taken.');
            }
        });

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $permission = Permission::findOrFail($id);

        $permission->description = $request->description;
        $permission->name = $request->name;
        $permission->save();

        $permission->refresh();

        return response($permission);
    }

    /**
     * Permanently delete the specific permission.
     *
     * @param string $id
     *
     * @throws \Exception
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorize('delete', [Permission::class]);

        $permission = Permission::findOrFail($id);

        $permission->delete();

        return response(null, 204);
    }
}
