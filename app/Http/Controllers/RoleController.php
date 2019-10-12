<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get roles.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('viewAny', [Role::class]);

        $user = Auth::guard('api')->user();

        $roles = Role::onlyRelated($user)->withTrashed()->get();

        return response(['roles' => $roles]);
    }

    /**
     * Register new role.
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
        $this->authorize('create', [Role::class]);

        $this->validate($request, [
            'name' => 'required|max:25',
            'description' => 'sometimes|max:50',
        ]);

        $user = Auth::guard('api')->user();

        $role = new Role();
        $role->name = $request->name;
        $role->description = $request->description;
        $role->creator()->associate($user);
        $role->facility()->associate($user->facility);
        $role->save();

        $role->refresh();

        return response($role, 201);
    }

    /**
     * Get specific role.
     *
     * @param string $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->authorize('view', [Role::class, $id]);

        $user = Auth::guard('api')->user();

        $role = Role::onlyRelated($user)->withTrashed()->findOrFail($id);

        return response($role);
    }

    /**
     * Update specific role.
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
        $this->authorize('update', [Role::class]);

        $user = Auth::guard('api')->user();

        $role = Role::onlyRelated($user)->findOrFail($id);

        $this->validate($request, [
            'name' => 'required|max:25',
            'description' => 'sometimes|max:50',
        ]);

        $role->name = $request->name;
        $role->description = $request->description;
        $role->save();

        $role->refresh();

        return response($role);
    }

    /**
     * Temporarily delete (ban) the specific role.
     *
     * @param string $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function revoke($id)
    {
        $this->authorize('softDelete', [Role::class]);

        $user = Auth::guard('api')->user();

        $role = Role::onlyRelated($user)->findOrFail($id);

        $role->delete();

        $role->refresh();

        return response($role);
    }

    /**
     * Restore the specific banned role.
     *
     * @param string $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        $this->authorize('restore', [Role::class]);

        $user = Auth::guard('api')->user();

        $role = Role::onlyRelated($user)->onlyTrashed()->findOrFail($id);

        $role->restore();

        $role->refresh();

        return response($role);
    }

    /**
     * Permanently delete the specific role.
     *
     * @param string $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorize('forceDelete', [Role::class]);

        $user = Auth::guard('api')->user();

        $role = Role::onlyRelated($user)->onlyTrashed()->findOrFail($id);

        // ...

        $dependants = User::withTrashed()->where('role_id', $id)->count();

        if ($dependants) {
            return response(['message' => "Can't delete non-orphaned role."], 400);
        }

        // ...

        $role->forceDelete();

        return response(null, 204);
    }

    /**
     * Get users assigned this role.
     *
     * @param string $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function users($id)
    {
        $this->authorize('viewAny', [User::class]);

        $user = Auth::guard('api')->user();

        $role = Role::onlyRelated($user)->findOrFail($id);

        return response(['users' => $role->users]);
    }

    /**
     * All permissions available to this role.
     *
     * @param string $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function permissions($id)
    {
        $this->authorize('viewPermissions', [Role::class]);

        $user = Auth::guard('api')->user();

        $role = Role::onlyRelated($user)->findOrFail($id);

        return response()->json(['permissions' => $role->permissions]);
    }

    /**
     * Only permissions granted to this role.
     *
     * @param string $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function permissionsGranted($id)
    {
        $this->authorize('viewPermissions', [Role::class]);

        $user = Auth::guard('api')->user();

        $role = Role::onlyRelated($user)->findOrFail($id);

        $permissions = Permission::query()
            ->whereHas('module.facilities', function ($query) use ($user) {
                $query->where('facility_id', $user->facility_id);
            })
            ->orWhereHas('roles', function ($query) use ($role) {
                $query->where('role_id', $role->id);
            })
            ->with(['module', 'roles'])
            ->get();

        $permissions = $permissions->map(function ($permission) {
            return [
                'id' => $permission->id,
                'name' => $permission->name,
                'granted' => (bool) count($permission->roles),
                'module' => [
                    'name' => $permission->module->name,
                    'category' => $permission->module->category,
                ],
            ];

            // $permission->granted = (bool) count($permission->roles);
            // return $permission;
        });

        return response(['permissions' => $permissions]);
    }

    /**
     * Sync role permissions.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @throws ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function syncPermissions(Request $request, $id)
    {
        $this->authorize('assignPermissions', [Permission::class]);

        $role = Role::findOrFail($id);

        $this->validate($request, [
            'permissions' => 'required|array',
            'permissions.*' => 'required|integer',
        ]);

        $user = Auth::guard('api')->user();

        $permissions = Permission::query()
            ->whereHas('module.facilities', function ($query) use ($user) {
                $query->where('facility_id', $user->facility_id);
            })
            ->get();

        $available_perms = $permissions->map(function ($permission) {
            return $permission->id;
        })->toArray();

        $unknown_perms = array_values(array_diff($request->permissions, $available_perms));

        if ($unknown_perms) {
            $validator = Validator::make([], []);
            $validator->errors()->add('permissions', 'Unknown permissions: '.implode(', ', $unknown_perms));

            throw new ValidationException($validator);
        }

        // Sync permissions...
        $role->permissions()->sync($request->permissions, true);
        $role->save();

        $role = Role::with('permissions')->find($id);

        return response($role);
    }
}
